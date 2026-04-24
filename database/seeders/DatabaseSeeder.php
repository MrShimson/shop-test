<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(ProductCategory::cases())->map(
            fn (ProductCategory $case) => Category::factory()->create(['name' => $case->value])
        );

        Product::factory()
            ->count(1500)
            ->recycle($categories)
            ->create();
    }
}
