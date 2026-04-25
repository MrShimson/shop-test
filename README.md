# shop-test

[![CI](https://github.com/MrShimson/shop-test/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/MrShimson/shop-test/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/MrShimson/shop-test/branch/master/graph/badge.svg)](https://codecov.io/gh/MrShimson/shop-test)

Тестовое задание: продуктовый каталог с фильтрацией, сортировкой, поиском и пагинацией. Один эндпоинт — `GET /api/v1/products`.

## Стек

- PHP 8.3, Laravel 13
- MySQL 8 (FULLTEXT-индекс на `name`, индексы на фильтруемые поля)
- Pest 4 — feature-тесты
- Docker Compose — окружение
- Pint, GitHub Actions, Codecov

## Запуск

```bash
make init
```

Что делает:
- поднимает контейнеры (`app`, `mysql`, `mysql_test`);
- ставит зависимости;
- генерирует `APP_KEY`;
- мигрирует и сидит ~1500 товаров в 10 категориях.

Затем — поднять веб-сервер:

```bash
make start
```

API доступен на `http://localhost:8000`.

## API

`GET /api/v1/products`

| Параметр      | Тип       | Описание                                                         |
| ------------- | --------- |------------------------------------------------------------------|
| `q`           | string    | Поиск по `name`. ≥3 символов — FULLTEXT с префиксом, иначе LIKE  |
| `price_from`  | number    | Нижняя граница цены, включительно                                |
| `price_to`    | number    | Верхняя граница цены, включительно                               |
| `category_id` | int       | Несуществующий → пустой список                                   |
| `in_stock`    | bool      | `true`/`false`/`1`/`0`                                           |
| `rating_from` | 0..5      | Минимальный рейтинг                                              |
| `sort`        | enum      | `price_asc`, `price_desc`, `rating_desc`, `newest` (default)     |
| `page`        | int       | Страница, начиная с 1                                            |
| `per_page`    | int       | Размер страницы, дефолт 20, кап 100                              |

### Примеры

```bash
# базовый список
curl http://localhost:8000/api/v1/products

# поиск + фильтры + сортировка
curl "http://localhost:8000/api/v1/products?q=iphone&in_stock=true&price_from=500&rating_from=4&sort=price_asc"

# пагинация
curl "http://localhost:8000/api/v1/products?page=2&per_page=10"
```

Готовые запросы для REST-клиента — в [`api.http`](api.http).

## Команды

```bash
make up            # поднять контейнеры
make down          # остановить
make start         # artisan serve внутри контейнера, на :8000
make test          # запустить Pest
make coverage      # тесты + текстовый coverage
make lint          # форматирование (Pint)
make lint-check    # проверка форматирования (для CI)
make fresh         # пересоздать БД и засидить
```

Под капотом — `bin/composer`, `bin/artisan`, `bin/pest`, `bin/pint`: обёртки над `docker compose exec` с пробросом UID/GID.

## Архитектура

```
app/
├── Enums/
│   ├── ProductCategory.php   # источник истины: имена категорий + шаблоны имён товаров
│   └── ProductSort.php       # enum для sort, с column() / direction()
├── Filters/
│   └── ProductFilter.php     # сборка where-условий из Collection
├── Http/
│   ├── Controllers/Api/V1/
│   │   └── ProductController.php
│   ├── Requests/
│   │   └── ListProductsRequest.php   # валидация + filters() / sort() / perPage()
│   └── Resources/V1/
│       └── ProductResource.php
├── Models/
│   ├── Category.php
│   └── Product.php
└── UseCases/
    └── ListProductsUseCase.php       # оркестратор: filter → sort → paginate
```

## Тесты

```bash
make test
```

Используется отдельная БД `mysql_test` (отдельный сервис в compose) — чтобы `make fresh` локально не сносил тестовые данные.
