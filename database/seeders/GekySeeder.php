<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GekySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@gekychat.com'],
            [
                'name' => 'Geky Admin',
                'email' => 'admin@gekychat.com',
                'password' => Hash::make('Gyabaa2000;'),
                'phone' => '0240000000',
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Geky admin user created/updated successfully!');
        $this->command->info('Email: admin@gekychat.com');
        $this->command->info('Password: Gyabaa2000;');
        $this->command->info('Role: admin');
    }
}
