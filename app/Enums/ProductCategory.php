<?php

namespace App\Enums;

enum ProductCategory: string
{
    case Smartphones  = 'Smartphones';
    case Laptops      = 'Laptops';
    case TVs          = 'TVs';
    case Headphones   = 'Headphones';
    case Tablets      = 'Tablets';
    case Cameras      = 'Cameras';
    case Speakers     = 'Speakers';
    case Smartwatches = 'Smartwatches';
    case Gaming       = 'Gaming';
    case Accessories  = 'Accessories';

    /** @return array<string, array<string>> */
    public function shape(): array
    {
        return match ($this) {
            self::Smartphones  => [
                'line'    => ['Apple iPhone', 'Samsung Galaxy', 'Xiaomi Redmi', 'Google Pixel', 'Huawei P', 'OnePlus'],
                'model'   => ['15 Pro', '15', '14 Pro Max', '14', 'S24 Ultra', 'S23', 'Note 13', '8 Pro', '50'],
                'storage' => ['128GB', '256GB', '512GB', '1TB'],
                'color'   => ['Black', 'Titanium', 'Silver', 'Gold', 'White', 'Purple'],
            ],
            self::Laptops      => [
                'line'  => ['MacBook Pro', 'MacBook Air', 'Dell XPS', 'Lenovo ThinkPad', 'HP Spectre', 'Asus ZenBook', 'Acer Swift'],
                'model' => ['13', '14', '15', '16'],
                'specs' => ['i5 16GB', 'i7 16GB', 'i7 32GB', 'i9 32GB', 'M3 16GB', 'M3 Pro 32GB', 'Ryzen 7 16GB'],
            ],
            self::TVs          => [
                'line' => ['Samsung Neo QLED', 'Samsung Frame', 'LG OLED C3', 'LG QNED', 'Sony Bravia XR', 'Xiaomi Mi TV S', 'TCL QLED'],
                'size' => ['43"', '50"', '55"', '65"', '75"', '85"'],
                'year' => ['2022', '2023', '2024'],
            ],
            self::Headphones   => [
                'line'  => ['Sony WH-1000XM5', 'Bose QuietComfort Ultra', 'AirPods Max', 'Sennheiser Momentum 4', 'Beats Studio Pro', 'Marshall Monitor III'],
                'color' => ['Black', 'Silver', 'Midnight', 'Beige', 'White'],
            ],
            self::Tablets      => [
                'line'    => ['Apple iPad Pro', 'Apple iPad Air', 'Apple iPad Mini', 'Samsung Galaxy Tab S9', 'Xiaomi Pad 6', 'Huawei MatePad Pro'],
                'size'    => ['8.3"', '10.9"', '11"', '12.9"'],
                'storage' => ['128GB', '256GB', '512GB', '1TB'],
            ],
            self::Cameras      => [
                'line' => ['Canon EOS R5', 'Canon EOS R6', 'Nikon Z6 II', 'Nikon Z8', 'Sony Alpha A7 IV', 'Fujifilm X-T5', 'Panasonic Lumix S5'],
                'lens' => ['Body', 'Kit 24-70mm', 'Kit 24-105mm', '+ 50mm f/1.8', '+ 85mm f/1.4'],
            ],
            self::Speakers     => [
                'line'  => ['JBL Flip 6', 'JBL Charge 5', 'Bose SoundLink Flex', 'Marshall Stanmore III', 'Sonos Era 100', 'Harman Kardon Onyx'],
                'color' => ['Black', 'Red', 'Blue', 'White', 'Forest'],
            ],
            self::Smartwatches => [
                'line'  => ['Apple Watch Series 9', 'Apple Watch Ultra 2', 'Samsung Galaxy Watch 6', 'Garmin Fenix 7', 'Garmin Forerunner 965', 'Huawei Watch GT4'],
                'size'  => ['41mm', '45mm', '49mm'],
                'strap' => ['Sport Band', 'Leather', 'Milanese Loop', 'Ocean Band'],
            ],
            self::Gaming       => [
                'line'    => ['Sony PlayStation 5', 'Xbox Series X', 'Xbox Series S', 'Nintendo Switch OLED', 'Steam Deck', 'Asus ROG Ally'],
                'edition' => ['Standard', 'Digital', 'Slim', 'Pro'],
                'storage' => ['512GB', '1TB', '2TB'],
            ],
            self::Accessories  => [
                'line'  => ['Logitech MX Master 3S', 'Logitech MX Keys', 'Razer BlackWidow V4', 'SteelSeries Arctis Nova', 'Apple Magic Mouse', 'Keychron K8'],
                'color' => ['Black', 'White', 'Graphite', 'Pale Gray'],
            ],
        };
    }
}
