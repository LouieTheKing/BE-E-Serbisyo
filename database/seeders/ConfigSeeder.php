<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'name' => 'app_name',
                'value' => 'Default Name'
            ],
            [
                'name' => 'phone',
                'value' => '09123456789'
            ],
            [
                'name' => 'email',
                'value' => 'santol@gmail.com'
            ]
        ];

        foreach ($configs as $configData) {
            Config::create($configData);
        }
    }
}
