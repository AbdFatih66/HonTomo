<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserShortcut;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShortcutController extends Controller
{
    /**
     * Keep this in sync with the `to.name` values in
     * resources/js/navigation/vertical/index.js — it's the allow-list for
     * what a user is allowed to pin as a shortcut. Admin-only pages are
     * rejected for non-admins even if somehow posted.
     */
    private const ADMIN_ONLY_KEYS = ['admin-users', 'admin-audit-logs'];

    private const VALID_KEYS = [
        'root', 'learn', 'kosakata', 'mondaishuu', 'jlpt-test', 'jlpt-mock', 'kaite-oboeru',
        'kana', 'kanji', 'lampiran', 'admin-users', 'admin-audit-logs',
    ];

    /**
     * GET /api/shortcuts
     * First-time users get a small sensible default set instead of an
     * empty grid — mirrors the demo template's old hardcoded shortcuts,
     * but pointing at real pages.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (! UserShortcut::where('user_id', $user->id)->exists())
            $this->seedDefaults($user->id);

        $shortcuts = UserShortcut::where('user_id', $user->id)
            ->orderBy('position')
            ->get(['page_key', 'position']);

        return response()->json(['shortcuts' => $shortcuts]);
    }

    /**
     * POST /api/shortcuts  { page_key }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'page_key' => ['required', 'string', Rule::in(self::VALID_KEYS)],
        ]);

        if (in_array($data['page_key'], self::ADMIN_ONLY_KEYS, true) && ! $request->user()->isAdmin()) {
            throw ValidationException::withMessages(['page_key' => 'Not allowed.']);
        }

        $user = $request->user();
        $nextPosition = 1 + (int) UserShortcut::where('user_id', $user->id)->max('position');

        $shortcut = UserShortcut::firstOrCreate(
            ['user_id' => $user->id, 'page_key' => $data['page_key']],
            ['position' => $nextPosition],
        );

        return response()->json(['page_key' => $shortcut->page_key, 'position' => $shortcut->position], 201);
    }

    /**
     * DELETE /api/shortcuts/{pageKey}
     */
    public function destroy(Request $request, string $pageKey)
    {
        UserShortcut::where('user_id', $request->user()->id)
            ->where('page_key', $pageKey)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * PUT /api/shortcuts/reorder  { page_keys: string[] }
     * The full ordered list of the user's own shortcut keys — position is
     * just the index in that array. Anything not owned by the user (or not
     * currently one of their shortcuts) is ignored rather than erroring, so
     * a stale client can't corrupt someone else's rows.
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'page_keys' => 'required|array',
            'page_keys.*' => 'string',
        ]);

        $user = $request->user();
        $owned = UserShortcut::where('user_id', $user->id)->pluck('id', 'page_key');

        foreach ($data['page_keys'] as $index => $pageKey) {
            if ($owned->has($pageKey)) {
                UserShortcut::where('id', $owned[$pageKey])->update(['position' => $index]);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function seedDefaults(int $userId): void
    {
        foreach (['learn', 'mondaishuu', 'kana', 'kanji'] as $index => $pageKey) {
            UserShortcut::create(['user_id' => $userId, 'page_key' => $pageKey, 'position' => $index]);
        }
    }
}
