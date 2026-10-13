<?php

namespace App\Services\Kanji;

/**
 * Converts one KanjiVG character SVG into the {strokes, medians} shape
 * HanziWriter's charDataLoader expects.
 *
 * Why this exists (see docs/kanji-module.md "Adapter KanjiVG -> HanziWriter"
 * for the full write-up): HanziWriter's own bundled data and every
 * "hanzi-writer-data-jp" package on npm are derived from AnimCJK / Make Me A
 * Hanzi, NOT from KanjiVG — different stroke shapes/order in places, and a
 * different license (Arphic/LGPL) than KanjiVG's CC BY-SA. The project
 * explicitly wants KanjiVG as the source of truth for Japanese stroke order,
 * so this converter exists instead of pulling in one of those packages.
 *
 * Coordinate transform: KanjiVG paths live in a 109x109, Y-down SVG
 * viewBox. HanziWriter's own data uses a 1024x1024, Y-UP space (an
 * em-square convention inherited from the font glyphs Make Me A Hanzi was
 * extracted from). So every point is scaled by 1024/109 and then flipped:
 * y' = 1024 - y*scale. This was verified against several real KanjiVG
 * files during development (converted paths land in the expected
 * orientation/quadrants); it has NOT been pixel-verified by literally
 * rendering through HanziWriter in a browser, so treat the very first
 * on-screen render of a freshly-imported character as the real
 * confirmation step, not this code comment.
 *
 * Medians (the stroke centerline HanziWriter uses for direction-checking
 * in quiz mode) are APPROXIMATED by resampling the stroke path itself at
 * even arc-length intervals. KanjiVG strokes are already fairly thin
 * calligraphic outlines rather than a filled glyph shape, so this is a
 * reasonable stand-in — but it is not a true geometric skeleton the way
 * Make Me A Hanzi's medians are. If stroke-direction checking in the quiz
 * ever feels too strict/lenient, this approximation is the first place to
 * revisit.
 *
 * IMPORTANT — `strokes` must be a FILLED shape, not a thin line:
 * HanziWriter always renders `strokes` as a fill (SVG <path> with no
 * stroke), never as an outlined/stroked line. KanjiVG's own paths are
 * just the centerline of the brush stroke (a few units wide at most), so
 * copying them straight through and filling them produces a jagged,
 * near-invisible sliver instead of a stroke — visible as "kana looks
 * right, kanji looks wrong" since the bundled kana data is already a
 * filled outline. `outlinePathData()` below fixes this by building a
 * constant-width ribbon around the centerline as a UNION of simple
 * convex shapes (a rectangle per segment, a round joint at every point) —
 * a real calligraphic outline (tapered, per-stroke width) is out of scope
 * here; this is a flat-width stand-in, good enough to read cleanly on
 * screen. See outlinePathData()'s own docblock for why it's built this
 * way rather than as one offset-and-connect polygon (the original
 * version of this file did that, and it self-intersected at sharp turns —
 * visible on screen as strokes rendering pinched-thin, ballooned-fat, or
 * fragmented right where a stroke changes direction).
 *
 * The join logic below (round arc on the convex side of a turn, direct
 * connection on the concave side) still self-intersected on real KanjiVG
 * calligraphy data — not the smoother hand-made demo data — because a
 * run of several sharp turns in a row (e.g. the small pen-entry hook at
 * the start of a ㇜-type stroke, or 会's stroke 5) can cross itself even
 * when each individual join looks fine on its own. `stripLoops()` is the
 * fix: a final pass over the finished left/right offset chains that
 * detects and collapses any self-crossing directly, regardless of what
 * caused it — see its docblock below.
 */
class KanjiVgConverter
{
    private const SOURCE_SIZE = 109.0;

    private const TARGET_SIZE = 1024.0;

