<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use App\Models\UserLesson;
use App\Models\UserQuestionAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Lessons, quizzes and progress keep working after the auth changes, and
 * user progress survives every account operation (reset, re-login, ...).
 *
 * Uses a tiny fixture curriculum (see BuildsLessonFixtures) in the throw-away
 * test database; nothing here can reach real data (see tests/TestCase.php).
 */
class LearningRegressionTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMiniCurriculum();
    }

    private function register(string $email = 'murid@example.com'): array
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Murid', 'email' => $email,
            'password' => 'Sakura2026', 'password_confirmation' => 'Sakura2026',
        ])->assertCreated();

        return [User::where('email', $email)->firstOrFail(), $response->json('token')];
    }

    /** Sanctum caches the resolved user per app instance; reset between identities. */
    private function as(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    private function startFirstLesson(string $token): TestResponse
    {
        return $this->as($token)->postJson("/api/lessons/{$this->firstLesson->id}/start")->assertOk();
    }

    private function answerCorrectly(string $token, int $userLessonId): TestResponse
    {
        return $this->as($token)->postJson("/api/user-lessons/{$userLessonId}/answer", [
            'question_id' => $this->question->id,
            'answer' => $this->correctOption->id,
        ]);
    }

    // -------------------------------------------------------------- access

    public function test_learning_endpoints_require_authentication(): void
    {
        foreach (['/api/dashboard', '/api/learning-path', '/api/levels', '/api/vocabulary', '/api/review/due', '/api/kana'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
    }

    public function test_a_new_user_can_open_the_dashboard_and_learning_path(): void
    {
        [, $token] = $this->register();

        $this->as($token)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure(['name', 'xp', 'streak', 'progress', 'current_level']);

        $this->as($token)->getJson('/api/learning-path')->assertOk();
        $this->as($token)->getJson('/api/levels')->assertOk();
        $this->as($token)->getJson('/api/review/due')->assertOk();
    }

    // ------------------------------------------------- lesson + quiz flow

    public function test_a_lesson_can_be_played_from_start_to_finish(): void
    {
        [$user, $token] = $this->register();

        $userLessonId = $this->startFirstLesson($token)->json('user_lesson_id');

        $this->as($token)->getJson("/api/lessons/{$this->firstLesson->id}")
            ->assertOk()
            ->assertJsonStructure(['lesson', 'questions'])
            ->assertJsonCount(4, 'questions');

        $this->answerCorrectly($token, $userLessonId)->assertOk()->assertJsonPath('is_correct', true);

        $finish = $this->as($token)->postJson("/api/user-lessons/{$userLessonId}/finish")->assertOk();

        $this->assertContains($finish->json('status'), ['completed', 'mastered']);
        $this->assertSame(10, $finish->json('xp_earned'));
        $this->assertSame(10, $user->totalXp());
        $this->assertDatabaseHas('user_lessons', ['id' => $userLessonId, 'user_id' => $user->id]);

        $this->as($token)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('progress.lessons_completed', 1);
    }

    public function test_a_wrong_answer_is_graded_as_wrong(): void
    {
        [, $token] = $this->register();
        $userLessonId = $this->startFirstLesson($token)->json('user_lesson_id');

        $wrong = $this->question->options()->where('is_correct', false)->firstOrFail();

        $this->as($token)->postJson("/api/user-lessons/{$userLessonId}/answer", [
            'question_id' => $this->question->id,
            'answer' => $wrong->id,
        ])->assertOk()->assertJsonPath('is_correct', false);
    }

    public function test_the_next_lesson_stays_locked_until_the_previous_one_is_completed(): void
    {
        [, $token] = $this->register();

        // locked at first
        $this->as($token)->postJson("/api/lessons/{$this->secondLesson->id}/start")->assertForbidden();

        $userLessonId = $this->startFirstLesson($token)->json('user_lesson_id');
        $this->answerCorrectly($token, $userLessonId)->assertOk();
        $this->as($token)->postJson("/api/user-lessons/{$userLessonId}/finish")->assertOk();

        // unlocked afterwards
        $this->as($token)->postJson("/api/lessons/{$this->secondLesson->id}/start")->assertOk();
    }

    // ------------------------------------------------ progress is preserved

    public function test_progress_survives_password_reset_and_a_fresh_login(): void
    {
        [$user, $token] = $this->register();

        $userLessonId = $this->startFirstLesson($token)->json('user_lesson_id');
        $this->answerCorrectly($token, $userLessonId)->assertOk();
        $this->as($token)->postJson("/api/user-lessons/{$userLessonId}/finish")->assertOk();

        $columns = ['lesson_id', 'status', 'best_accuracy', 'xp_earned'];
        $before = UserLesson::where('user_id', $user->id)->get($columns)->toArray();
        $xpBefore = $user->totalXp();
        $attemptsBefore = UserQuestionAttempt::where('user_id', $user->id)->count();

        // forgot -> reset (revokes every session) -> log in again
        $resetToken = Password::broker()->createToken($user);
        $this->postJson('/api/reset-password', [
            'token' => $resetToken, 'email' => $user->email,
            'password' => 'BaruAman2026', 'password_confirmation' => 'BaruAman2026',
        ])->assertOk();

        $newToken = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'BaruAman2026'])
            ->assertOk()->json('token');

        $this->assertSame($before, UserLesson::where('user_id', $user->id)->get($columns)->toArray());
        $this->assertSame($xpBefore, $user->fresh()->totalXp());
        $this->assertSame($attemptsBefore, UserQuestionAttempt::where('user_id', $user->id)->count());

        $this->as($newToken)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('progress.lessons_completed', 1);
    }

    public function test_registering_new_users_never_changes_existing_users_progress(): void
    {
        [$first, $firstToken] = $this->register('pertama@example.com');
        $this->startFirstLesson($firstToken);

        $before = UserLesson::where('user_id', $first->id)->get()->toArray();

        $this->register('kedua@example.com');
        $this->register('ketiga@example.com');

        $this->assertSame($before, UserLesson::where('user_id', $first->id)->get()->toArray());
        $this->assertSame(1, UserLesson::count());
    }

    // ------------------------------------------------ authorization (IDOR)

    public function test_a_user_cannot_answer_or_finish_someone_elses_lesson_attempt(): void
    {
        [, $aliceToken] = $this->register('alice@example.com');
        [$bob, $bobToken] = $this->register('bob@example.com');

        $aliceAttemptId = $this->startFirstLesson($aliceToken)->json('user_lesson_id');
        $columns = ['status', 'best_accuracy', 'xp_earned', 'attempts'];
        $before = UserLesson::findOrFail($aliceAttemptId)->only($columns);

        $this->answerCorrectly($bobToken, $aliceAttemptId)->assertNotFound();
        $this->as($bobToken)->postJson("/api/user-lessons/{$aliceAttemptId}/finish")->assertNotFound();

        $this->assertSame($before, UserLesson::findOrFail($aliceAttemptId)->only($columns));
        $this->assertSame(0, UserQuestionAttempt::count());
        $this->assertSame(0, $bob->totalXp());
    }

    public function test_the_owner_can_still_use_their_own_attempt(): void
    {
        [, $token] = $this->register();
        $id = $this->startFirstLesson($token)->json('user_lesson_id');

        $this->answerCorrectly($token, $id)->assertOk();
        $this->as($token)->postJson("/api/user-lessons/{$id}/finish")->assertOk();
    }
}
