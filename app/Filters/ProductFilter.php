<?php

namespace App\Filters;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductFilter
{
    private const FULLTEXT_MIN_TOKEN = 3;

    /**
     * @param  Builder<Product>  $query
     * @param  Collection<string, mixed>  $f
     * @return Builder<Product>
     */
    public function applyFilters(Builder $query, Collection $f): Builder
    {
        return $query
            ->when($f->has('q'), fn (Builder $q) => $this->applyFulltextSearch($q, $f->get('q')))
            ->when($f->has('price_from'), fn (Builder $q) => $q->where('price', '>=', $f->get('price_from')))
            ->when($f->has('price_to'), fn (Builder $q) => $q->where('price', '<=', $f->get('price_to')))
            ->when($f->has('category_id'), fn (Builder $q) => $q->where('category_id', $f->get('category_id')))
            ->when($f->has('in_stock'), fn (Builder $q) => $q->where('in_stock', $this->toBool($f->get('in_stock'))))
            ->when($f->has('rating_from'), fn (Builder $q) => $q->where('rating', '>=', $f->get('rating_from')));
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applyFulltextSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if (mb_strlen($term) < self::FULLTEXT_MIN_TOKEN) {
            return $query->where('name', 'like', "%$term%");
        }

        $expression = collect(preg_split('/\s+/', $term))
            ->filter()
            ->map(fn (string $token) => '+'.addcslashes($token, '+-<>~()"*@').'*')
            ->implode(' ');

        return $query->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', [$expression]);
    }

    private function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
