# Laravel 12 E-Commerce REST API Documentation

Base URL: `/api/v1`

All responses follow a consistent standard JSON structure with appropriate HTTP status codes:
- **Success (`200`, `201`)**:
  ```json
  {
      "success": true,
      "message": "Resource retrieved or processed successfully",
      "data": {},
      "meta": {
          "current_page": 1,
          "last_page": 4,
          "per_page": 15,
          "total": 50
      }
  }
  ```
- **Error / Validation (`400`, `401`, `403`, `404`, `422`, `500`)**:
  ```json
  {
      "success": false,
      "message": "Validation failed",
      "errors": {
          "field_name": ["Specific error description."]
      }
  }
  ```

---

## 1. Authentication (`/api/v1/auth`)

| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | No | Any | `name`, `email`, `password`, `password_confirmation`, `role` (`customer`, `vendor`), `shop_name` (if vendor) | Register user and return Sanctum Bearer token |
| `POST` | `/api/v1/auth/login` | No | Any | `email`, `password` | Authenticate user and issue token |
| `POST` | `/api/v1/auth/logout` | Sanctum | Any | None | Revoke current access token (204 No Content) |
| `GET` | `/api/v1/auth/me` | Sanctum | Any | None | Get current authenticated user profile and roles |

---

## 2. Categories (`/api/v1/categories` & `/api/v1/admin/categories`)

| Method | Endpoint | Auth | Role | Query / Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/categories` | No | Public | `search`, `status`, `sort`, `direction`, `per_page` | List all categories with product counts |
| `GET` | `/api/v1/categories/{category}` | No | Public | None | Get single category with preview products |
| `GET` | `/api/v1/categories/{category}/products` | No | Public | `per_page` | Get paginated active products for category |
| `POST` | `/api/v1/admin/categories` | Sanctum | Admin | `name`, `description`, `image`, `status` | Create new category |
| `PUT` | `/api/v1/admin/categories/{category}` | Sanctum | Admin | `name`, `description`, `image`, `status` | Update category details |
| `DELETE` | `/api/v1/admin/categories/{category}` | Sanctum | Admin | None | Delete category (204 No Content) |

---

## 3. Products (`/api/v1/products` & `/api/v1/vendor/products`)

| Method | Endpoint | Auth | Role | Query / Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/products` | No | Public | `category_id`, `vendor_id`, `min_price`, `max_price`, `featured`, `search`, `sort`, `direction`, `per_page` | List products with multi-attribute filtering |
| `GET` | `/api/v1/products/search` | No | Public | `search`, `category_id`, `min_price`, `max_price` | Dedicated product search |
| `GET` | `/api/v1/products/{product}` | No | Public | None | Get product details, ratings, approved reviews, vendor, images |
| `GET` | `/api/v1/products/{product}/related` | No | Public | `limit` (default 6) | Get related products in same category |
| `POST` | `/api/v1/vendor/products` | Sanctum | Vendor / Admin | `category_id`, `name`, `sku`, `price`, `discount_price`, `stock`, `description`, `image`, `status`, `featured`, `vendor_id` (admin only) | Create product (logs initial stock transaction) |
| `PUT` | `/api/v1/vendor/products/{product}` | Sanctum | Vendor / Admin | `category_id`, `name`, `sku`, `price`, `discount_price`, `stock`, `description`, `status`, `featured` | Update owned product (logs stock delta transaction) |
| `DELETE` | `/api/v1/vendor/products/{product}` | Sanctum | Vendor / Admin | None | Delete owned product (204 No Content) |
| `POST` | `/api/v1/vendor/products/{product}/images` | Sanctum | Vendor / Admin | `path`, `is_primary` | Add gallery image for product |
| `DELETE` | `/api/v1/vendor/products/{product}/images/{image}` | Sanctum | Vendor / Admin | None | Remove image from product |

---

## 4. Vendors & Inventory (`/api/v1/vendors` & `/api/v1/vendor`)

| Method | Endpoint | Auth | Role | Query / Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/vendors` | No | Public | `search`, `per_page` | List active vendors with product counts |
| `GET` | `/api/v1/vendors/{vendor}` | No | Public | None | View vendor shop profile and active products |
| `GET` | `/api/v1/vendor/profile` | Sanctum | Vendor | None | View authenticated vendor profile |
| `PUT` | `/api/v1/vendor/profile` | Sanctum | Vendor | `shop_name`, `description`, `logo`, `phone`, `email`, `address` | Update vendor profile |
| `GET` | `/api/v1/vendor/inventory` | Sanctum | Vendor | `low_stock`, `per_page` | Vendor inventory overview with stock levels |
| `GET` | `/api/v1/vendor/inventory/transactions` | Sanctum | Vendor | `per_page` | Stock audit trail (`stock_in`, `stock_out`, `order_deduction`, `order_restoration`) |

---

## 5. Customers & Addresses (`/api/v1/customer`)

| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/customer/profile` | Sanctum | Customer | None | View customer profile and saved addresses |
| `PUT` | `/api/v1/customer/profile` | Sanctum | Customer | `name`, `email`, `password`, `password_confirmation` | Update customer profile |
| `GET` | `/api/v1/customer/addresses` | Sanctum | Customer | None | List saved delivery addresses |
| `POST` | `/api/v1/customer/addresses` | Sanctum | Customer | `label`, `address`, `city`, `phone`, `is_default` | Add new delivery address |
| `GET` | `/api/v1/customer/addresses/{address}` | Sanctum | Customer | None | View single address (IDOR protected) |
| `PUT` | `/api/v1/customer/addresses/{address}` | Sanctum | Customer | `label`, `address`, `city`, `phone`, `is_default` | Update address details |
| `DELETE` | `/api/v1/customer/addresses/{address}` | Sanctum | Customer | None | Delete address (204 No Content) |