    /**
     * HanziWriter animates a stroke being drawn by tracing a straight-line
     * polyline through exactly these median points (see
     * hanzi-writer's generateStrokes()/Stroke — `points` is `medians[i]`
     * verbatim, no further smoothing) and clipping it to this stroke's
     * filled outline. Too few points here and that polyline cuts corners
     * at any sharp bend/hook — cutting far enough to land outside the
     * (comparatively narrow, flat-width) outline this converter builds,
     * which clips the animated line to nothing right at that bend and
     * reads as the stroke "breaking" mid-draw. 18 keeps every bend in
     * this project's real stroke shapes well inside STROKE_WIDTH's half
     * (checked empirically: worst-case deviation ~22 units against a
     * half-width of 45 — see docs/kanji-module.md "Animasi kanji putus").
     *
     * NOT the fix for the "floating round cap for the first frame or two"
     * animation artifact (confirmed by testing at 40 — no change). That
     * artifact happens at drawn-length ≈ 0, which point density can't
     * touch — it's the generic round-linecap "dot before it's a line"
     * effect any SVG draw-on animation has at t≈0. Fix that on the
     * rendering side (e.g. a brief opacity fade-in in
     * KanjiStrokeAnimation.vue), not here.
     */
    private const MEDIAN_POINTS = 18;

    private const CURVE_SAMPLES_PER_SEGMENT = 12;

    /**
     * Flat stroke width in the 1024-unit target space (no taper). Matched
     * to the bundled kana glyph data's own average stroke width, measured
     * directly off resources/js/data/kana-strokes.json by rasterizing
     * each stroke and taking filled-area / skeleton-length (a standard
     * "average ribbon width" estimate): several kana strokes across あ,
     * か, さ, ん, わ averaged ~64 units, vs. this constant's old value of
     * 90 — which is why kanji strokes rendered visibly thicker/heavier
     * than kana next to them.
     */
    private const STROKE_WIDTH = 64.0;

    /** Points used to build the offset outline before it's serialized to a path — denser than MEDIAN_POINTS so the outline stays smooth, not faceted. */
    private const OUTLINE_POINTS = 24;

    /**
     * @return array{strokes: string[], medians: array<int, array<int, array{0: float, 1: float}>>, stroke_count: int}
     */
    public function convert(string $svgContent, string $sourceRef = ''): array
    {
        $paths = $this->extractStrokePaths($svgContent);

        $strokes = [];
        $medians = [];

        foreach ($paths as $d) {
            $segments = $this->transformSegments($this->parsePath($d));
            $centerline = $this->centerlinePoints($segments);

            // Both the outline and the median used to be resampled
            // independently from this same dense centerline, at two
            // different point counts (OUTLINE_POINTS=24 vs
            // MEDIAN_POINTS=18). That let their very first segment's
            // direction drift apart from ordinary floating-point/sampling
            // divergence — normally too small to see, but magnified right
            // after a sharp curve (e.g. a pen-entry hook), which is what
            // showed up on screen as HanziWriter's animated stroke (drawn
            // along the median) starting in a visibly different place/
            // angle than the filled outline underneath it for the first
            // frame or two. Deriving the median from the outline's own
            // already-resampled point chain instead of the raw centerline
            // ties them to identical geometry, so they can't diverge.
            $outlinePts = $this->outlinePoints($centerline);
            $strokes[] = $this->outlinePathData($outlinePts);
            $medians[] = $this->resampleByArcLength($outlinePts, self::MEDIAN_POINTS);
        }

        return [
            'strokes' => $strokes,
            'medians' => $medians,
            'stroke_count' => count($strokes),
        ];
    }

    /**
     * Pulls stroke <path> elements out of the kvg:StrokePaths_* group, in
     * kvg:XXXXX-sN order (N = real stroke order, not document order —
     * KanjiVG nests paths inside per-component <g> groups so document
     * order alone isn't reliable for multi-radical characters).
     *
     * @return string[] raw `d` attribute values, in stroke order
     */
    private function extractStrokePaths(string $svg): array
    {
        if (! preg_match('/<g\s+id="kvg:StrokePaths_[^"]*"[^>]*>(.*?)<g\s+id="kvg:StrokeNumbers/s', $svg, $groupMatch)) {
            // Fall back to "everything after StrokePaths" if the
            // StrokeNumbers group is missing/renamed in some file.
            if (! preg_match('/<g\s+id="kvg:StrokePaths_[^"]*"[^>]*>(.*)/s', $svg, $groupMatch)) {
                return [];
            }
        }

        $body = $groupMatch[1];

        preg_match_all(
            '/<path\s+id="kvg:[0-9a-f]+-s(\d+)"[^>]*\sd="([^"]+)"/i',
            $body,
            $matches,
            PREG_SET_ORDER
        );

        usort($matches, fn($a, $b) => (int) $a[1] <=> (int) $b[1]);

        return array_map(fn($m) => $m[2], $matches);
    }

