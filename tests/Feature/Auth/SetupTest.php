<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_page_is_available_when_no_user_exists(): void
    {
        $this->get('/setup')
            ->assertOk()
            ->assertSee('Eerste beheerder aanmaken');
    }

    public function test_admin_login_redirects_to_setup_when_no_user_exists(): void
    {
        $this->get('/admin')
            ->assertRedirect('/setup');

        $this->get('/admin/login')
            ->assertRedirect('/setup');
    }

    public function test_setup_creates_first_user_and_logs_in(): void
    {
        $response = $this->post('/setup', [
            'name' => 'Edwin Rasser',
            'email' => 'edwin@example.com',
            'password' => 'SterkWachtwoord123',
            'password_confirmation' => 'SterkWachtwoord123',
        ]);

        $response->assertRedirect('/admin');

        $user = User::query()->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Edwin Rasser', $user->name);
        $this->assertSame('edwin@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('SterkWachtwoord123', $user->password));
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'first_user_created',
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
        ]);
    }

    public function test_setup_redirects_to_admin_when_a_user_already_exists(): void
    {
        User::factory()->create();

        $this->get('/setup')->assertRedirect('/admin');
    }

    public function test_admin_login_is_available_when_a_user_exists(): void
    {
        User::factory()->create();

        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_setup_refuses_to_create_another_user(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->post('/setup', [
            'name' => 'Tweede Admin',
            'email' => 'second@example.com',
            'password' => 'SterkWachtwoord123',
            'password_confirmation' => 'SterkWachtwoord123',
        ])->assertForbidden();

        $this->assertSame(1, User::query()->count());
        $this->assertSame(0, ActivityLog::query()->count());
    }

    public function test_admin_password_can_be_reset_from_artisan(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'OudWachtwoord123',
            'remember_token' => 'token',
        ]);

        $this->artisan('cms:admin:reset-password', [
            'email' => 'admin@example.com',
            '--password' => 'NieuwWachtwoord123',
        ])->assertExitCode(0);

        $user->refresh();

        $this->assertTrue(Hash::check('NieuwWachtwoord123', $user->password));
        $this->assertNull($user->remember_token);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'admin_password_reset',
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
        ]);
    }

    public function test_admin_password_reset_requires_existing_user(): void
    {
        $this->artisan('cms:admin:reset-password', [
            'email' => 'missing@example.com',
            '--password' => 'NieuwWachtwoord123',
        ])->assertExitCode(1);

        $this->assertSame(0, ActivityLog::query()->count());
    }

    public function test_setup_validates_required_account_fields(): void
    {
        $this->from('/setup')->post('/setup', [
            'name' => '',
            'email' => 'geen-email',
            'password' => 'kort',
            'password_confirmation' => 'anders',
        ])
            ->assertRedirect('/setup')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertSame(0, User::query()->count());
    }
}
