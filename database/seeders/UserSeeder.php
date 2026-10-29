<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = User::firstOrCreate(
            ['email'     => 'admin@sotytech.com'],
            [
            'name'      => 'Admin',
            'last_name' => 'SOTyTECH',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrador');
    }
}