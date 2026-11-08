<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_logout(): void
    {
        $user = User::factory()->create();
        
        echo "Before actingAs: auth check = " . var_export(auth()->check(), true) . PHP_EOL;
        $response = $this->actingAs($user)->post('/logout');
        echo "After actingAs+post logout: auth check = " . var_export(auth()->check(), true) . PHP_EOL;
        echo "Response status: " . $response->status() . PHP_EOL;
        echo "Response redirect: " . $response->headers->get('Location') . PHP_EOL;
        
        $this->assertGuest();
    }
}
