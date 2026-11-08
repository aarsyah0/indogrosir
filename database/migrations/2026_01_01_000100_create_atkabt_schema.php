<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('group_name');
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('division_id');
        });

        Schema::create('division_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->index(['division_id', 'user_id', 'ended_at']);
        });

        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('item_category_id')->constrained('item_categories');
            $table->foreignId('unit_id')->constrained();
            $table->text('description')->nullable();
            $table->decimal('reference_price', 15, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('last_price', 15, 2)->nullable();
            $table->unique(['supplier_id', 'item_id']);
        });

        Schema::create('item_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->string('source', 32);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['item_id', 'recorded_at']);
        });

        Schema::create('request_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('start_date');
            $table->date('deadline_date');
            $table->string('status', 20)->default('open');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('request_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('division_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 40);
            $table->string('procurement_status', 30)->default('not_purchased');
            $table->string('receipt_status', 30)->default('not_received');
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->timestamps();
            $table->index(['status', 'division_id']);
            $table->index(['request_period_id', 'division_id']);
        });

        Schema::create('requisition_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('created_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['requisition_id', 'version_number']);
        });

        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requisition_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->string('item_code_snapshot');
            $table->string('item_name_snapshot');
            $table->string('category_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('quantity_requested', 12, 2);
            $table->decimal('quantity_manager_approved', 12, 2)->nullable();
            $table->decimal('quantity_ga_recommended', 12, 2)->nullable();
            $table->decimal('quantity_ga_final', 12, 2)->nullable();
            $table->decimal('quantity_sam_approved', 12, 2)->nullable();
            $table->decimal('quantity_sm_approved', 12, 2)->nullable();
            $table->decimal('reference_unit_price', 15, 2);
            $table->decimal('estimated_subtotal', 15, 2);
            $table->text('need_reason');
            $table->text('ga_change_reason')->nullable();
            $table->json('quantity_change_log')->nullable();
            $table->unique(['requisition_version_id', 'item_id']);
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requisition_version_id')->constrained()->cascadeOnDelete();
            $table->string('step', 20);
            $table->string('status', 20)->default('pending');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->unique(['requisition_version_id', 'step']);
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requisition_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users');
            $table->string('role_slug');
            $table->string('step', 20);
            $table->string('action', 20);
            $table->text('comments')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
            $table->index(['requisition_id', 'decided_at']);
        });

        Schema::create('procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('requisition_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('purchased_at');
            $table->string('invoice_number')->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('extra_cost', 15, 2)->default(0);
            $table->decimal('actual_total', 15, 2)->default(0);
            $table->boolean('over_qty_authorized')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('procurement_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->string('item_code_snapshot');
            $table->string('item_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('quantity_approved', 12, 2);
            $table->decimal('quantity_purchased', 12, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('procurement_order_id')->constrained()->restrictOnDelete();
            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_purchased', 12, 2);
            $table->decimal('quantity_received', 12, 2);
            $table->decimal('quantity_short', 12, 2)->default(0);
            $table->string('condition', 30)->default('baik');
            $table->text('discrepancy_notes')->nullable();
        });

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_delta', 12, 2);
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('last_number')->default(0);
            $table->unique(['prefix', 'year', 'month']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('procurement_order_items');
        Schema::dropIfExists('procurement_orders');
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('requisition_items');
        Schema::dropIfExists('requisition_versions');
        Schema::dropIfExists('requisitions');
        Schema::dropIfExists('request_periods');
        Schema::dropIfExists('item_price_histories');
        Schema::dropIfExists('supplier_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('items');
        Schema::dropIfExists('units');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('division_managers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
            $table->dropColumn('is_active');
        });
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
