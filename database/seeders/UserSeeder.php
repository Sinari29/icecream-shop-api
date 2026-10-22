<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Demo-only password for local development and testing.
        $password = 'Password123!';

        $users = [
            ['name' => 'Shop Owner', 'email' => 'owner@example.com', 'role' => Role::Owner],
            ['name' => 'Shop Manager', 'email' => 'manager@example.com', 'role' => Role::Manager],
            ['name' => 'Shop Cashier', 'email' => 'cashier@example.com', 'role' => Role::Cashier],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                ['name' => $user['name'], 'role' => $user['role'], 'password' => $password]
            );
        }
    }
}