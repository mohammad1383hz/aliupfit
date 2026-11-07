<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->firstOrCreate(
            ['key' => 'site_config'],
            ['value' => [
                'phone' => '',
                'whatsapp' => '',
                'instagram' => '',
                'capacity' => ['s612' => null, 's35a' => null, 's35b' => null],
                'prices' => ['training' => '', 'nutrition' => '', 'assessment' => ''],
                'coachPhoto' => '',
            ]]
        );
        SiteSetting::query()->firstOrCreate(
            ['key' => 'site_address'],
            ['value' => 'تهران، خیابان آزادی، نبش اسکندری شمالی، برج نگین، طبقه سوم']
        );
    }
}
