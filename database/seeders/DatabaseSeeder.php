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

        foreach (config('admin_seed') as $role => $credentials) {
            if (empty($credentials['email'])) {
                continue; // No implicit privileged accounts.
            }
            validator($credentials, ['email' => 'required|email', 'password' => 'required|string|min:16|max:128'])->validate();
            $existing = User::where('email', $credentials['email'])->first();
            if ($existing) {
                if (! $existing->hasRole($role)) {
                    throw new \RuntimeException('Seeder refuses to promote an existing account.');
                }
                continue; // Never reset existing passwords on rerun.
            }
            $user = User::create([
                'name' => $role === 'super_admin' ? 'Administrator SAMUDERA' : 'Admin Dinas',
                'email' => $credentials['email'],
                'password' => Hash::driver('bcrypt')->make($credentials['password']),
            ]);
            $user->assignRole($role);
        }
    }
}
