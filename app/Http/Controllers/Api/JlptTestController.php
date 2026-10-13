<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Jlpt\AnswersRequest;
use App\Http\Requests\Jlpt\CheckAnswerRequest;
use App\Http\Requests\Jlpt\StartAttemptRequest;
use App\Models\UserJlptTestAttempt;
use App\Services\JlptTestService;
use Illuminate\Http\Request;

/**
 * Tes JLPT (simulasi ujian, termasuk paket "Simulasi JLPT" yang dulu terpisah).
 * Controller tipis — aturan ujian ada di App\Services\JlptTestService,
 * konfigurasi paket & kunci di config/jlpt.php.
 */
class JlptTestController extends Controller
{
    public function __construct(private JlptTestService $service) {}

    /** GET /api/jlpt-test/packs — paket yang aktif + ringkasan user (skor terbaik, tes berjalan). */
    public function packs(Request $request)
    {
        return response()->json(['packs' => $this->service->packs($request->user())]);
    }

    /** GET /api/jlpt-test/packs/{pack} — status tes sebuah paket + riwayat. */
    public function show(Request $request, string $pack)
    {
        return response()->json($this->service->state($request->user(), $pack));
    }

    /** POST /api/jlpt-test/packs/{pack}/attempts — mulai (atau lanjutkan) tes. Body: mode=strict|practice. */
    public function start(StartAttemptRequest $request, string $pack)
    {
        $this->service->startAttempt(
            $request->user(),
            $pack,
            $request->validated('mode') ?? JlptTestService::MODE_STRICT,
        );

        return response()->json($this->service->state($request->user(), $pack));
    }

    /** DELETE /api/jlpt-test/packs/{pack}/attempts/current — batalkan tes yang berjalan. */
    public function abandon(Request $request, string $pack)
    {
        $this->service->assertPackAccessible($request->user(), $pack);

        $attempt = $this->service->currentAttempt($request->user(), $pack);
        if ($attempt)
            $this->service->abandon($attempt);

        return response()->json($this->service->state($request->user(), $pack));
    }

    /** POST /api/jlpt-test/attempts/{attempt}/sections/{section}/start */
    public function startSection(Request $request, int $attempt, string $section)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);
        $row = $this->service->startSection($attemptModel, $section);

        return response()->json($this->service->sectionPayload($row, $attemptModel, withTest: true));
    }

    /** GET /api/jlpt-test/attempts/{attempt}/sections/{section} — lanjutkan sesi (refresh). */
    public function section(Request $request, int $attempt, string $section)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);
        $row = $attemptModel->sections()->where('section', $section)->first();
        abort_if($row === null || $row->started_at === null, 404);

        return response()->json($this->service->sectionPayload($row, $attemptModel, withTest: true));
    }

    /** PUT .../answers — autosave jawaban sementara. */
    public function saveAnswers(AnswersRequest $request, int $attempt, string $section)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);
        $row = $this->service->saveAnswers($attemptModel, $section, $request->validated('answers'));

        return response()->json(['remaining_seconds' => $this->service->sectionPayload($row, $attemptModel)['remaining_seconds']]);
    }

    /**
     * POST .../check — umpan balik SATU soal (benar/salah, kunci, pembahasan).
     * Hanya untuk mode latihan; mode ujian dijawab 403.
     */
    public function check(CheckAnswerRequest $request, int $attempt, string $section)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);

        return response()->json($this->service->check(
            $attemptModel,
            $section,
            $this->questionId($request->validated('question')),
            (int) $request->validated('choice'),
        ));
    }

    /**
     * POST .../submit — kirim & nilai sesi. Respons SENGAJA tidak memuat
     * skor/kunci: rekap baru bisa dibuka setelah semua sesi selesai.
     */
    public function submit(AnswersRequest $request, int $attempt, string $section)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);
        $this->service->submitSection($attemptModel, $section, $request->validated('answers'));

        return response()->json([
            'submitted' => true,
            'test_completed' => $attemptModel->refresh()->status === UserJlptTestAttempt::STATUS_COMPLETED,
            'attempt_id' => $attemptModel->id,
        ]);
    }

    /** GET /api/jlpt-test/attempts/{attempt}/recap */
    public function recap(Request $request, int $attempt)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);

        return response()->json($this->service->recap($attemptModel));
    }

    /** GET /api/jlpt-test/attempts/{attempt}/review — soal + kunci + pembahasan + jawaban user (setelah tes selesai). */
    public function review(Request $request, int $attempt)
    {
        $attemptModel = $this->service->findOwned($request->user(), $attempt);

        return response()->json($this->service->review($attemptModel));
    }

    /** Id soal bank classic berupa angka ("12"); di JSON kunci array-nya int. */
    private function questionId(string $raw): string|int
    {
        return ctype_digit($raw) ? (int) $raw : $raw;
    }
}
