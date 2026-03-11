<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::updateOrCreate(
        ['id' => 1],
        [
            'name' => 'Smash Fitness',
            'email' => 'abc@gmail.com',
            'phone' => '15000000',
            'mobile' => '9800000000',
            'address' => 'Sitapaila kathmandu, KTM, Nepal',
            'logo' => 'organizations/default_logo.png',
            'facebook' => 'https://facebook.com',
            'instagram' => 'https://instagram.com',
            'twitter' => 'https://twitter.com',
            'linkedin' => 'https://linkedin.com',
            'tiktok' => 'https://tiktok.com',
            'other' => [],
        ]
        );
    }
}
