<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogout3Test extends TestCase
{
    use RefreshDatabase;

    public function test_debug_logout3(): void
    {
        $user = User::factory()->create();
        
        // Start session so CSRF token is generated
        $this->withSession([]);
        $token = csrf_token();
        echo "CSRF token: " . $token . PHP_EOL;
        
        $response = $this->actingAs($user)->post('/logout', [
            '_token' => $token,
        ]);
        echo "Response status: " . $response->status() . PHP_EOL;
        echo "Response redirect: " . $response->headers->get('Location') ?? 'null' . PHP_EOL;
        
        echo "After logout: auth check = " . var_export(auth()->check(), true) . PHP_EOL;
        
        $this->assertGuest();
    }
}
