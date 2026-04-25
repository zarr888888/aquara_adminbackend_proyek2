<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $roleAdminDinas = Role::firstOrCreate(['name' => 'Admin Dinas', 'guard_name' => 'web']);

        $superAdmin = User::create([
            'name' => 'Developer AQUARA',
            'email' => 'dev@aquara.com',
            'password' => Hash::make('0808'), 
        ]);
        $superAdmin->assignRole($roleSuperAdmin);

        $adminDinas = User::create([
            'name' => 'Admin AQUARA',
            'email' => 'admin@aquara.test',
            'password' => Hash::make('0808'),
        ]);
        $adminDinas->assignRole($roleAdminDinas);
    }
}
