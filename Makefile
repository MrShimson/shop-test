.PHONY: init rebuild up down start install test lint lint-check fresh

init:
	cp -n .env.example .env || true
	docker network create shop_test_network || true
	$(MAKE) up
	$(MAKE) install
	./bin/artisan key:generate
	./bin/artisan migrate --seed

install:
	./bin/composer install -o

rebuild:
	docker compose -f docker-compose.yml build --no-cache

up:
	docker compose -f docker-compose.yml up -d --build

down:
	docker compose -f docker-compose.yml down

start:
	./bin/artisan serve --host=0.0.0.0 --port=8000

test:
	./bin/pest

lint:
	./bin/pint

lint-check:
	./bin/pint --test

fresh:
	./bin/artisan migrate:fresh --seed