    /**
     * Parses an SVG path `d` string into a list of absolute cubic-bezier
     * segments: [x0, y0, x1, y1, x2, y2, x3, y3] (start point, two control
     * points, end point). Handles M/L/C/S/Q — the commands KanjiVG's own
     * stroke paths actually use (verified against the live dataset; a
     * command outside this set is simply skipped rather than throwing, so
     * one malformed stroke can't take down an entire import batch).
     *
     * @return array<int, array{0: float,1: float,2: float,3: float,4: float,5: float,6: float,7: float}>
     */
    private function parsePath(string $d): array
    {
        preg_match_all('/[MLCQSZmlcqsz]|-?\d*\.?\d+(?:[eE]-?\d+)?/', $d, $m);
        $tokens = $m[0];
        $count = count($tokens);

        $segments = [];
        $cx = 0.0;
        $cy = 0.0;
        $cmd = null;
        $i = 0;

        $num = fn($idx) => (float) $tokens[$idx];

        while ($i < $count) {
            $t = $tokens[$i];

            if (ctype_alpha($t)) {
                $cmd = $t;
                $i++;

                continue;
            }

            switch ($cmd) {
                case 'M':
                case 'm':
                    $x = $num($i);
                    $y = $num($i + 1);
                    $i += 2;
                    if ($cmd === 'm') {
                        $x += $cx;
                        $y += $cy;
                    }
                    $cx = $x;
                    $cy = $y;
                    // Per SVG spec, any extra coordinate pairs after M are implicit lineto.
                    $cmd = $cmd === 'M' ? 'L' : 'l';
                    break;

                case 'L':
                case 'l':
                    $x = $num($i);
                    $y = $num($i + 1);
                    $i += 2;
                    if ($cmd === 'l') {
                        $x += $cx;
                        $y += $cy;
                    }
                    $segments[] = [$cx, $cy, $cx, $cy, $x, $y, $x, $y];
                    $cx = $x;
                    $cy = $y;
                    break;

                case 'C':
                case 'c':
                    $x1 = $num($i);
                    $y1 = $num($i + 1);
                    $x2 = $num($i + 2);
                    $y2 = $num($i + 3);
                    $x = $num($i + 4);
                    $y = $num($i + 5);
                    $i += 6;
                    if ($cmd === 'c') {
                        $x1 += $cx;
                        $y1 += $cy;
                        $x2 += $cx;
                        $y2 += $cy;
                        $x += $cx;
                        $y += $cy;
                    }
                    $segments[] = [$cx, $cy, $x1, $y1, $x2, $y2, $x, $y];
                    $cx = $x;
                    $cy = $y;
                    break;

                case 'S':
                case 's':
                    $x2 = $num($i);
                    $y2 = $num($i + 1);
                    $x = $num($i + 2);
                    $y = $num($i + 3);
                    $i += 4;
                    if ($cmd === 's') {
                        $x2 += $cx;
                        $y2 += $cy;
                        $x += $cx;
                        $y += $cy;
                    }
                    // Reflect the previous segment's second control point
                    // through the current point for a smooth join; with no
                    // prior curve, fall back to the current point itself.
                    $last = end($segments);
                    if ($last !== false) {
                        $x1 = 2 * $last[6] - $last[4];
                        $y1 = 2 * $last[7] - $last[5];
                    } else {
                        $x1 = $cx;
                        $y1 = $cy;
                    }
                    $segments[] = [$cx, $cy, $x1, $y1, $x2, $y2, $x, $y];
                    $cx = $x;
                    $cy = $y;
                    break;

                case 'Q':
                case 'q':
                    $qx1 = $num($i);
                    $qy1 = $num($i + 1);
                    $x = $num($i + 2);
                    $y = $num($i + 3);
                    $i += 4;
                    if ($cmd === 'q') {
                        $qx1 += $cx;
                        $qy1 += $cy;
                        $x += $cx;
                        $y += $cy;
                    }
                    // Degree-elevate quadratic -> cubic (standard 2/3 rule).
                    $x1 = $cx + (2 / 3) * ($qx1 - $cx);
                    $y1 = $cy + (2 / 3) * ($qy1 - $cy);
                    $x2 = $x + (2 / 3) * ($qx1 - $x);
                    $y2 = $y + (2 / 3) * ($qy1 - $y);
                    $segments[] = [$cx, $cy, $x1, $y1, $x2, $y2, $x, $y];
                    $cx = $x;
                    $cy = $y;
                    break;

                case 'Z':
                case 'z':
                    $i++;
                    break;

                default:
                    // Unknown/unsupported command token — skip it rather
                    // than looping forever or throwing on one bad stroke.
                    $i++;
                    break;
            }
        }

        return $segments;
    }

