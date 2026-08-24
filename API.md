# E-Commerce REST API

Base URL: `/api/v1`. JSON is returned in the form `{ success, message, data, meta }`; validation errors use HTTP 422 and `{ success: false, message, errors }`.

## Authentication

| Method | Endpoint | Auth | Body |
|---|---|---|---|
| POST | `/auth/register` | No | `name`, `email`, `password`, `password_confirmation`, optional `role` |
| POST | `/auth/login` | No | `email`, `password` |
| GET | `/auth/me` | Sanctum | Bearer token |
| POST | `/auth/logout` | Sanctum | Bearer token |

## Catalog

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| GET | `/categories` | No | `search`, `status`, `sort`, `direction`, `per_page` |
| GET | `/categories/{category}` | No | Includes products |
| GET | `/categories/{category}/products` | No | Paginated |
| POST/PUT/DELETE | `/admin/categories[/{category}]` | Admin | Category management |
| GET | `/products` | No | `search`, `category_id`, `vendor_id`, `min_price`, `max_price`, `featured`, `sort`, `direction`, `per_page` |
| GET | `/products/{product}` | No | Includes category and vendor |
| POST/PUT/DELETE | `/vendor/products[/{product}]` | Vendor | Owner-only product management |

## Customer tools

| Method | Endpoint | Body |
|---|---|---|
| GET | `/cart` | Returns subtotal, tax, shipping, grand total |
| POST | `/cart/items` | `product_id`, `quantity` |
| DELETE | `/cart/items/{item}` | Removes an owned cart item |
| DELETE | `/cart/clear` | Clears the authenticated cart |
| GET/POST | `/wishlist` or `/wishlist/items` | POST body: `product_id` |
| DELETE | `/wishlist/items/{product}` | Removes an owned item |
| DELETE | `/wishlist/clear` | Clears the authenticated wishlist |

## Local setup

```sh
composer install
php artisan migrate:fresh --seed
php artisan serve
php artisan test
```

Demo accounts use the password `password`: `admin@example.com`, `vendor1@example.com`, and `customer1@example.com`.