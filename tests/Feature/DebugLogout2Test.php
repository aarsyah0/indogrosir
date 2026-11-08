<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogout2Test extends TestCase
{
    use RefreshDatabase;

    public function test_debug_logout2(): void
    {
        $user = User::factory()->create();
        
        $token = csrf_token();
        echo "CSRF token: " . $token . PHP_EOL;
        
        $response = $this->actingAs($user)->post('/logout', [
            '_token' => $token,
        ]);
        echo "Response status: " . $response->status() . PHP_EOL;
        echo "Response redirect: " . $response->headers->get('Location') . PHP_EOL;
        
        $this->assertGuest();
    }
}