    private function transformPoint(float $x, float $y): array
    {
        $scale = self::TARGET_SIZE / self::SOURCE_SIZE;

        return [$x * $scale, self::TARGET_SIZE - $y * $scale];
    }

    private function transformSegments(array $segments): array
    {
        return array_map(function ($seg) {
            [$x0, $y0] = $this->transformPoint($seg[0], $seg[1]);
            [$x1, $y1] = $this->transformPoint($seg[2], $seg[3]);
            [$x2, $y2] = $this->transformPoint($seg[4], $seg[5]);
            [$x3, $y3] = $this->transformPoint($seg[6], $seg[7]);

            return [$x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3];
        }, $segments);
    }

    private function cubicPoint(array $p0, array $p1, array $p2, array $p3, float $t): array
    {
        $mt = 1 - $t;
        $x = $mt ** 3 * $p0[0] + 3 * $mt ** 2 * $t * $p1[0] + 3 * $mt * $t ** 2 * $p2[0] + $t ** 3 * $p3[0];
        $y = $mt ** 3 * $p0[1] + 3 * $mt ** 2 * $t * $p1[1] + 3 * $mt * $t ** 2 * $p2[1] + $t ** 3 * $p3[1];

        return [$x, $y];
    }

    /**
     * Dense point sampling of the raw centerline (pre-offset, pre-resample) —
     * shared source for both the median (resampled to MEDIAN_POINTS) and the
     * filled outline (resampled to OUTLINE_POINTS and built into a ribbon).
     *
     * @return array<int, array{0: float, 1: float}>
     */
    private function centerlinePoints(array $segments): array
    {
        $points = [];

        foreach ($segments as $seg) {
            $p0 = [$seg[0], $seg[1]];
            $p1 = [$seg[2], $seg[3]];
            $p2 = [$seg[4], $seg[5]];
            $p3 = [$seg[6], $seg[7]];

            for ($s = 0; $s <= self::CURVE_SAMPLES_PER_SEGMENT; $s++) {
                $points[] = $this->cubicPoint($p0, $p1, $p2, $p3, $s / self::CURVE_SAMPLES_PER_SEGMENT);
            }
        }

        return $points;
    }

