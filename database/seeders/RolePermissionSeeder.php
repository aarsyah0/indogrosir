<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::GROUPS as $group => $permissions) {
            foreach ($permissions as $slug => $name) {
                Permission::query()->updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'group_name' => $group]
                );
            }
        }

        $names = [
            'admin' => 'Admin Sistem',
            'staff' => 'Staff Divisi',
            'manager' => 'Manager Divisi',
            'ga' => 'General Affairs',
            'sam' => 'SAM',
            'sm' => 'SM',
        ];

        foreach (PermissionCatalog::ROLE_PERMISSIONS as $slug => $permissionSlugs) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $names[$slug], 'description' => $names[$slug]]
            );
            $ids = Permission::query()->whereIn('slug', $permissionSlugs)->pluck('id');
            $role->permissions()->sync($ids);
        }
    }
}
