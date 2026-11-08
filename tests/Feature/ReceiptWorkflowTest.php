<?php

namespace Tests\Feature;

use App\Enums\ProcurementStatus;
use App\Enums\ReceiptStatus;
use App\Enums\RequisitionStatus;
use App\Models\Division;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionVersion;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use App\Services\ReceiptService;
use App\Services\ReportService;
use App\Services\ProcurementService;
use App\Exceptions\BusinessRuleException;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspection_status_is_not_overwritten_when_procurement_status_refreshes(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $division = Division::query()->create(['code' => 'TST', 'name' => 'Test', 'is_active' => true]);
        $user = User::factory()->create(['division_id' => $division->id]);
        $user->roles()->attach(Role::query()->where('slug', 'ga')->value('id'));

        $period = RequestPeriod::query()->create([
            'name' => 'Test period',
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
            'requester_id' => $user->id,
            'status' => RequisitionStatus::ApprovedForPurchase,
            'procurement_status' => ProcurementStatus::Partial,
            'receipt_status' => ReceiptStatus::Awaiting,
            'current_version' => 1,
            'estimated_total' => 100,
        ]);
        RequisitionVersion::query()->create([
            'requisition_id' => $requisition->id,
            'version_number' => 1,
            'created_by' => $user->id,
            'estimated_total' => 100,
        ]);

        $category = ItemCategory::query()->create(['code' => 'TST', 'name' => 'Test', 'is_active' => true]);
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
        $version = $requisition->latestVersion();
        RequisitionItem::query()->create([
            'requisition_id' => $requisition->id,
            'requisition_version_id' => $version->id,
            'item_id' => $item->id,
            'item_code_snapshot' => $item->code,
            'item_name_snapshot' => $item->name,
            'category_name_snapshot' => $category->name,
            'unit_name_snapshot' => $unit->name,
            'quantity_requested' => 10,
            'quantity_manager_approved' => 10,
            'quantity_ga_recommended' => 10,
            'quantity_ga_final' => 10,
            'quantity_sam_approved' => 10,
            'quantity_sm_approved' => 10,
            'reference_unit_price' => 10,
            'estimated_subtotal' => 100,
            'need_reason' => 'Test stock replenishment',
        ]);
        $supplier = Supplier::query()->create([
            'code' => 'TST-001',
            'name' => 'Test supplier',
            'is_active' => true,
        ]);
        try {
            app(ProcurementService::class)->create($user, $requisition, [
                'supplier_id' => $supplier->id,
                'purchased_at' => '2026-10-10',
                'items' => [
                    ['item_id' => $item->id, 'quantity_purchased' => 6, 'unit_price' => 10],
                    ['item_id' => $item->id, 'quantity_purchased' => 6, 'unit_price' => 10],
                ],
            ]);
            $this->fail('Duplicate procurement lines must be rejected.');
        } catch (BusinessRuleException $exception) {
            $this->assertSame('Barang duplikat tidak diizinkan dalam satu pembelian.', $exception->getMessage());
        }

        $order = ProcurementOrder::query()->create([
            'number' => 'PO-TEST-001',
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier->id,
            'purchased_at' => '2026-10-10',
            'actual_total' => 100,
            'created_by' => $user->id,
        ]);
        ProcurementOrderItem::query()->create([
            'procurement_order_id' => $order->id,
            'item_id' => $item->id,
            'item_code_snapshot' => $item->code,
            'item_name_snapshot' => $item->name,
            'unit_name_snapshot' => $unit->name,
            'quantity_approved' => 10,
            'quantity_purchased' => 10,
            'unit_price' => 10,
            'subtotal' => 100,
        ]);
        $otherSupplier = Supplier::query()->create([
            'code' => 'TST-002',
            'name' => 'Other supplier',
            'is_active' => true,
        ]);
        $otherOrder = ProcurementOrder::query()->create([
            'number' => 'PO-TEST-002',
            'requisition_id' => $requisition->id,
            'supplier_id' => $otherSupplier->id,
            'purchased_at' => '2026-10-12',
            'actual_total' => 50,
            'created_by' => $user->id,
        ]);
        ProcurementOrderItem::query()->create([
            'procurement_order_id' => $otherOrder->id,
            'item_id' => $item->id,
            'item_code_snapshot' => $item->code,
            'item_name_snapshot' => $item->name,
            'unit_name_snapshot' => $unit->name,
            'quantity_approved' => 10,
            'quantity_purchased' => 5,
            'unit_price' => 10,
            'subtotal' => 50,
        ]);

        $receipts = app(ReceiptService::class);
        $firstPayload = [
            'submission_key' => 'f9a11b0c-0e31-4b7f-a145-75811177b125',
            'received_at' => '2026-10-10',
            'notes' => null,
            'items' => [[
                'item_id' => $item->id,
                'quantity_received' => 2,
                'condition' => 'rusak',
                'discrepancy_notes' => 'Kemasan rusak',
            ]],
        ];
        $firstReceipt = $receipts->receive($user, $order, $firstPayload);

        $receipts->receive($user, $order, [
            'submission_key' => 'f9a11b0c-0e31-4b7f-a145-75811177b126',
            'received_at' => '2026-10-11',
            'notes' => null,
            'items' => [[
                'item_id' => $item->id,
                'quantity_received' => 3,
                'condition' => 'baik',
                'discrepancy_notes' => null,
            ]],
        ]);
        $duplicateReceipt = $receipts->receive($user, $order, $firstPayload);

        $this->assertSame(ReceiptStatus::NeedsInspection, $requisition->fresh()->receipt_status);
        $this->assertSame($firstReceipt->id, $duplicateReceipt->id);
        $this->assertSame(2, $requisition->procurementOrders()->first()->receipts()->count());
        $this->assertSame(5.0, (float) StockTransaction::query()->where('item_id', $item->id)->sum('quantity'));

        $this->actingAs($user)
            ->get(route('procurements.show', $order))
            ->assertOk()
            ->assertSee(route('receipts.create', ['procurement_id' => $order->id]), false)
            ->assertSee('Catat penerimaan');
        $this->get(route('receipts.create', ['procurement_id' => $order->id]))
            ->assertOk()
            ->assertSee('Sisa yang belum diterima: 5.00');

        $recap = app(ReportService::class)->recap($user, [
            'year' => 2026,
            'month' => 10,
            'supplier_id' => $supplier->id,
        ]);
        $charts = app(ReportService::class)->charts([
            'year' => 2026,
            'division_id' => $division->id,
            'item_category_id' => $category->id,
            'item_id' => $item->id,
        ], $user);
        $divisionCharts = app(ReportService::class)->charts([
            'year' => 2026,
            'division_id' => $division->id,
        ], $user);
        $supplierReport = app(ReportService::class)->monthly($user, [
            'year' => 2026,
            'month' => 10,
            'supplier_id' => $otherSupplier->id,
        ]);

        $this->assertCount(1, $recap);
        $this->assertSame(10.0, $recap->first()['purchased']);
        $this->assertSame(150.0, (float) array_sum($charts['spend']));
        $this->assertSame(150.0, (float) $charts['byCategory']->first()->total);
        $this->assertSame(150.0, (float) array_sum($divisionCharts['spend']));
        $this->assertSame(150.0, (float) $divisionCharts['byDivision']->first()->total);
        $this->assertSame(50.0, $supplierReport['actual']);
        $this->assertSame(5.0, $supplierReport['qty_purchased']);
        $this->assertSame(0.0, $supplierReport['qty_received']);

        $limitRequisition = Requisition::query()->create([
            'number' => 'REQ-TEST-002',
            'request_period_id' => $period->id,
            'division_id' => $division->id,
            'requester_id' => $user->id,
            'status' => RequisitionStatus::ApprovedForPurchase,
            'current_version' => 1,
            'estimated_total' => 100,
        ]);
        $limitVersion = RequisitionVersion::query()->create([
            'requisition_id' => $limitRequisition->id,
            'version_number' => 1,
            'created_by' => $user->id,
            'estimated_total' => 100,
        ]);
        RequisitionItem::query()->create([
            'requisition_id' => $limitRequisition->id,
            'requisition_version_id' => $limitVersion->id,
            'item_id' => $item->id,
            'item_code_snapshot' => $item->code,
            'item_name_snapshot' => $item->name,
            'category_name_snapshot' => $category->name,
            'unit_name_snapshot' => $unit->name,
            'quantity_requested' => 10,
            'quantity_manager_approved' => 10,
            'quantity_ga_final' => 10,
            'quantity_sam_approved' => 10,
            'quantity_sm_approved' => 10,
            'reference_unit_price' => 10,
            'estimated_subtotal' => 100,
            'need_reason' => 'Cumulative purchase test',
        ]);

        $procurement = app(ProcurementService::class);
        $procurement->create($user, $limitRequisition, [
            'supplier_id' => $supplier->id,
            'purchased_at' => '2026-10-13',
            'items' => [['item_id' => $item->id, 'quantity_purchased' => 6, 'unit_price' => 10]],
        ]);
        try {
            $procurement->create($user, $limitRequisition, [
                'supplier_id' => $supplier->id,
                'purchased_at' => '2026-10-14',
                'items' => [['item_id' => $item->id, 'quantity_purchased' => 5, 'unit_price' => 10]],
            ]);
            $this->fail('Cumulative purchase quantity above approval must be rejected.');
        } catch (BusinessRuleException $exception) {
            $this->assertStringContainsString('melebihi kuantitas', $exception->getMessage());
        }
    }
}