---

## 6. Shopping Cart & Coupons (`/api/v1/cart`)

| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/cart` | Sanctum | Customer | None | View cart with calculated `subtotal`, `discount`, `tax` (10%), `shipping`, `grand_total` |
| `POST` | `/api/v1/cart/items` | Sanctum | Customer | `product_id`, `quantity` | Add product to cart with stock validation |
| `PUT` | `/api/v1/cart/items/{item}` | Sanctum | Customer | `quantity` | Update item quantity with stock validation |
| `DELETE` | `/api/v1/cart/items/{item}` | Sanctum | Customer | None | Remove item from cart |
| `DELETE` | `/api/v1/cart/clear` | Sanctum | Customer | None | Empty entire cart |
| `POST` | `/api/v1/cart/apply-coupon` | Sanctum | Customer | `code` | Validate and apply promo coupon |
| `DELETE` | `/api/v1/cart/coupon` | Sanctum | Customer | None | Remove coupon code |

---

## 7. Wishlist (`/api/v1/wishlist`)

| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/wishlist` | Sanctum | Customer | None | List wishlist products |
| `POST` | `/api/v1/wishlist/items` | Sanctum | Customer | `product_id` | Add product (prevents duplicates) |
| `DELETE` | `/api/v1/wishlist/items/{product}` | Sanctum | Customer | None | Remove product from wishlist |
| `DELETE` | `/api/v1/wishlist/clear` | Sanctum | Customer | None | Clear entire wishlist |

---

## 8. Orders & Payments

### Customer Orders (`/api/v1/customer/orders`)
| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/customer/orders` | Sanctum | Customer | `per_page` | List customer's order history |
| `POST` | `/api/v1/customer/orders` | Sanctum | Customer | `shipping_address` or `shipping_address_id`, `payment_method`, `notes` | Checkout cart, deduct stock, create order & payment |
| `GET` | `/api/v1/customer/orders/{order}` | Sanctum | Customer | None | View customer order details |
| `POST` | `/api/v1/customer/orders/{order}/cancel` | Sanctum | Customer | None | Cancel pending/confirmed order and restore inventory |
| `GET` | `/api/v1/customer/orders/{order}/payment` | Sanctum | Customer | None | View order payment record |
| `POST` | `/api/v1/customer/orders/{order}/payment` | Sanctum | Customer | `method` (`cash_on_delivery`, `demo_card`, `bank_transfer`), `card_number`, `bank_reference` | Process payment and confirm order |

### Vendor Orders (`/api/v1/vendor/orders`)
| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/vendor/orders` | Sanctum | Vendor | `status`, `per_page` | List orders containing vendor's products |
| `GET` | `/api/v1/vendor/orders/{order}` | Sanctum | Vendor | None | View order containing vendor's products |
| `PUT` | `/api/v1/vendor/orders/{order}/status` | Sanctum | Vendor | `status` (`pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`) | Update order fulfillment status |

### Admin Orders (`/api/v1/admin/orders`)
| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/admin/orders` | Sanctum | Admin | `status`, `search`, `per_page` | List all orders in the platform |
| `GET` | `/api/v1/admin/orders/{order}` | Sanctum | Admin | None | View full order record |
| `PUT` | `/api/v1/admin/orders/{order}/status` | Sanctum | Admin | `status` | Update any order status |

---

## 9. Reviews & Ratings (`/api/v1/products/{product}/reviews` & `/api/v1/reviews`)

| Method | Endpoint | Auth | Role | Request Body | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/products/{product}/reviews` | No | Public | None | List approved reviews for product |
| `POST` | `/api/v1/products/{product}/reviews` | Sanctum | Customer | `order_id`, `rating` (1-5), `comment` | Submit review (verified buyer only - customer must have purchased product in order) |
| `PUT` | `/api/v1/reviews/{review}` | Sanctum | Customer | `rating`, `comment` | Update owned review |
| `DELETE` | `/api/v1/reviews/{review}` | Sanctum | Customer / Admin | None | Delete review |

---

## 10. Admin Analytics Dashboard (`/api/v1/admin/dashboard`)

| Method | Endpoint | Auth | Role | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/admin/dashboard` | Sanctum | Admin | Platform KPIs: total customers, vendors, products, categories, orders, pending/delivered orders, gross revenue, recent orders, top products, top vendors |

---

## Demo Accounts

All demo accounts use the password: `password`

| Role | Email | Password | Shop / Details |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `password` | Super Admin access to all management endpoints |
| **Vendor 1** | `vendor1@example.com` | `password` | Phnom Penh Tech Zone (Electronics) |
| **Vendor 2** | `vendor2@example.com` | `password` | Angkor Fashion Hub (Fashion & Silk) |
| **Vendor 3** | `vendor3@example.com` | `password` | Siem Reap Handicrafts & Gifts |
| **Vendor 4** | `vendor4@example.com` | `password` | Battambang Organic & Living |
| **Vendor 5** | `vendor5@example.com` | `password` | Khmer Mobile & Accessories |
| **Customers** | `customer1@example.com` to `customer20@example.com` | `password` | 20 Demo Customer accounts with addresses and order histories |

Active Demo Promo Coupons:
- `KHMERNEWYEAR` (20% off, min order $30)
- `WELCOME10` (10% off, min order $20)
- `FREESHIP` ($3.00 off shipping, min order $15)
- `SAVE15` ($15.00 fixed discount, min order $100)
