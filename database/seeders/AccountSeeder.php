<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin account
        Account::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('pass123'),
            'status' => 'active',
            'type' => 'admin',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'sex' => 'Male',
            'nationality' => 'Filipino',
            'birthday' => '1990-01-01',
            'contact_no' => '09123456789',
            'municipality' => 'Sample City',
            'barangay' => 'Barangay Uno',
            'house_no' => '123',
            'street' => 'Main Street',
            'zip_code' => '1000',
            'birth_place' => 'Bulacan'
        ]);

        // Staff account
        Account::create([
            'email' => 'staff@example.com',
            'password' => Hash::make('pass123'),
            'status' => 'active',
            'type' => 'staff',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'sex' => 'Male',
            'nationality' => 'Filipino',
            'birthday' => '1995-05-15',
            'contact_no' => '09987654321',
            'municipality' => 'Sample City',
            'barangay' => 'Barangay Dos',
            'house_no' => '456',
            'street' => 'Second Street',
            'zip_code' => '1001',
            'birth_place' => 'Bulacan'
        ]);

        // Resident account
        Account::create([
            'email' => 'resident@example.com',
            'password' => Hash::make('pass123'),
            'status' => 'active',
            'type' => 'resident',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'sex' => 'Female',
            'nationality' => 'Filipino',
            'birthday' => '2000-08-20',
            'contact_no' => '09111222333',
            'municipality' => 'Sample City',
            'barangay' => 'Barangay Tres',
            'house_no' => '789',
            'street' => 'Third Street',
            'zip_code' => '1002',
            'birth_place' => 'Bulacan'
        ]);
    }
}
