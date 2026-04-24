<?php

namespace App\Http\Requests;

use App\Enums\ProductSort;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ListProductsRequest extends FormRequest
{
    private const MAX_PER_PAGE = 100;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('in_stock');

        if (is_string($value) && in_array(strtolower($value), ['true', 'false'], true)) {
            $this->merge(['in_stock' => strtolower($value) === 'true']);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0', 'gte:price_from'],
            'category_id' => ['nullable', 'integer'],
            'in_stock' => ['nullable', 'boolean'],
            'rating_from' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sort' => ['nullable', Rule::enum(ProductSort::class)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return Collection<string, mixed>
     */
    public function filters(): Collection
    {
        return collect($this->only([
            'q',
            'price_from',
            'price_to',
            'category_id',
            'in_stock',
            'rating_from',
        ]))->reject(fn ($v) => $v === null || $v === '');
    }

    public function sort(): ProductSort
    {
        return ProductSort::tryFrom((string) $this->input('sort')) ?? ProductSort::Newest;
    }

    public function perPage(): int
    {
        return min((int) $this->input('per_page', 20), self::MAX_PER_PAGE);
    }
}
