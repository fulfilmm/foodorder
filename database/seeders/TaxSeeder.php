<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'No tax',        'percent' => 0,    'is_default' => true,  'is_active' => true],
            ['name' => 'VAT 5%',        'percent' => 5.00, 'is_default' => false, 'is_active' => true],
            ['name' => 'VAT 7%',        'percent' => 7.00, 'is_default' => false, 'is_active' => true],
            ['name' => 'Service 10%',   'percent' => 10.0, 'is_default' => false, 'is_active' => true],
        ];

        foreach ($rows as $r) {
            Tax::updateOrCreate(['name' => $r['name']], $r);
        }

        // (optional) ensure only one default
        Tax::where('name', '!=', 'No tax')->update(['is_default' => false]);
    }
}
