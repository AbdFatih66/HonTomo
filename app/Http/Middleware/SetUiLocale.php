<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUiLocale
{
    private const SUPPORTED = ['id', 'en'];

    /**
     * Registered for BOTH the web and api groups in bootstrap/app.php.
     *
     * Resolution order:
     *   1. X-UI-Language header (sent by the SPA on every request, so the
     *      language the user just picked is applied immediately)
     *   2. authenticated user's saved preference (sanctum guard)
     *   3. session
     *   4. browser Accept-Language
     *   5. app default
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-UI-Language');

        $locale = (in_array($header, self::SUPPORTED, true) ? $header : null)
            ?? $request->user('sanctum')?->ui_language
            ?? session('ui_language')
            ?? $request->getPreferredLanguage(self::SUPPORTED)
            ?? config('app.locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
