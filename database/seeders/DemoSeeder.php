<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivisionManager;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemPriceHistory;
use App\Models\RequestPeriod;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('atkabt') as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            Settings::put($key, $value);
        }

        $it = Division::query()->create(['code' => 'IT', 'name' => 'Teknologi Informasi', 'is_active' => true]);
        $hr = Division::query()->create(['code' => 'HR', 'name' => 'Sumber Daya Manusia', 'is_active' => true]);
        $gaDiv = Division::query()->create(['code' => 'GA', 'name' => 'General Affairs', 'is_active' => true]);

        $roles = Role::query()->pluck('id', 'slug');

        $users = [
            ['name' => 'Admin Sistem', 'email' => 'admin@atkabt.test', 'role' => 'admin', 'division_id' => null],
            ['name' => 'Staff IT', 'email' => 'staff.it@atkabt.test', 'role' => 'staff', 'division_id' => $it->id],
            ['name' => 'Manager IT', 'email' => 'manager.it@atkabt.test', 'role' => 'manager', 'division_id' => $it->id],
            ['name' => 'Staff HR', 'email' => 'staff.hr@atkabt.test', 'role' => 'staff', 'division_id' => $hr->id],
            ['name' => 'Manager HR', 'email' => 'manager.hr@atkabt.test', 'role' => 'manager', 'division_id' => $hr->id],
            ['name' => 'Petugas GA', 'email' => 'ga@atkabt.test', 'role' => 'ga', 'division_id' => $gaDiv->id],
            ['name' => 'SAM', 'email' => 'sam@atkabt.test', 'role' => 'sam', 'division_id' => null],
            ['name' => 'SM', 'email' => 'sm@atkabt.test', 'role' => 'sm', 'division_id' => null],
        ];

        $created = [];
        foreach ($users as $row) {
            $user = User::query()->create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make('password'),
                'division_id' => $row['division_id'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->roles()->attach($roles[$row['role']]);
            $created[$row['email']] = $user;
        }

        DivisionManager::query()->create([
            'division_id' => $it->id,
            'user_id' => $created['manager.it@atkabt.test']->id,
            'started_at' => now(),
        ]);
        DivisionManager::query()->create([
            'division_id' => $hr->id,
            'user_id' => $created['manager.hr@atkabt.test']->id,
            'started_at' => now(),
        ]);

        $atk = ItemCategory::query()->create(['code' => 'ATK', 'name' => 'Alat Tulis Kantor', 'is_active' => true]);
        $abt = ItemCategory::query()->create(['code' => 'ABT', 'name' => 'Alat Bantu Tulis', 'is_active' => true]);
        $pcs = Unit::query()->create(['code' => 'PCS', 'name' => 'Buah', 'is_active' => true]);
        $rim = Unit::query()->create(['code' => 'RIM', 'name' => 'Rim', 'is_active' => true]);
        $box = Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);

        $items = [
            ['ATK-001', 'Kertas A4 80gsm', $atk->id, $rim->id, 52000, 10],
            ['ATK-002', 'Pulpen Hitam', $atk->id, $box->id, 35000, 5],
            ['ATK-003', 'Spidol Whiteboard', $atk->id, $pcs->id, 8500, 12],
            ['ABT-001', 'Tinta Printer Hitam', $abt->id, $pcs->id, 175000, 4],
            ['ABT-002', 'Stapler Besar', $abt->id, $pcs->id, 45000, 3],
        ];

        foreach ($items as [$code, $name, $cat, $unit, $price, $min]) {
            $item = Item::query()->create([
                'code' => $code,
                'name' => $name,
                'item_category_id' => $cat,
                'unit_id' => $unit,
                'reference_price' => $price,
                'min_stock' => $min,
                'is_active' => true,
            ]);
            ItemPriceHistory::query()->create([
                'item_id' => $item->id,
                'price' => $price,
                'source' => 'master',
                'recorded_by' => $created['admin@atkabt.test']->id,
                'notes' => 'Harga referensi awal',
                'recorded_at' => now(),
            ]);
        }

        Supplier::query()->create([
            'code' => 'SUP-001',
            'name' => 'CV ATK Mandiri',
            'contact_name' => 'Budi',
            'phone' => '08123456789',
            'email' => 'sales@atkmandiri.test',
            'address' => 'Jakarta',
            'is_active' => true,
        ]);

        RequestPeriod::query()->create([
            'name' => now()->translatedFormat('F Y'),
            'year' => (int) now()->year,
            'month' => (int) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'deadline_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'opened_by' => $created['ga@atkabt.test']->id,
        ]);
    }
}
