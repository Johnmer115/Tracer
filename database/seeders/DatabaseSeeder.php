<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
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


         // =========================
        // Seed Branches
        // =========================

        Branch::create([
            'name' => 'Juan Sumulong Campus',
            'location' => '2600 Legarda St., Sampaloc, Manila',
            'code' => 'AU_MAIN',
        ]);

        Branch::create([
            'name' => 'Jose Abad Santos Campus',
            'location' => '3258 Taft Avenue, Pasay City',
            'code' => 'AU_PASAY',
        ]);

        Branch::create([
            'name' => 'Andres Bonifacio Campus',
            'location' => 'Pag-asa St., Brgy. Caniogan, Pasig City',
            'code' => 'AU_PASIG',
        ]);

        Branch::create([
            'name' => 'Plaridel Campus',
            'location' => '53 Gen. Kalentong St., Mandaluyong City',
            'code' => 'AU_MANDALUYONG',
        ]);

        Branch::create([
            'name' => 'Apolinario Mabini Campus',
            'location' => 'Menlo St., Taft Ave., Pasay City',
            'code' => 'AU_MABINI',
        ]);

        Branch::create([
            'name' => 'Elisa Esguerra Campus',
            'location' => 'Gen. Luna corner Esguerra St., Bayan-Bayanan, Malabon City',
            'code' => 'AU_MALABON_EE',
        ]);

        Branch::create([
            'name' => 'Jose Rizal Campus',
            'location' => 'Gov. Pascual St., Malabon City',
            'code' => 'AU_MALABON_JR',
        ]);
    }
}
