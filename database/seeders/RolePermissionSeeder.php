<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions=[
            'profile.view',
            'profile.create',
            'profile.update',
            'profile.delete',

        ];
        $profilePermissions = [
            'profile.view',
            'profile.create',
            'profile.update',
            'profile.delete',
        ];

        foreach($permissions as $permission)
        {

            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }


        $allApiPermissions = Permission::where('guard_name', 'api')->get();
        $allWebPermissions = Permission::where('guard_name', 'web')->get();

        Role::firstOrCreate(['name'=>'player', 'guard_name' => 'api'])->syncPermissions($profilePermissions);
        Role::firstOrCreate(['name'=>'player', 'guard_name' => 'web'])->syncPermissions($profilePermissions);


        Role::firstOrCreate(['name'=>'stadium_owner', 'guard_name' => 'api'])->syncPermissions();
        Role::firstOrCreate(['name'=>'stadium_owner', 'guard_name' => 'web'])->syncPermissions();


        Role::firstOrCreate(['name'=>'admin', 'guard_name' => 'api'])->syncPermissions($allApiPermissions);
        Role::firstOrCreate(['name'=>'admin', 'guard_name' => 'web'])->syncPermissions($allWebPermissions);


    }
}
