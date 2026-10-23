# Ice Cream Shop API

A RESTful backend for an ice cream shop that manages **inventory items** and **customer records** with token authentication and role-based access control (Owner, Manager, Cashier).

## Tech stack

- Laravel 12 (PHP 8.2+)
- MySQL 8
- Laravel Sanctum (API token authentication)
- Laravel Policies (role-based authorization)
- PHPUnit (feature and unit tests)

## Setup

Requirements: PHP 8.2+, Composer, MySQL, Git.

```bash
git clone https://github.com/Sinari29/icecream-shop-api.git
cd icecream-shop-api
composer install
cp .env.example .env        
php artisan key:generate
```

Create two empty databases (one for the app, one for tests):

```sql
CREATE DATABASE icecream_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE icecream_shop_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Check the `DB_*` values in `.env`. 
Defaults: 
    host `127.0.0.1` 
    port `3306` 
    user `root` 
    empty password
    
Then:
```bash
php artisan migrate --seed
php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

## Seeded demo accounts

These are **demo-only** credentials created by the seeder. 

|  Role   |       Email         |   Password   |
|---------|---------------------|--------------|
| Owner   | owner@example.com   | Password123! |
| Manager | manager@example.com | Password123! |
| Cashier | cashier@example.com | Password123! |

The seeder also creates three categories (Chocolate, Vanilla, Strawberry), six items, and three customers.

## Authentication

1. `POST /api/auth/login` with `email` and `password` returns a Bearer token.
2. Send it on later requests: `Authorization: Bearer <token>` and `Accept: application/json`.
3. `POST /api/auth/logout` deletes the token used for the request, so it can no longer be used. Other tokens for the same user are unaffected.

Login is rate limited to 5 attempts per minute.

## Endpoints

|   Method  |          Path         |                     Purpose                     |
|-----------|-----------------------|-------------------------------------------------|
| POST      | `/api/auth/login`     | Authenticate and receive a token                |
| POST      | `/api/auth/logout`    | Revoke the current token                        |
| GET       | `/api/items`          | List items (supports `?search=` and pagination) |
| GET       | `/api/items/{id}`     | Retrieve an item                                |
| POST      | `/api/items`          | Create an item                                  |
| PUT/PATCH | `/api/items/{id}`     | Update an item                                  |
| DELETE    | `/api/items/{id}`     | Delete an item                                  |
| GET       | `/api/customers`      | List customers (paginated)                      |
| GET       | `/api/customers/{id}` | Retrieve a customer                             |
| POST      | `/api/customers`      | Create a customer                               |
| PUT/PATCH | `/api/customers/{id}` | Update a customer                               |
| DELETE    | `/api/customers/{id}` | Delete a customer                               |

**Item search:** `GET /api/items?search=choc` returns items whose name contains the text (case-insensitive).

**Pagination:** list endpoints return 15 results per page. Use `?page=2` for further pages.

**Status codes:** 200 OK, 201 Created, 204 No Content (delete), 401 unauthenticated or bad credentials, 403 forbidden for your role, 404 not found, 422 validation error. All `/api/*` errors are JSON.

## Role permissions

|     Operation    | Owner | Manager | Cashier |
|------------------|-------|---------|---------|
| View items       | Yes   | Yes     | Yes     |
| Create items     | Yes   | Yes     | No      |
| Update items     | Yes   | Yes     | Yes     |
| Delete items     | Yes   | No      | No      |
| View customers   | Yes   | Yes     | Yes     |
| Create customers | Yes   | Yes     | Yes     |
| Update customers | Yes   | Yes     | Yes     |
| Delete customers | Yes   | No      | No      |

Permissions are enforced on the server in `app/Policies/ItemPolicy.php` and `app/Policies/CustomerPolicy.php`.

### Assumption: Cashiers cannot delete customers

The assessment states that Cashiers can "add, view, and edit customer records" and that Managers cannot delete customers. It does **not** explicitly say whether Cashiers may delete customers. This project assumes they **cannot**, because delete is not among the operations listed for the role. 

## Design decisions

- **Categories are a separate table.** Each item belongs to one category (one-to-many). Deleting a category that still has items is blocked (restrict).
- **Hard deletes.** 
- **Price** is stored as `DECIMAL(8,2)` and returned as a JSON number.
- **Roles** are stored as a string column cast to a PHP enum (`owner`, `manager`, `cashier`).

## Database

See [docs/er-diagram.md](docs/er-diagram.md) (also exported as `docs/er-diagram.png`). Schema is created by Laravel migrations in `database/migrations`.

## Testing

Tests use the separate `icecream_shop_test` MySQL database, so demo data is never touched.

```bash
php artisan test
```

The suite covers login and logout (including token revocation), unauthenticated access, validation, 404 handling, item search, full CRUD for both resources, and a 24-case role permission matrix (`tests/Feature/RolePermissionTest.php`), plus policy unit tests.

## Postman

Import `docs/postman/IceCreamShop.postman_collection.json`. Run a login request first; the token is stored automatically and used by the other requests. Set the `base_url` collection variable if your server is not on `http://127.0.0.1:8000`.