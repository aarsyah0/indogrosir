<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogin4Test extends TestCase
{
    use RefreshDatabase;

    public function test_debug_login4(): void
    {
        $user = User::factory()->create();
        
        // Start session first
        $this->withSession([]);
        
        $response = $this->post('/login', [
            '_token' => csrf_token(),
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        echo "SUCCESS: Login works" . PHP_EOL;
    }
}
