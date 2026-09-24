<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\KanaController;
use App\Http\Controllers\Api\KanjiController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\WebAuthnController;
use App\Http\Controllers\Api\VocabularyController;
use Illuminate\Support\Facades\Route;

// ==========================================
// GUEST
// ==========================================
Route::middleware('throttle:auth-ip')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:forgot-password');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:reset-password');

    Route::post('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed:relative', 'throttle:verify-email'])
        ->name('verification.verify');

    Route::post('/auth/google/exchange', [SocialAuthController::class, 'exchange'])->middleware('throttle:oauth');
});

// Google OAuth browser round-trip. Needs the session (OAuth `state`), so these
// two GET routes use the `web` middleware group even though they live under /api.
Route::middleware(['web', 'throttle:oauth'])->prefix('auth/google')->group(function () {
    Route::get('/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('/callback', [SocialAuthController::class, 'callback']);
});

// WebAuthn/passkeys. 'web' is needed to hold the challenge in the session
// between the "options" and "verify" calls (see WebAuthnController docblock).
// CSRF is exempted for these routes in bootstrap/app.php.
Route::middleware(['web', 'throttle:webauthn'])->prefix('webauthn')->group(function () {
    Route::post('/login/options', [WebAuthnController::class, 'loginOptions']);
    Route::post('/login', [WebAuthnController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/register/options', [WebAuthnController::class, 'registerOptions']);
        Route::post('/register', [WebAuthnController::class, 'register']);
        Route::get('/credentials', [WebAuthnController::class, 'credentials']);
        Route::delete('/credentials/{id}', [WebAuthnController::class, 'destroyCredential']);
    });
});

// ==========================================
// AUTHENTICATED
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:verification-resend');

    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:change-password');

    // Active sessions (profile page)
    Route::get('/sessions', [SessionController::class, 'index']);
    Route::delete('/sessions', [SessionController::class, 'destroyOthers']);
    Route::delete('/sessions/{id}', [SessionController::class, 'destroy'])->whereNumber('id');

    // Connected accounts (profile page)
    Route::post('/auth/google/link-intent', [SocialAuthController::class, 'linkIntent']);
    Route::delete('/auth/google', [SocialAuthController::class, 'unlink']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/learning-path', [LevelController::class, 'path']);
    Route::get('/levels', [LevelController::class, 'index']);
    Route::get('/levels/{level}', [LevelController::class, 'show']);

    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);
    Route::post('/lessons/{lesson}/start', [LessonController::class, 'start']);
    Route::post('/user-lessons/{userLesson}/answer', [LessonController::class, 'submitAnswer']);
    Route::post('/user-lessons/{userLesson}/finish', [LessonController::class, 'finish']);

    Route::get('/vocabulary', [VocabularyController::class, 'index']);
    Route::get('/vocabulary/{vocabulary}', [VocabularyController::class, 'show']);

    Route::get('/kana', [KanaController::class, 'index']);
    Route::get('/kana/quiz', [KanaController::class, 'quiz']);
    Route::get('/kana/{kanaCharacter}', [KanaController::class, 'show']);

    Route::get('/kanji', [KanjiController::class, 'index']);
    Route::get('/kanji/quiz', [KanjiController::class, 'quiz']);
    Route::get('/kanji/{kanji}', [KanjiController::class, 'show']);
    Route::get('/kanji/{kanji}/strokes', [KanjiController::class, 'strokes']);
    Route::post('/kanji/{kanji}/progress', [KanjiController::class, 'recordProgress']);

    Route::get('/review/due', [ReviewController::class, 'due']);
    Route::post('/review/submit', [ReviewController::class, 'submit']);

    Route::post('/locale', [LocaleController::class, 'update']);

    // ==========================================
    // USER MANAGEMENT (admin only)
    // ==========================================
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::put('/users/{user}', [UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);

        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/actions', [AuditLogController::class, 'actions']);
    });
});