    /**
     * Turns a centerline into the filled ribbon HanziWriter's `strokes`
     * actually needs, as ONE continuous closed polygon (a single SVG
     * subpath: one `M`, then `L`s, then `Z`).
     *
     * This used to be built as a union of many separate primitives (one
     * rectangle per segment plus a round joint circle at every point —
     * ~40+ disjoint subpaths for a typical stroke). That version rendered
     * correctly as a static image, but HanziWriter's animated stroke draw
     * (and the write-quiz's stroke-progress matching) measure position
     * along the path as ONE continuous line via getTotalLength()/
     * getPointAtLength() — fed a path made of dozens of disconnected
     * loops, that measurement jumps between unrelated fragments instead
     * of sweeping smoothly along the stroke, which is what showed up as
     * kanji rendering "putus-putus" (broken/disconnected) during
     * animation even though the final static shape looked fine. A single
     * polygon fixes that at the source.
     *
     * Built as a standard round-join polyline buffer: walk the centerline
     * building a left offset chain and a right offset chain, and at each
     * interior vertex, insert a rounded arc on whichever side is convex
     * (the outside of that turn) while the concave side just connects the
     * two offset points directly (a plain bevel — safe because the
     * concave offset points are converging, not diverging, so a direct
     * connection can't fold back over itself the way the old "one shared
     * offset for both sides" approach did at sharp turns). Both ends get
     * a rounded cap the same way. This is the textbook technique for
     * turning a stroked line into a single filled outline; see e.g. how
     * SVG itself defines `stroke-linejoin: round`.
     */
    private function outlinePathData(array $pts): string
    {
        $n = count($pts);
        $half = self::STROKE_WIDTH / 2;

        if ($n === 0) {
            return '';
        }

        if ($n === 1) {
            // Degenerate: the whole stroke collapsed to a single point
            // (an extremely short stroke, e.g. a dot) — draw it as a dot
            // instead of vanishing.
            return $this->circlePathData($pts[0], $half);
        }

        // Per-segment unit tangent and its left-hand normal (tangent
        // rotated 90°). Segment $i$ runs from $pts[$i]$ to $pts[$i+1]$.
        $tangents = [];
        $tangentAngles = [];
        $normals = [];
        for ($i = 0; $i < $n - 1; $i++) {
            $t = $this->unit([$pts[$i + 1][0] - $pts[$i][0], $pts[$i + 1][1] - $pts[$i][1]]);
            $tangents[] = $t;
            $tangentAngles[] = atan2($t[1], $t[0]);
            $normals[] = [-$t[1], $t[0]];
        }

        $left = [[$pts[0][0] + $normals[0][0] * $half, $pts[0][1] + $normals[0][1] * $half]];
        $right = [[$pts[0][0] - $normals[0][0] * $half, $pts[0][1] - $normals[0][1] * $half]];

        for ($i = 1; $i < $n - 1; $i++) {
            $tPrev = $tangents[$i - 1];
            $tNext = $tangents[$i];
            $nPrev = $normals[$i - 1];
            $nNext = $normals[$i];

            $leftPrev = [$pts[$i][0] + $nPrev[0] * $half, $pts[$i][1] + $nPrev[1] * $half];
            $leftNext = [$pts[$i][0] + $nNext[0] * $half, $pts[$i][1] + $nNext[1] * $half];
            $rightPrev = [$pts[$i][0] - $nPrev[0] * $half, $pts[$i][1] - $nPrev[1] * $half];
            $rightNext = [$pts[$i][0] - $nNext[0] * $half, $pts[$i][1] - $nNext[1] * $half];

            // Sign of the turn (cross product of consecutive tangents):
            // negative = right turn (left side is the convex/outer side),
            // positive = left turn (right side is the convex/outer side).
            $cross = $tPrev[0] * $tNext[1] - $tPrev[1] * $tNext[0];

            if ($cross < -1e-9) {
                // Right turn: the LEFT side is convex (outer) here — arc
                // it (arcPoints already lands exactly on $leftNext as its
                // last point). The RIGHT side is concave (inner) — a
                // plain direct connection.
                $left[] = $leftPrev;
                $a0 = $tangentAngles[$i - 1] + M_PI / 2;
                $a1 = $tangentAngles[$i] + M_PI / 2;
                foreach ($this->arcPoints($pts[$i], $a0, $this->shortDelta($a0, $a1), $half) as $p) {
                    $left[] = $p;
                }
                $right[] = $rightPrev;
                $right[] = $rightNext;
            } elseif ($cross > 1e-9) {
                // Left turn: mirror image of the above — RIGHT side
                // convex (arc), LEFT side concave (direct connection).
                $right[] = $rightPrev;
                $a0 = $tangentAngles[$i - 1] - M_PI / 2;
                $a1 = $tangentAngles[$i] - M_PI / 2;
                foreach ($this->arcPoints($pts[$i], $a0, $this->shortDelta($a0, $a1), $half) as $p) {
                    $right[] = $p;
                }
                $left[] = $leftPrev;
                $left[] = $leftNext;
            } else {
                $left[] = $leftNext;
                $right[] = $rightNext;
            }
        }

        $left[] = [$pts[$n - 1][0] + $normals[$n - 2][0] * $half, $pts[$n - 1][1] + $normals[$n - 2][1] * $half];
        $right[] = [$pts[$n - 1][0] - $normals[$n - 2][0] * $half, $pts[$n - 1][1] - $normals[$n - 2][1] * $half];

        // Safety net for the concave (inner) side: the direct
        // leftPrev->leftNext / rightPrev->rightNext connection above is
        // only guaranteed not to fold back at ONE turn in isolation. Over
        // a whole stroke, several turns in a row (KanjiVG's real
        // calligraphy data — not the smoother hand-made demo data — is
        // full of these, e.g. the small pen-entry hook at the very start
        // of a ㇜-type stroke) can still make the chain cross itself
        // further down the line, even when each individual join looked
        // fine. Rather than keep hunting for the one clever local rule
        // that prevents every case (bevel, then miter, then "insert the
        // centerline point" were all tried and each still failed on real
        // 会/食 stroke data), strip_loops() detects and removes ANY
        // self-crossing in the finished chain directly — the standard,
        // input-shape-agnostic fix for this class of offset-curve bug.
        $left = $this->stripLoops($left);
        $right = $this->stripLoops($right);

        // Round cap at the end, bulging forward (through the final
        // tangent direction): left-normal angle (+90°) to right-normal
        // angle (-90°) is an unambiguous -180° sweep through 0°.
        $endBase = $tangentAngles[$n - 2];
        $endCap = $this->arcPoints($pts[$n - 1], $endBase + M_PI / 2, -M_PI, $half);

        // Round cap at the start, bulging backward — plain constant
        // radius, matching the end cap exactly (kept deliberately simple:
        // no flare/"pressed brush" look at stroke starts). Its angle
        // comes from tangentAngles[0], i.e. pts[0]->pts[1] — same $pts
        // the median is now resampled from (see outlinePoints()), so the
        // animated stroke drawn along the median and this cap agree on
        // which way the stroke starts.
        $startBase = $tangentAngles[0];
        $startCap = $this->arcPoints($pts[0], $startBase - M_PI / 2, -M_PI, $half);

        $polygon = array_merge($left, $endCap, array_reverse($right), $startCap);

        // stripLoops() above only ever saw $left and $right in isolation,
        // so it can only catch a chain crossing itself. It CANNOT catch a
        // crossing BETWEEN $left and $right, or between either side and
        // one of the caps — e.g. two "gunungan"-shaped bulges of the
        // outline pinching together at a single point with a gap on each
        // side (a classic bowtie/self-intersecting polygon), which is
        // exactly what a nonzero-fill renderer shows as a hole punched
        // out of the stroke. stripLoopsClosed() runs the same collapse
        // logic over the whole assembled, closed polygon so any such
        // cross-chain crossing gets caught too.
        $polygon = $this->stripLoopsClosed($polygon);

        return $this->polygonToPathData($polygon);
    }

