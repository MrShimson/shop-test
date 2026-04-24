<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->category = Category::factory()->create(['name' => 'Smartphones']);
});

it('returns paginated list with default shape', function () {
    Product::factory()->count(5)->recycle($this->category)->create();

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'rating', 'in_stock', 'category' => ['id', 'name'], 'created_at', 'updated_at'],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
        ])
        ->assertJsonPath('meta.total', 5);
});

it('returns price as a string (decimal precision)', function () {
    Product::factory()->recycle($this->category)->create(['price' => 19.99]);

    $response = $this->getJson('/api/v1/products');

    expect($response->json('data.0.price'))->toBe('19.99');
});

it('searches by name via FULLTEXT for terms >= 3 chars', function () {
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone 15 Pro']);
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone 14']);
    Product::factory()->recycle($this->category)->create(['name' => 'Samsung Galaxy S24']);

    $response = $this->getJson('/api/v1/products?q=iphone');

    $response->assertOk()
        ->assertJsonPath('meta.total', 2);
});

it('uses FULLTEXT boolean mode with prefix matching', function () {
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone 15 Pro']);
    Product::factory()->recycle($this->category)->create(['name' => 'Samsung Galaxy S24']);

    $response = $this->getJson('/api/v1/products?q=iph');

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.name', 'Apple iPhone 15 Pro');
});

it('falls back to LIKE for terms shorter than 3 chars', function () {
    Product::factory()->recycle($this->category)->create(['name' => 'Samsung TV Frame']);
    Product::factory()->recycle($this->category)->create(['name' => 'MacBook Pro']);

    $response = $this->getJson('/api/v1/products?q=tv');

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.name', 'Samsung TV Frame');
});

it('ignores empty q parameter', function () {
    Product::factory()->count(3)->recycle($this->category)->create();

    $this->getJson('/api/v1/products?q=')
        ->assertOk()
        ->assertJsonPath('meta.total', 3);
});

it('filters by price range (bounds inclusive)', function () {
    Product::factory()->recycle($this->category)->create(['price' => 100]);
    Product::factory()->recycle($this->category)->create(['price' => 500]);
    Product::factory()->recycle($this->category)->create(['price' => 1000]);

    $this->getJson('/api/v1/products?price_from=100&price_to=500')
        ->assertOk()
        ->assertJsonPath('meta.total', 2);
});

it('rejects price_from greater than price_to', function () {
    $this->getJson('/api/v1/products?price_from=500&price_to=100')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price_to']);
});

it('filters by category_id', function () {
    $other = Category::factory()->create(['name' => 'Laptops']);
    Product::factory()->count(2)->recycle($this->category)->create();
    Product::factory()->count(3)->recycle($other)->create();

    $this->getJson("/api/v1/products?category_id={$this->category->id}")
        ->assertOk()
        ->assertJsonPath('meta.total', 2);
});

it('returns empty list for non-existing category_id', function () {
    Product::factory()->count(3)->recycle($this->category)->create();

    $this->getJson('/api/v1/products?category_id=999999')
        ->assertOk()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonCount(0, 'data');
});

it('filters by in_stock true/false and accepts both 1/0 and string forms', function () {
    Product::factory()->count(2)->recycle($this->category)->create(['in_stock' => true]);
    Product::factory()->count(3)->recycle($this->category)->create(['in_stock' => false]);

    $this->getJson('/api/v1/products?in_stock=true')->assertJsonPath('meta.total', 2);
    $this->getJson('/api/v1/products?in_stock=false')->assertJsonPath('meta.total', 3);
    $this->getJson('/api/v1/products?in_stock=1')->assertJsonPath('meta.total', 2);
    $this->getJson('/api/v1/products?in_stock=0')->assertJsonPath('meta.total', 3);
});

it('rejects invalid in_stock values', function () {
    $this->getJson('/api/v1/products?in_stock=maybe')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['in_stock']);
});

