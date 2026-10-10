<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        if (CompanySetting::query()->doesntExist()) {
            CompanySetting::create(config('rentacar.default_company'));
        }
    }
}
