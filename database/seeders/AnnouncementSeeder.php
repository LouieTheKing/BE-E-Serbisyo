<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $announcements = [
            [
                'type' => 'information',
                'description' => 'System maintenance scheduled on October 1, 2025. Expect downtime from 12:00 AM to 2:00 AM.',
                'images' => null,
            ],
            [
                'type' => 'problem',
                'description' => 'Some users are experiencing login issues. Our team is investigating.',
                'images' => ['announcements/problem1.png', 'announcements/problem2.jpg'],
            ],
            [
                'type' => 'warning',
                'description' => 'Beware of phishing emails pretending to be from our support team. Always check the sender address.',
                'images' => ['announcements/warning1.jpg'],
            ],
            [
                'type' => 'information',
                'description' => 'New features have been added to the dashboard. Check them out now!',
                'images' => null,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }
    }
}
