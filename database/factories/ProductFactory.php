<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->getRandomRecycledModel(Category::class)
            ?? Category::factory()->create()
        ;

        return [
            'name'        => $this->buildProductName($category),
            'price'       => fake()->randomFloat(2, 10, 3000),
            'category_id' => $category->id,
            'in_stock'    => fake()->boolean(70),
            'rating'      => fake()->randomFloat(1, 0, 5),
        ];
    }

    private function buildProductName(Category $category): string
    {
        $case = ProductCategory::tryFrom($category->name);

        if ($case === null) {
            return "Generic {$category->name} " . fake()->bothify('??###');
        }

        $productNameParts = array_map(fn (array $options) => fake()->randomElement($options), $case->shape());

        return implode(' ', $productNameParts);
    }
}
