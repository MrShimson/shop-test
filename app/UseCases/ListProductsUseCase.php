<?php

namespace App\UseCases;

use App\Filters\ProductFilter;
use App\Http\Requests\ListProductsRequest;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsUseCase
{
    public function __construct(private readonly ProductFilter $filter) {}

    public function handle(ListProductsRequest $request): LengthAwarePaginator
    {
        $query = $this->filter->applyFilters(Product::query(), $request->filters());
        $sort = $request->sort();

        return $query->with('category')
            ->orderBy($sort->column(), $sort->direction())
            ->paginate($request->perPage());
    }
}
