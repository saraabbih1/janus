<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HabitsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'alice@example.com')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                    'token',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
        ]);
    }

    public function test_user_can_login_and_invalid_credentials_return_401(): void
    {
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_manage_only_their_habits(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $storeResponse = $this->postJson('/api/habits', [
            'title' => 'Read 20 pages',
            'description' => 'Evening reading session',
            'frequency' => 'daily',
            'target_days' => 5,
            'color' => '#00AAFF',
            'is_active' => true,
        ]);

        $habitId = $storeResponse->json('data.habit.id');

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.habit.user_id', $user->id)
            ->assertJsonPath('data.habit.title', 'Read 20 pages');

        $this->getJson('/api/habits')
            ->assertOk()
            ->assertJsonCount(1, 'data.habits');

        $this->putJson("/api/habits/{$habitId}", [
            'title' => 'Read 30 pages',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.habit.title', 'Read 30 pages')
            ->assertJsonPath('data.habit.is_active', false);

        $this->getJson('/api/habits?active=false')
            ->assertOk()
            ->assertJsonCount(1, 'data.habits');

        $otherHabit = $otherUser->habits()->create([
            'title' => 'Private habit',
            'frequency' => 'daily',
            'target_days' => 3,
            'is_active' => true,
        ]);

        $this->getJson("/api/habits/{$otherHabit->id}")
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->deleteJson("/api/habits/{$habitId}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_cannot_log_same_habit_twice_on_same_day(): void
    {
        Carbon::setTestNow('2026-03-13 09:00:00');

        $user = User::factory()->create();
        $habit = $user->habits()->create([
            'title' => 'Workout',
            'frequency' => 'daily',
            'target_days' => 4,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/habits/{$habit->id}/logs", [
            'note' => 'Morning session',
        ])
            ->assertCreated()
            ->assertJsonPath('data.log.date', '2026-03-13T00:00:00.000000Z');

        $this->postJson("/api/habits/{$habit->id}/logs", [
            'note' => 'Second attempt',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->getJson("/api/habits/{$habit->id}/logs")
            ->assertOk()
            ->assertJsonCount(1, 'data.logs');
    }

    public function test_habit_stats_and_overview_are_calculated_correctly(): void
    {
        Carbon::setTestNow('2026-03-13 09:00:00');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $habitOne = $user->habits()->create([
            'title' => 'Meditate',
            'frequency' => 'daily',
            'target_days' => 7,
            'is_active' => true,
        ]);

        $habitTwo = $user->habits()->create([
            'title' => 'Drink water',
            'frequency' => 'daily',
            'target_days' => 7,
            'is_active' => true,
        ]);

        foreach (['2026-03-10', '2026-03-11', '2026-03-12', '2026-03-13'] as $date) {
            $habitOne->logs()->create(['date' => $date]);
        }

        foreach (['2026-03-07', '2026-03-08', '2026-03-09', '2026-03-10', '2026-03-13'] as $date) {
            $habitTwo->logs()->create(['date' => $date]);
        }

        $this->getJson("/api/habits/{$habitOne->id}/stats")
            ->assertOk()
            ->assertJsonPath('data.current_streak', 4)
            ->assertJsonPath('data.longest_streak', 4)
            ->assertJsonPath('data.total_completions', 4)
            ->assertJsonPath('data.completion_rate', 13.33);

        $this->getJson('/api/stats/overview')
            ->assertOk()
            ->assertJsonPath('data.total_active_habits', 2)
            ->assertJsonPath('data.completed_today', 2)
            ->assertJsonPath('data.habit_with_longest_streak.title', 'Meditate')
            ->assertJsonPath('data.habit_with_longest_streak.longest_streak', 4)
            ->assertJsonPath('data.completion_rate_last_7_days', 64.29);
    }
}
