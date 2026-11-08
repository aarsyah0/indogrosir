<?php

namespace Tests\Feature;

use App\Enums\RequisitionStatus;
use App\Models\Division;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Models\Role;
use App\Models\User;
use App\Services\ReportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_a_division_cannot_read_company_wide_reports(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $division = Division::query()->create(['code' => 'SEC', 'name' => 'Restricted division', 'is_active' => true]);
        $staff = User::factory()->create(['division_id' => null]);
        $staff->roles()->attach(Role::query()->where('slug', 'staff')->value('id'));

        $period = RequestPeriod::query()->create([
            'name' => 'October 2026',
            'year' => 2026,
            'month' => 10,
            'start_date' => '2026-10-01',
            'deadline_date' => '2026-10-31',
            'status' => 'open',
        ]);
        Requisition::query()->create([
            'number' => 'REQ-SEC-001',
            'request_period_id' => $period->id,
            'division_id' => $division->id,
            'requester_id' => $staff->id,
            'status' => RequisitionStatus::ApprovedForPurchase,
            'estimated_total' => 900000,
        ]);

        $reports = app(ReportService::class);
        $monthly = $reports->monthly($staff, ['year' => 2026, 'month' => 10]);
        $charts = $reports->charts(['year' => 2026], $staff);

        $this->assertSame(0, $monthly['total_requisitions']);
        $this->assertSame(0.0, $monthly['actual']);
        $this->assertSame(0.0, (float) array_sum($charts['spend']));
        $this->assertCount(0, $charts['byDivision']);
        $this->assertSame(0.0, $charts['estimateVsActual']['actual']);

        $this->actingAs($staff)
            ->get(route('charts.index', ['year' => 2026]))
            ->assertOk()
            ->assertDontSee('Restricted division');
    }
}
