<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            // DocumentSeeder::class,
            // AnnouncementSeeder::class,
            // BlotterSeeder::class,
            ConfigSeeder::class,
        ]);
    }

}
