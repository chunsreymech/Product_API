# Laravel 12 E-Commerce REST API Demo Project

A production-grade **RESTful Multi-Vendor E-Commerce API** built with **Laravel 12**, **Laravel Sanctum**, and modern REST architecture.

---

## Features

- 🔐 **Authentication & RBAC**: Laravel Sanctum token-based authentication with `admin`, `vendor`, and `customer` role management.
- 📦 **Product Catalog**: Advanced search, filtering by category, vendor, price range, and featured status; related products; stock tracking.
- 🏪 **Multi-Vendor Management**: Independent vendor shops, product management with inventory audit logs, vendor order tracking, and shop profiles.
- 🛒 **Dynamic Shopping Cart**: Instant calculation of subtotals, tax (10%), free shipping thresholds, stock checks, and coupon discounts.
- 🏷️ **Coupons & Promotions**: Fixed and percentage discount coupons with validity periods, minimum spend requirements, and usage caps.
- ❤️ **Wishlist**: Personalized customer wishlist with duplicate prevention.
- 📋 **Order Processing**: Customer cart checkout, stock decrements, order cancellation with inventory restoration, and status lifecycle (`pending` -> `confirmed` -> `processing` -> `shipped` -> `delivered` -> `cancelled`).
- 💳 **Demo Payment Gateway**: Support for `cash_on_delivery`, `demo_card`, and `bank_transfer`.
- ⭐ **Verified Buyer Reviews**: Product reviews and star ratings restricted strictly to customers who have ordered the product.
- 📊 **Admin Analytics Dashboard**: High-level platform KPIs, revenue summary, recent orders, top products, and top vendor analytics.
- 🇰🇭 **Realistic Cambodian Demo Dataset**: Seeders with 1 admin, 5 vendors, 20 customers, 10 categories, 50 products, addresses, wishlist items, coupons, and 100 orders.
- 🧪 **100% Feature Test Coverage**: 41 comprehensive tests covering every API endpoint and permission boundary.

---

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Authentication**: Laravel Sanctum
- **Database**: SQLite / MySQL / PostgreSQL
- **Testing**: PHPUnit / Laravel Feature Tests
- **API Standards**: Form Requests, API Resources, Eloquent Policies, Consistent JSON Responses

---

## Getting Started

### 1. Installation

```bash
# Clone the repository
git clone <repo-url>
cd Product_API

# Install Composer dependencies
composer install

# Set up environment file
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup & Seeding

```bash
# Run migrations and seed with Cambodian e-commerce demo dataset
php artisan migrate:fresh --seed
```

### 3. Start Development Server

```bash
php artisan serve
```
The API is accessible at: `http://127.0.0.1:8000/api/v1`

### 4. Run Test Suite

```bash
php artisan test
```

---

## Demo Accounts

| Role | Email | Password | Details |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `password` | Super Administrator with full platform control |
| **Vendor 1** | `vendor1@example.com` | `password` | Phnom Penh Tech Zone |
| **Vendor 2** | `vendor2@example.com` | `password` | Angkor Fashion Hub |
| **Vendor 3** | `vendor3@example.com` | `password` | Siem Reap Handicrafts & Gifts |
| **Vendor 4** | `vendor4@example.com` | `password` | Battambang Organic & Living |
| **Vendor 5** | `vendor5@example.com` | `password` | Khmer Mobile & Accessories |
| **Customer** | `customer1@example.com` to `customer20@example.com` | `password` | 20 Demo Customer accounts |

Active Promo Coupons:
- `KHMERNEWYEAR` (20% off, min $30)
- `WELCOME10` (10% off, min $20)
- `FREESHIP` ($3 off shipping, min $15)
- `SAVE15` ($15 fixed discount, min $100)

---

## API Documentation

For the complete API documentation matrix, visit [API.md](API.md) or open `/docs` in your browser when the server is running.
