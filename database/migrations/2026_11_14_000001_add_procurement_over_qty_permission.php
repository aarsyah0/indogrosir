<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->text('over_qty_reason')->nullable();
        });

        $now = now();
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'procurement.authorize_over_qty'],
            [
                'name' => 'Mengotorisasi pembelian melebihi kuantitas disetujui',
                'group_name' => 'operations',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $gaRoleId = DB::table('roles')->where('slug', 'ga')->value('id');
        $permissionId = DB::table('permissions')->where('slug', 'procurement.authorize_over_qty')->value('id');

        if ($gaRoleId && $permissionId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $gaRoleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'procurement.authorize_over_qty')->value('id');

        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->dropColumn('over_qty_reason');
        });
    }
};
