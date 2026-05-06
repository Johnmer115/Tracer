<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Dean test account
        Account::create([
            'username' => 'dean_admin',
            'usertype' => 'Dean_OSA',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Create Staff OSA test account
        Account::create([
            'username' => 'staff_osa1',
            'usertype' => 'Staff_OSA',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Create Branch OSA test account
        Account::create([
            'username' => 'branch_osa1',
            'usertype' => 'Branch_OSA',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'branch_id' => null,
        ]);
    }
}
