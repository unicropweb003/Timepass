<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_does_not_set_a_remember_me_cookie(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('api.login'), ['email' => $user->email, 'password' => 'password', 'remember' => true])
            ->assertOk()
            ->assertCookieMissing(Auth::guard()->getRecallerName());
    }

    public function test_admin_login_does_not_set_a_remember_me_cookie(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->post(route('admin.login.submit'), ['email' => $admin->email, 'password' => 'password', 'remember' => 'on'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertCookieMissing(Auth::guard()->getRecallerName());
    }

    public function test_user_is_signed_out_after_the_idle_timeout(): void
    {
        $user = User::factory()->create();
        $idle = config('session.lifetime');

        $sessionId = $this->postJson(route('api.login'), ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->getCookie(config('session.cookie'))
            ->getValue();

        // Each visit restarts the timer, so an active user stays signed in.
        foreach ([1, 2] as $visit) {
            $this->travel($idle - 1)->minutes();
            $this->freshBrowserRequest();
            $this->withCookie(config('session.cookie'), $sessionId)->get('/account')->assertOk();
        }

        $this->travel($idle + 1)->minutes();
        $this->freshBrowserRequest();
        $this->withCookie(config('session.cookie'), $sessionId)->get('/account')->assertRedirect(route('login'));
    }

    public function test_remember_me_cookies_issued_before_the_change_stop_working(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-token']);
        $cookie = $user->id.'|old-token|'.$user->getAuthPassword();

        $this->withCookie(Auth::guard()->getRecallerName(), $cookie)->get('/account')->assertOk();

        (require database_path('migrations/2026_10_22_000001_forget_remember_me_logins.php'))->up();

        $this->assertNull($user->fresh()->remember_token);
        $this->freshBrowserRequest();
        $this->withCookie(Auth::guard()->getRecallerName(), $cookie)->get('/account')->assertRedirect(route('login'));
    }

    /**
     * The test app is shared between requests: drop the signed-in user and
     * session data earlier requests left in memory, so only the cookies sent
     * with the next request can sign the user in (as in a real browser).
     */
    private function freshBrowserRequest(): void
    {
        $this->app['auth']->forgetGuards();
        $this->app['session.store']->flush();
    }
}
