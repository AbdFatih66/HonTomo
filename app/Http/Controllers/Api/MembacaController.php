<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserMembacaProgress;
use App\Services\NotificationService;
use App\Services\StreakService;
use App\Services\XpService;
use Illuminate\Http\Request;

class MembacaController extends Controller
{
    /** Format set_key yang dikenal route: n5l1 .. n3l12 (3 level × 12 pelajaran). */
    public const SET_KEY_PATTERN = 'n[345]l(?:[1-9]|1[0-2])';

    /**
     * Bacaan yang sudah ada di frontend (resources/js/data/practice/membaca.js).
     * Hanya kunci ini yang boleh memberi XP, supaya kunci karangan yang cocok dengan
     * pola route tidak bisa dipakai menimbun XP. Tambahkan kunci di sini setiap
     * menambah pelajaran di membaca.js (dijaga oleh tests/js/membaca.test.mjs).
     */
    public const AVAILABLE_KEYS = ['n5l1', 'n4l1', 'n3l1', 'n5l2', 'n4l2', 'n3l2', 'n5l3', 'n4l3', 'n3l3', 'n5l4', 'n4l4', 'n3l4', 'n5l5', 'n4l5', 'n3l5', 'n5l6', 'n4l6', 'n3l6', 'n5l7', 'n4l7', 'n3l7', 'n5l8', 'n4l8', 'n3l8', 'n5l9', 'n4l9', 'n3l9', 'n5l10', 'n4l10', 'n3l10', 'n5l11', 'n4l11', 'n3l11', 'n5l12', 'n4l12', 'n3l12'];

    public function __construct(
        private XpService $xpService,
        private StreakService $streakService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Seluruh progres Membaca milik user, sebagai peta datar berkunci set_key
     * ({ [key]: { done, crown } }) — bentuk sama dengan endpoint Mondaishuu
     * dan Kaite Oboeru.
     * GET /api/membaca/progress
     */
    public function index(Request $request)
    {
        $rows = UserMembacaProgress::where('user_id', $request->user()->id)->get();

        $progress = $rows->mapWithKeys(fn (UserMembacaProgress $row) => [
            $row->set_key => ['done' => $row->done, 'crown' => $row->crown],
        ]);

        return response()->json(['progress' => $progress]);
    }

    /**
     * Mencatat hasil satu bacaan. Upsert per (user_id, set_key) karena bacaan
     * boleh diulang. XP sama seperti latihan lain: 10 XP pertama kali selesai,
     * +5 pertama kali selesai sempurna (mahkota), plus notifikasi dan streak.
     * POST /api/membaca/progress/{setKey}
     */
    public function store(Request $request, string $setKey)
    {
        abort_unless(in_array($setKey, self::AVAILABLE_KEYS, true), 404);

        $validated = $request->validate(['perfect' => 'required|boolean']);

        $user = $request->user();

        $progress = UserMembacaProgress::firstOrCreate([
            'user_id' => $user->id,
            'set_key' => $setKey,
        ]);

        $wasDone = $progress->done;
        $wasCrown = $progress->crown;

        $progress->applyResult($validated['perfect']);

        $xp = 0;
        if (! $wasDone)
            $xp += 10;
        if (! $wasCrown && $progress->crown)
            $xp += 5;

        if ($xp > 0) {
            $this->xpService->award($user, $xp, 'membaca_completed');

            if (! $wasDone) {
                $progress->crown
                    ? $this->notificationService->membacaMastered($user, $setKey, $xp)
                    : $this->notificationService->membacaCompleted($user, $setKey, $xp);
            }
            elseif (! $wasCrown && $progress->crown) {
                $this->notificationService->membacaMastered($user, $setKey, $xp);
            }
        }

        $this->streakService->recordActivity($user);

        return response()->json([
            'set_key' => $progress->set_key,
            'done' => $progress->done,
            'crown' => $progress->crown,
            'xp' => $xp,
        ]);
    }
}
