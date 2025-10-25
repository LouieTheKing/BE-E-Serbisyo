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
                'value' => 'E-Serbisyo'
            ],
            [
                'name' => 'app_header',
                'value' => 'Barangay Santoleño'
            ],
            [
                'name' => 'phone',
                'value' => '09123456789'
            ],
            [
                'name' => 'email',
                'value' => 'santol@gmail.com'
            ],
            [
                'name' => 'address',
                'value' => 'Barangay Santol, Balagtas, Bulacan'
            ],
            [
                'name' => 'exact_address',
                'value' => 'VW27+JP4, Unnamed Rd, Balagtas, 3016 Bulacan'
            ],
            [
                'name' => 'google_map',
                'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3858.255964964839!2d120.912136!3d14.8515435!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397acbedc3446fb:0x4a87a27d415902d8!2sSantol%20Barangay%20Hall!5e0!3m2!1sen!2sph!4v1719148800000!5m2!1sen!2sph'
            ],
            [
                'name' => 'telephone',
                'value' => '(0123) 456 789'
            ],
            [
                'name' => 'barangay',
                'value' => 'Santol'
            ],
            [
                'name' => 'logo_url',
                'value' => null
            ]
        ];

        foreach ($configs as $configData) {
            Config::create($configData);
        }
    }
}