it('filters by minimum rating', function () {
    Product::factory()->recycle($this->category)->create(['rating' => 3.0]);
    Product::factory()->recycle($this->category)->create(['rating' => 4.5]);
    Product::factory()->recycle($this->category)->create(['rating' => 5.0]);

    $this->getJson('/api/v1/products?rating_from=4')
        ->assertOk()
        ->assertJsonPath('meta.total', 2);
});

it('rejects rating outside 0..5 range', function () {
    $this->getJson('/api/v1/products?rating_from=10')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rating_from']);
});

it('sorts by price ascending', function () {
    Product::factory()->recycle($this->category)->create(['price' => 500]);
    Product::factory()->recycle($this->category)->create(['price' => 100]);
    Product::factory()->recycle($this->category)->create(['price' => 300]);

    $prices = $this->getJson('/api/v1/products?sort=price_asc')->json('data.*.price');

    expect($prices)->toBe(['100.00', '300.00', '500.00']);
});

it('sorts by price descending', function () {
    Product::factory()->recycle($this->category)->create(['price' => 500]);
    Product::factory()->recycle($this->category)->create(['price' => 100]);
    Product::factory()->recycle($this->category)->create(['price' => 300]);

    $prices = $this->getJson('/api/v1/products?sort=price_desc')->json('data.*.price');

    expect($prices)->toBe(['500.00', '300.00', '100.00']);
});

it('sorts by rating descending', function () {
    Product::factory()->recycle($this->category)->create(['rating' => 3.7]);
    Product::factory()->recycle($this->category)->create(['rating' => 4.8]);
    Product::factory()->recycle($this->category)->create(['rating' => 2.1]);

    $ratings = $this->getJson('/api/v1/products?sort=rating_desc')->json('data.*.rating');

    expect($ratings)->toBe([4.8, 3.7, 2.1]);
});

it('sorts by newest (created_at desc)', function () {
    $older = Product::factory()->recycle($this->category)->create(['created_at' => now()->subDay()]);
    $newer = Product::factory()->recycle($this->category)->create(['created_at' => now()]);

    $ids = $this->getJson('/api/v1/products?sort=newest')->json('data.*.id');

    expect($ids)->toBe([$newer->id, $older->id]);
});

it('rejects invalid sort value', function () {
    $this->getJson('/api/v1/products?sort=invalid')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['sort']);
});

it('paginates with page and per_page parameters', function () {
    Product::factory()->count(25)->recycle($this->category)->create();

    $response = $this->getJson('/api/v1/products?page=2&per_page=10');

    $response->assertOk()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonCount(10, 'data');
});

it('caps per_page at the maximum', function () {
    Product::factory()->count(120)->recycle($this->category)->create();

    $this->getJson('/api/v1/products?per_page=1000')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 100)
        ->assertJsonCount(100, 'data');
});

it('combines multiple filters', function () {
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone 15', 'price' => 1200, 'in_stock' => true, 'rating' => 4.5]);
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone SE', 'price' => 400, 'in_stock' => true, 'rating' => 4.5]);
    Product::factory()->recycle($this->category)->create(['name' => 'Apple iPhone 14', 'price' => 900, 'in_stock' => false, 'rating' => 4.5]);

    $response = $this->getJson('/api/v1/products?q=iphone&in_stock=true&price_from=500&rating_from=4');

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.name', 'Apple iPhone 15');
});

it('eager-loads category to avoid N+1', function () {
    Product::factory()->count(10)->recycle($this->category)->create();

    DB::enableQueryLog();
    $this->getJson('/api/v1/products')->assertOk();
    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();

    $selectQueries = $queries->filter(fn (string $q) => str_starts_with(strtolower($q), 'select'));

    // Expected: 1 count (pagination) + 1 products select + 1 categories eager-load = 3
    expect($selectQueries)->toHaveCount(3);
});

it('returns 404 for /api/products without v1 prefix', function () {
    $this->getJson('/api/products')->assertNotFound();
});
