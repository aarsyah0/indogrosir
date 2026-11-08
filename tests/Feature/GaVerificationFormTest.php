<?php

namespace Tests\Feature;

use App\Enums\RequisitionStatus;
use App\Models\Division;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionVersion;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaVerificationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_ga_form_submits_final_quantities_to_sam(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $division = Division::query()->create(['code' => 'TST', 'name' => 'Test division', 'is_active' => true]);
        $ga = User::factory()->create(['division_id' => $division->id]);
        $requester = User::factory()->create(['division_id' => $division->id]);
        $ga->roles()->attach(Role::query()->where('slug', 'ga')->value('id'));

        $period = RequestPeriod::query()->create([
            'name' => 'October 2026',
            'year' => 2026,
            'month' => 10,
            'start_date' => '2026-10-01',
            'deadline_date' => '2026-10-31',
            'status' => 'open',
        ]);
        $requisition = Requisition::query()->create([
            'number' => 'REQ-GA-001',
            'request_period_id' => $period->id,
            'division_id' => $division->id,
            'requester_id' => $requester->id,
            'status' => RequisitionStatus::PendingGa,
            'current_version' => 1,
            'estimated_total' => 100,
        ]);
        $version = RequisitionVersion::query()->create([
            'requisition_id' => $requisition->id,
            'version_number' => 1,
            'created_by' => $requester->id,
            'estimated_total' => 100,
        ]);
        $category = ItemCategory::query()->create(['code' => 'TST', 'name' => 'Test category', 'is_active' => true]);
        $unit = Unit::query()->create(['code' => 'PCS', 'name' => 'Pieces', 'is_active' => true]);
        $item = Item::query()->create([
            'code' => 'TST-001',
            'name' => 'Test item',
            'item_category_id' => $category->id,
            'unit_id' => $unit->id,
            'reference_price' => 10,
            'min_stock' => 0,
            'is_active' => true,
        ]);
        $line = RequisitionItem::query()->create([
            'requisition_id' => $requisition->id,
            'requisition_version_id' => $version->id,
            'item_id' => $item->id,
            'item_code_snapshot' => $item->code,
            'item_name_snapshot' => $item->name,
            'category_name_snapshot' => $category->name,
            'unit_name_snapshot' => $unit->name,
            'quantity_requested' => 10,
            'quantity_manager_approved' => 10,
            'reference_unit_price' => 10,
            'estimated_subtotal' => 100,
            'need_reason' => 'Test requirement',
        ]);

        $this->actingAs($ga)
            ->get(route('approvals.ga.show', $requisition))
            ->assertOk()
            ->assertSee('lines['.$line->id.'][quantity_ga_final]', false)
            ->assertSee('lines['.$line->id.'][ga_change_reason]', false);

        $this->post(route('approvals.ga.decide', $requisition), [
            'intent' => 'submit_sam',
            'lines' => [
                $line->id => [
                    'id' => $line->id,
                    'quantity_ga_recommended' => 8,
                    'quantity_ga_final' => 8,
                    'ga_change_reason' => 'Disesuaikan berdasarkan stok',
                ],
            ],
        ])->assertRedirect(route('approvals.ga.index'));

        $this->assertSame(RequisitionStatus::PendingSam, $requisition->fresh()->status);
        $this->assertSame('8.00', $line->fresh()->quantity_ga_final);
        $this->assertSame('Disesuaikan berdasarkan stok', $line->fresh()->ga_change_reason);
    }
}