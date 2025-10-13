<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // unique key
            [
                'name'     => 'Admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'otp'      => null,
            ]
        );


        $extras = [
            ['name' => 'Manager', 'email' => 'manager@example.com', 'role' => 'manager'],
            ['name' => 'Kitchen', 'email' => 'kitchen@example.com', 'role' => 'kitchen'],
            ['name' => 'Waiter',  'email' => 'waiter@example.com',  'role' => 'waiter'],
            ['name' => 'Customer','email' => 'customer@example.com','role' => 'customer'],
        ];

        foreach ($extras as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'     => $u['name'],
                    'password' => Hash::make('password123'),
                    'role'     => $u['role'],
                    'otp'      => null,
                ]
            );
        }
    }
}