    /**
     * Same collapse logic as stripLoops(), but for a CLOSED polygon (the
     * final left+endCap+right+startCap ring) instead of one open chain.
     * The only difference from stripLoops() is that edge (n-1 -> 0) — the
     * implicit closing edge — is included, and the adjacency check has to
     * additionally treat edge 0 and edge (n-1) as neighbors (they share
     * point 0), not just consecutive edges i/i+1. This is what catches a
     * crossing between the left chain and the right chain, or between a
     * cap and the opposite side, which stripLoops() (called separately on
     * $left and $right, before the caps even exist) structurally cannot
     * see.
     *
     * @param array<int, array{0: float, 1: float}> $points
     * @return array<int, array{0: float, 1: float}>
     */
    private function stripLoopsClosed(array $points): array
    {
        $points = array_values($points);
        $maxIterations = 200;

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $n = count($points);
            if ($n < 4) {
                break;
            }

            $found = false;

            for ($i = 0; $i < $n; $i++) {
                $iNext = ($i + 1) % $n;

                for ($j = $i + 2; $j < $n; $j++) {
                    $jNext = ($j + 1) % $n;

                    // Skip edges that share an endpoint: normal forward
                    // adjacency (j === iNext, i.e. j === i+1, already
                    // excluded by starting j at i+2) plus the wraparound
                    // adjacency where the closing edge (n-1 -> 0) shares
                    // point 0 with edge (0 -> 1).
                    if ($i === 0 && $j === $n - 1) {
                        continue;
                    }

                    $hit = $this->segmentIntersect(
                        $points[$i],
                        $points[$iNext],
                        $points[$j],
                        $points[$jNext]
                    );

                    if ($hit === null) {
                        continue;
                    }

                    // Collapse the forward span between the two crossing
                    // edges (i+1 .. j) down to the single crossing point —
                    // same as stripLoops()'s open-chain case.
                    array_splice($points, $i + 1, $j - $i, [$hit]);
                    $found = true;
                    break 2;
                }
            }

            if (! $found) {
                break;
            }
        }

