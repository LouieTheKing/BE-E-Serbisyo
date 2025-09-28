<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\DocumentRequirement;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example documents
        $documents = [
            [
                'document_name' => 'Transcript of Records',
                'description' => 'Official academic transcript issued by the school.',
                'status' => 'active',
                'requirements' => [
                    [
                        'name' => 'Request Form',
                        'description' => 'Fill out the official request form for transcript.'
                    ],
                    [
                        'name' => 'Payment Receipt',
                        'description' => 'Proof of payment for transcript processing.'
                    ]
                ]
            ],
            [
                'document_name' => 'Good Moral Certificate',
                'description' => 'Certification of good moral character from the school.',
                'status' => 'active',
                'requirements' => [
                    [
                        'name' => 'Application Form',
                        'description' => 'Signed application form for certificate request.'
                    ],
                    [
                        'name' => 'Clearance',
                        'description' => 'School clearance to certify good moral.'
                    ]
                ]
            ],
            [
                'document_name' => 'Diploma Copy',
                'description' => 'Certified true copy of diploma issued by registrar.',
                'status' => 'active',
                'requirements' => [
                    [
                        'name' => 'ID Card',
                        'description' => 'Valid school or government-issued ID.'
                    ],
                    [
                        'name' => 'Authorization Letter',
                        'description' => 'If claiming by proxy, a signed authorization letter.'
                    ]
                ]
            ]
        ];

        foreach ($documents as $docData) {
            $requirements = $docData['requirements'];
            unset($docData['requirements']);

            // Create document
            $document = Document::create($docData);

            // Attach requirements
            foreach ($requirements as $req) {
                DocumentRequirement::create([
                    'name' => $req['name'],
                    'description' => $req['description'],
                    'document' => $document->id
                ]);
            }
        }
    }
}
