<?php

namespace Tests\Feature;

use App\Enums\RequisitionStatus;
use App\Models\Division;
use App\Models\DivisionManager;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Models\RequisitionVersion;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalDecisionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_decision_form_uses_validator_action_values(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $division = Division::query()->create(['code' => 'TST', 'name' => 'Test division', 'is_active' => true]);
        $manager = User::factory()->create(['division_id' => $division->id]);
        $requester = User::factory()->create(['division_id' => $division->id]);
        $manager->roles()->attach(Role::query()->where('slug', 'manager')->value('id'));
        DivisionManager::query()->create([
            'division_id' => $division->id,
            'user_id' => $manager->id,
            'started_at' => now(),
        ]);

        $period = RequestPeriod::query()->create([
            'name' => 'October 2026',
            'year' => 2026,
            'month' => 10,
            'start_date' => '2026-10-01',
            'deadline_date' => '2026-10-31',
            'status' => 'open',
        ]);
        $requisition = Requisition::query()->create([
            'number' => 'REQ-TEST-001',
            'request_period_id' => $period->id,
            'division_id' => $division->id,
            'requester_id' => $requester->id,
            'status' => RequisitionStatus::PendingManager,
            'current_version' => 1,
            'estimated_total' => 0,
        ]);
        RequisitionVersion::query()->create([
            'requisition_id' => $requisition->id,
            'version_number' => 1,
            'created_by' => $requester->id,
            'estimated_total' => 0,
        ]);

        $this->actingAs($manager)
            ->get(route('approvals.manager.show', $requisition))
            ->assertOk()
            ->assertSee('value="approved"', false)
            ->assertSee('value="rejected"', false)
            ->assertSee('value="returned"', false)
            ->assertDontSee('value="approve"', false)
            ->assertDontSee('value="reject"', false)
            ->assertDontSee('value="return"', false);

        $this->post(route('approvals.manager.decide', $requisition), [
            'action' => 'approved',
            'comment' => null,
        ])->assertRedirect(route('approvals.manager.index'));

        $this->assertSame(RequisitionStatus::PendingGa, $requisition->fresh()->status);
    }
}
