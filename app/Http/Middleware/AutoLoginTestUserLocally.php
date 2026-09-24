<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TEMPORARY, LOCAL-ONLY: auto-authenticates API requests as a fixed test
 * user, since there is no login/register flow yet. This middleware does
 * nothing outside the `local` environment.
 *
 * Remove this (and its registration in bootstrap/app.php) once real
 * login/register + Sanctum auth is built.
 */
class AutoLoginTestUserLocally
{
    public function handle(Request $request, Closure $next): Response
    {
        if (App::environment('local') && ! $request->user()) {
            $user = User::firstOrCreate(
                ['email' => 'test@local.dev'],
                ['name' => 'Local Test User', 'password' => 'password']
            );

            Auth::setUser($user);
        }

        return $next($request);
    }
}
