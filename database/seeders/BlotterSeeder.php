<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blotter;
use Illuminate\Support\Str;

class BlotterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleData = [
            [
                'case_number' => 'BLT-' . Str::random(6),
                'complainant_name' => 'Juan Dela Cruz',
                'respondent_name' => 'Pedro Santos',
                'additional_respondent' => ['Maria Reyes', 'Jose Lopez'],
                'complaint_details' => 'Noise disturbance late at night.',
                'relief_sought' => 'Cease and desist from creating disturbance.',
                'case_type' => 'Barangay Dispute',
                'date_filed' => now(),
                'status' => 'filed',
                'received_by' => 1,
                'created_by' => 1,
            ],
            [
                'case_number' => 'BLT-' . Str::random(6),
                'complainant_name' => 'Ana Mendoza',
                'respondent_name' => 'Carlos Garcia',
                'additional_respondent' => [],
                'complaint_details' => 'Boundary dispute regarding property.',
                'relief_sought' => 'Clarification and boundary agreement.',
                'case_type' => 'Property Dispute',
                'date_filed' => now()->subDays(5),
                'status' => 'ongoing',
                'received_by' => 1,
                'created_by' => 1,
            ],
            [
                'case_number' => 'BLT-' . Str::random(6),
                'complainant_name' => 'Mario Villanueva',
                'respondent_name' => 'Roberto Ramos',
                'additional_respondent' => ['Elena Cruz'],
                'complaint_details' => 'Physical altercation during community event.',
                'relief_sought' => 'Apology and settlement of damages.',
                'case_type' => 'Assault',
                'date_filed' => now()->subDays(10),
                'status' => 'settled',
                'received_by' => 1,
                'created_by' => 1,
            ],
        ];

        foreach ($sampleData as $data) {
            Blotter::create($data);
        }
    }
}
