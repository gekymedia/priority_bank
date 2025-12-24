<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (CEO)
        User::create([
            'name' => 'Admin CEO',
            'email' => 'admin@prioritybank.com',
            'password' => bcrypt('password'),
            'phone' => '+233501234567',
            'role' => 'admin',
            'preferred_currency' => 'GHS',
            'notification_email' => true,
            'notification_browser' => true,
            'theme' => 'light',
        ]);

        // Create regular users (friends)
        $users = [
            ['name' => 'John Mensah', 'email' => 'john@example.com', 'phone' => '+233507654321'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com', 'phone' => '+233547890123'],
            ['name' => 'Michael Osei', 'email' => 'michael@example.com', 'phone' => '+233551234567'],
            ['name' => 'Grace Asante', 'email' => 'grace@example.com', 'phone' => '+233562345678'],
            ['name' => 'David Boateng', 'email' => 'david@example.com', 'phone' => '+233573456789'],
        ];

        foreach ($users as $userData) {
            User::create(array_merge($userData, [
                'password' => bcrypt('password'),
                'role' => 'user',
                'preferred_currency' => 'GHS',
                'notification_email' => true,
                'notification_browser' => true,
                'theme' => 'light',
            ]));
        }
    }
}
