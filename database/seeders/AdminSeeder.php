<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {

        $role = Role::firstOrCreate(['name' => 'admin']);


        $admin = User::firstOrCreate(
            ['email' => 'donaassafin222@gmail.com'],
            [

                'password' => Hash::make('1234567P9'),
            ]
        );


        if (!$admin->hasRole('admin')) {
            $admin->assignRole($role);
        }

        $this->command->info(' Admin user created successfully with role "admin".');
    }
}
