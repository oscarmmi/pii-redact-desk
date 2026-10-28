<?php

namespace Database\Seeders;

use App\Models\RedactJob;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            'Harbor Desk order HD-48291 arrived with a cracked lid. Please email customer-one@example.com or call +1 (202) 555-0147 with the replacement options.',
            'The return label for HD-73014 is missing. Send instructions to customer-two@example.com; the contact number is +1-202-555-0182.',
            'The delivery status for HD-15906 has not changed in four days. Send an update to customer-three@example.com or phone (202) 555-0126.',
            'Please update the shipping address on HD-90427 before dispatch. Confirm the change with customer-four@example.com at +1 202 555 0194.',
        ] as $sourceText) {
            RedactJob::query()->firstOrCreate(
                ['source_text' => $sourceText],
                ['status' => 'sample'],
            );
        }
    }
}
