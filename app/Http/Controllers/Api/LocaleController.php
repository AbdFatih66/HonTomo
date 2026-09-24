<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Update the UI language (not the learning language, which is always Japanese).
     * Persists to the user profile when authenticated, otherwise to the session.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required|in:id,en',
        ]);

        session(['ui_language' => $data['locale']]);

        if ($request->user()) {
            $request->user()->update(['ui_language' => $data['locale']]);
        }

        app()->setLocale($data['locale']);

        return response()->json(['locale' => $data['locale']]);
    }
}
