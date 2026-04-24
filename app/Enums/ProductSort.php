<?php

namespace App\Enums;

enum ProductSort: string
{
    case PriceAsc = 'price_asc';
    case PriceDesc = 'price_desc';
    case RatingDesc = 'rating_desc';
    case Newest = 'newest';

    public function column(): string
    {
        return match ($this) {
            self::PriceAsc => 'price',
            self::PriceDesc => 'price',
            self::RatingDesc => 'rating',
            self::Newest => 'created_at',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::PriceAsc => 'asc',
            self::PriceDesc => 'desc',
            self::RatingDesc => 'desc',
            self::Newest => 'desc',
        };
    }
}