        return $points;
    }

    /**
     * Removes self-crossings from an open polyline chain (one side of the
     * ribbon — $left or $right — before it's merged into the final
     * polygon). Whenever segment [i, i+1] crosses a later, non-adjacent
     * segment [j, j+1], the looped-back span between them is collapsed
     * down to that single crossing point — the chain now runs straight
     * through the crossing instead of doubling back past it, which is
     * exactly the "duri"/notch artifact this was producing on screen.
     * Repeats until no crossings remain (a chain this short — at most
     * OUTLINE_POINTS-ish points — never needs many passes in practice;
     * the iteration cap is just a hard backstop against a pathological
     * input looping forever).
     *
     * @param array<int, array{0: float, 1: float}> $points
     * @return array<int, array{0: float, 1: float}>
     */
    private function stripLoops(array $points): array
    {
        $points = array_values($points);
        $maxIterations = 200;

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $n = count($points);
            $found = false;

            for ($i = 0; $i < $n - 1; $i++) {
                // j starts at i+2: segment [i,i+1] and [i+1,i+2] share an
                // endpoint by construction, not a real crossing, so the
                // immediate neighbor is skipped.
                for ($j = $i + 2; $j < $n - 1; $j++) {
                    $hit = $this->segmentIntersect(
                        $points[$i],
                        $points[$i + 1],
                        $points[$j],
                        $points[$j + 1]
                    );

                    if ($hit === null) {
                        continue;
                    }

                    array_splice($points, $i + 1, $j - $i, [$hit]);
                    $found = true;
                    break 2;
                }
            }

            if (! $found) {
                break;
            }
        }

        return $points;
    }

    /**
     * Standard parametric segment/segment intersection test. Returns the
     * crossing point only when it falls strictly inside BOTH segments
     * (a small epsilon excludes shared/touching endpoints, which are a
     * normal adjacency, not a loop) — null for parallel, collinear, or
     * non-crossing segments.
     *
     * @return array{0: float, 1: float}|null
     */
    private function segmentIntersect(array $a, array $b, array $c, array $d): ?array
    {
        $r = [$b[0] - $a[0], $b[1] - $a[1]];
        $s = [$d[0] - $c[0], $d[1] - $c[1]];

        $denom = $r[0] * $s[1] - $r[1] * $s[0];
        if (abs($denom) < 1e-9) {
            return null;
        }

        $qp = [$c[0] - $a[0], $c[1] - $a[1]];
        $t = ($qp[0] * $s[1] - $qp[1] * $s[0]) / $denom;
        $u = ($qp[0] * $r[1] - $qp[1] * $r[0]) / $denom;

        $eps = 1e-6;
        if ($t > $eps && $t < 1 - $eps && $u > $eps && $u < 1 - $eps) {
            return [$a[0] + $t * $r[0], $a[1] + $t * $r[1]];
        }

        return null;
    }

    /**
     * Resamples the dense centerline down to OUTLINE_POINTS, then drops
     * near-duplicate consecutive points (a zero-length segment has no
     * direction to offset, and would divide by zero downstream). This is
     * the single source of truth for the stroke's point geometry — both
     * outlinePathData() and the median (see convert()) are built from
     * its output, so they can never disagree about where the stroke
     * starts or which way it's pointing there.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    private function outlinePoints(array $centerline): array
    {
        $resampled = $this->resampleByArcLength($centerline, self::OUTLINE_POINTS);

        $pts = [];
        foreach ($resampled as $p) {
            if (empty($pts) || $this->distance($pts[count($pts) - 1], $p) > 1e-6) {
                $pts[] = $p;
            }
        }

        return $pts;
    }

    /** Euclidean distance between two [x, y] points. */
    private function distance(array $a, array $b): float
    {
        return sqrt(($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2);
    }

    /**
     * Normalizes an angle delta into (-π, π] — the short way around —
     * so a join arc always sweeps the small angle between two nearly-
     * aligned segment normals rather than the long way around the circle.
     */
    private function shortDelta(float $a0, float $a1): float
    {
        $d = fmod($a1 - $a0 + M_PI, 2 * M_PI);
        if ($d < 0) {
            $d += 2 * M_PI;
        }

        return $d - M_PI;
    }

    /**
     * Points along an arc centered at $center, starting at angle
     * $baseAngle and sweeping by $delta radians (signed; negative = 
     * clockwise), NOT including the arc's own start point (the caller
     * already has it as the preceding offset point) but INCLUDING its
     * end point, so the arc can be spliced directly into a polygon's
     * point list.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    private function arcPoints(array $center, float $baseAngle, float $delta, float $radius, int $steps = 6): array
    {
        $points = [];
        $stepCount = max(1, (int) round($steps * abs($delta) / M_PI) + 1);

        for ($s = 1; $s <= $stepCount; $s++) {
            $angle = $baseAngle + $delta * ($s / $stepCount);
            $points[] = [$center[0] + $radius * cos($angle), $center[1] + $radius * sin($angle)];
        }

        return $points;
    }

    /**
     * A full circle approximated with straight segments — used only for
     * the degenerate single-point (dot) stroke case.
     *
     * @return string SVG path `d` for one closed subpath
     */
    private function circlePathData(array $center, float $radius, int $steps = 16): string
    {
        $points = [];

        for ($k = 0; $k < $steps; $k++) {
            $angle = 2 * M_PI * $k / $steps;
            $points[] = [$center[0] + $radius * cos($angle), $center[1] - $radius * sin($angle)];
        }

        return $this->polygonToPathData($points);
    }

    /** @param array{0: float, 1: float} $v @return array{0: float, 1: float} */
    private function unit(array $v): array
    {
        $len = sqrt($v[0] ** 2 + $v[1] ** 2);

        return $len < 1e-6 ? [0.0, 0.0] : [$v[0] / $len, $v[1] / $len];
    }

    /** @param array<int, array{0: float, 1: float}> $polygon */
    private function polygonToPathData(array $polygon): string
    {
        if (empty($polygon)) {
            return '';
        }

        $parts = [sprintf('M%.2f,%.2f', $polygon[0][0], $polygon[0][1])];

        foreach (array_slice($polygon, 1) as $p) {
            $parts[] = sprintf('L%.2f,%.2f', $p[0], $p[1]);
        }

        $parts[] = 'Z';

        return implode(' ', $parts);
    }

    /** @return array<int, array{0: float, 1: float}> */
    private function resampleByArcLength(array $points, int $n): array
    {
        $count = count($points);
        if ($count === 0) {
            return [];
        }

        $dists = [0.0];
        for ($i = 1; $i < $count; $i++) {
            $dx = $points[$i][0] - $points[$i - 1][0];
            $dy = $points[$i][1] - $points[$i - 1][1];
            $dists[] = $dists[$i - 1] + sqrt($dx * $dx + $dy * $dy);
        }

        $total = end($dists);
        if ($total == 0.0) {
            return array_fill(0, $n, [round($points[0][0], 1), round($points[0][1], 1)]);
        }

        $result = [];
        $j = 0;
        for ($k = 0; $k < $n; $k++) {
            $target = $total * $k / ($n - 1);
            while ($j < $count - 2 && $dists[$j + 1] < $target) {
                $j++;
            }
            $segLen = $dists[$j + 1] - $dists[$j];
            $ratio = $segLen == 0.0 ? 0.0 : ($target - $dists[$j]) / $segLen;
            $x = $points[$j][0] + ($points[$j + 1][0] - $points[$j][0]) * $ratio;
            $y = $points[$j][1] + ($points[$j + 1][1] - $points[$j][1]) * $ratio;
            $result[] = [round($x, 1), round($y, 1)];
        }

        return $result;
    }
}
