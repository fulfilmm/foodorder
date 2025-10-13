<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Drinks',
            'Coffee',
            'Tea',
            'Cakes',
            'Rice And Curry',
            'Sandwich',
            'Pizza',
        ];

        // global counter for PR-001, PR-002, …
        $counter = 1;

        foreach ($categories as $catName) {
            $category = Category::where('name', $catName)->first();

            if (! $category) {
                continue;
            }

            for ($i = 1; $i <= 10; $i++) {
                Product::create([
                    'code'         => 'PR-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                    'name'         => "{$catName} Item {$i}",
                    'actual_price' => rand(1000, 10000),
                    'qty'          => 100,
                    'remain_qty'   => 100,
                    'sell_qty'     => 0,
                    'category_id'  => $category->id,
                    'image'        => 'assets/images/logo/logo.png',
                    'description'  => "This is a sample {$catName} product number {$i}.",
                ]);
            }
        }
    }
}
