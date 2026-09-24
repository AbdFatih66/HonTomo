<?php

namespace Tests\Unit\Services\Kanji;

use App\Services\Kanji\KanjiVgConverter;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the "thin / thick / broken" stroke rendering bug:
 * the old outlinePathData() built one offset-and-connect polygon per
 * stroke, which self-intersected at sharp turns or wherever two resampled
 * centerline points coincided. The fix builds each stroke as a union of
 * simple convex shapes (a rectangle per segment, a round joint per point),
 * which cannot self-intersect. These tests check the geometric property
 * that actually caused the bug — consistent polygon winding, and every
 * stroke producing *something* visible — rather than pixel-matching exact
 * output, since the flat-width ribbon is deliberately an approximation.
 */
class KanjiVgConverterTest extends TestCase
{
    private function svgWithStroke(string $d, string $id = '04e00'): string
    {
        return <<<SVG
        <svg>
          <g id="kvg:StrokePaths_{$id}">
            <path id="kvg:{$id}-s1" d="{$d}" />
          </g>
          <g id="kvg:StrokeNumbers_{$id}"></g>
        </svg>
        SVG;
    }

    /** @return float[] signed area of every closed subpath in the `d` string */
    private function subpathSignedAreas(string $d): array
    {
        $subpaths = preg_split('/(?=M)/', trim($d));
        $areas = [];

        foreach ($subpaths as $sp) {
            if (trim($sp) === '') {
                continue;
            }

            preg_match_all('/[ML](-?[\d.]+),(-?[\d.]+)/', $sp, $m, PREG_SET_ORDER);
            $poly = array_map(fn ($p) => [(float) $p[1], (float) $p[2]], $m);

            if (count($poly) < 3) {
                continue;
            }

            $n = count($poly);
            $sum = 0.0;
            for ($i = 0; $i < $n; $i++) {
                [$x1, $y1] = $poly[$i];
                [$x2, $y2] = $poly[($i + 1) % $n];
                $sum += $x1 * $y2 - $x2 * $y1;
            }
            $areas[] = $sum / 2;
        }

        return $areas;
    }

    public static function sharpAndDegenerateStrokeProvider(): array
    {
        return [
            'sharp 90-degree turn' => ['M10,10 L10,50 L50,50'],
            'sharp zigzag' => ['M0,0 L100,5 L0,10 L100,15'],
            'smooth curve' => ['M10,90 C30,10 70,10 90,90'],
            'near-zero-length stroke (a dot)' => ['M70,70 L70.05,70.05'],
            'literal single point (degenerate M only)' => ['M70,70'],
            // Real KanjiVG data (04f1a.svg, stroke 5 of 会 — type ㇜):
            // the small sharp pen-entry hook right at the start of this
            // curve was the case that broke the old bevel/miter joins —
            // its curvature is tighter than half the stroke width, so
            // the concave-side offset points cross even though the turn
            // isn't extreme-looking on its own.
            '会 stroke 5 (real pen-entry hook)' => ['M47.16,66.38c0.62,1.65-0.03,2.93-0.92,4.28c-5.17,7.8-8.02,11.38-14.99,18.84c-2.11,2.25-1.5,4.18,2,3.75c7.35-0.91,28.19-5.83,40.16-7.95'],
        ];
    }

    /**
     * @dataProvider sharpAndDegenerateStrokeProvider
     *
     * The bug: at a sharp turn (or wherever resampling produced coincident
     * points), the old single-polygon offset could fold back over itself.
     * A self-intersecting polygon has subpaths of BOTH winding directions
     * in the same shape once split at the crossing — this asserts every
     * primitive making up the stroke winds the same way, i.e. nothing
     * folded over.
     */
    public function test_stroke_outline_never_self_intersects(string $d): void
    {
        $result = (new KanjiVgConverter)->convert($this->svgWithStroke($d));

        $this->assertNotEmpty($result['strokes'], 'A stroke with valid path data must not vanish.');

        $areas = $this->subpathSignedAreas($result['strokes'][0]);

        $this->assertNotEmpty($areas, 'Expected at least one filled shape in the stroke outline.');

        $signs = array_unique(array_map(fn ($a) => $a <=> 0, $areas));
        $this->assertCount(
            1,
            $signs,
            'All primitives in one stroke must wind the same direction, or overlapping shapes '.
            'can cancel out to a hole under the nonzero fill rule (this is what "putus-putus" looks like).',
        );
    }

    /**
     * @dataProvider sharpAndDegenerateStrokeProvider
     *
     * The other half of the bug: a degenerate stroke (near-zero length, or
     * a bare moveto with nothing else) used to come out as an empty `d`
     * string via `if ($n < 2) return '';` — an entire stroke silently
     * missing from the rendered character. A dot-sized stroke should still
     * draw as a dot, not disappear.
     */
    public function test_stroke_outline_is_never_empty_for_parseable_path_data(string $d): void
    {
        $result = (new KanjiVgConverter)->convert($this->svgWithStroke($d));

        $this->assertNotSame('', $result['strokes'][0]);
    }

    public function test_unparseable_stroke_data_does_not_crash_the_whole_import(): void
    {
        // A command this parser doesn't support (arcs) and nothing else —
        // must degrade to "nothing to draw" for that one stroke, not throw.
        $result = (new KanjiVgConverter)->convert(
            $this->svgWithStroke('A50,50 0 0 1 60,60'),
        );

        $this->assertSame(1, $result['stroke_count']);
        $this->assertSame('', $result['strokes'][0]);
    }

    public function test_stroke_order_follows_the_kvg_s_number_not_document_order(): void
    {
        // s2 appears in the document before s1 — a real KanjiVG pattern for
        // multi-component characters (radicals nested in their own <g>).
        $svg = <<<'SVG'
        <svg>
          <g id="kvg:StrokePaths_04e9c">
            <path id="kvg:04e9c-s2" d="M20,20 L20,60" />
            <path id="kvg:04e9c-s1" d="M10,10 L10,50" />
          </g>
          <g id="kvg:StrokeNumbers_04e9c"></g>
        </svg>
        SVG;

        $result = (new KanjiVgConverter)->convert($svg);

        $this->assertSame(2, $result['stroke_count']);
        // s1's centerline starts near x=10; s2's near x=20 — order in the
        // output array should be s1 then s2 regardless of document order.
        $this->assertStringContainsString('10.', $result['strokes'][0]);
    }

    public function test_medians_are_unaffected_by_the_outline_rewrite(): void
    {
        $result = (new KanjiVgConverter)->convert(
            $this->svgWithStroke('M10,10 L10,50 L50,50'),
        );

        $this->assertCount(1, $result['medians']);
        $this->assertCount(18, $result['medians'][0]); // MEDIAN_POINTS
        foreach ($result['medians'][0] as $point) {
            $this->assertCount(2, $point);
            $this->assertIsFloat($point[0]);
            $this->assertIsFloat($point[1]);
        }
    }
}
