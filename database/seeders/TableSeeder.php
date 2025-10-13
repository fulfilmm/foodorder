<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $code = strtoupper(uniqid("TBL$i"));
            $table ='Table ' . $i;

            // Save QR code to public storage
            $qrPath = "tables/qr_$code.svg";
            // Storage::disk('public')->put($qrPath, QrCode::format('svg')->size(200)->generate($code));
            Storage::disk('public')->put($qrPath, QrCode::format('svg')->size(200)->generate($table));
            // Create table with code and optional QR image path
            Table::create([
                'name'    => $table,
                'code'    => $code,
                'status'  => 'available',
                'qr_path' => $qrPath,
            ]);
        }
    }
}
