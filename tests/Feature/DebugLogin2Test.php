<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogin2Test extends TestCase
{
    use RefreshDatabase;

    public function test_debug_login2(): void
    {
        $user = User::factory()->create();
        
        // Get CSRF token first
        $token = csrf_token();
        $sessionToken = session('token') ?: null;
        
        echo "csrf_token() = " . $token . PHP_EOL;
        echo "session('token') = " . ($sessionToken ?? 'null') . PHP_EOL;
        
        $response = $this->post('/login', [
            '_token' => $token,
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        echo "Response status: " . $response->status() . PHP_EOL;
        echo "Response body: " . substr($response->baseResponse->getContent(), 0, 200) . PHP_EOL;
        
        $this->assertAuthenticated();
    }
}
