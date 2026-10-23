# Ice Cream Shop API

REST API for an ice cream shop's inventory and customer records, with token authentication and role-based access (Owner, Manager, Cashier).

**Stack:** Laravel 12, Laravel Sanctum (API tokens), Laravel Policies (authorization), PHPUnit.

## Prerequisites

- PHP 8.2+
- Composer
- MySQL
- Git

## Setup

```bash
git clone https://github.com/Sinari29/icecream-shop-api.git
cd icecream-shop-api

composer install

cp .env.example .env
php artisan key:generate
```

Create the application and test databases:

```sql
CREATE DATABASE icecream_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE icecream_shop_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Configure the application database in `.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=icecream_shop
DB_USERNAME=root
DB_PASSWORD=
```

Update these values if your local MySQL configuration differs.

The test suite uses the `icecream_shop_test` database and the same database credentials.

Run the migrations and seed the database:

```bash
php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000/api
```

### Demo Accounts

The following demo accounts are created by the database seeder for local development and testing.

| Role | Email | Password |
|------|--------|----------|
| Owner | `owner@example.com` | `Password123!` |
| Manager | `manager@example.com` | `Password123!` |
| Cashier | `cashier@example.com` | `Password123!` |

The seeder also creates 3 categories (Chocolate, Vanilla, Strawberry), 6 items, and 3 customers.

## Verify

Run the tests (they use `icecream_shop_test`, so seeded data is not touched):

```bash
php artisan test
```

Then make a request:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@example.com","password":"Password123!"}'
```

```bash
curl http://127.0.0.1:8000/api/items \
  -H "Authorization: Bearer <token>"
```

On Windows PowerShell, use `curl.exe` (plain `curl` is an alias for another command), or use the Postman collection below.

## API

All routes are under `/api` and return JSON, including errors. Everything except login requires `Authorization: Bearer <token>`.

| Method | Path | Description |
|---------|---------|---------|
| POST | `/auth/login` | Returns `token` and `user`; limited to 5 requests per minute |
| POST | `/auth/logout` | Revokes the token used for the request |
| GET | `/items` | List items; `?search=` filters by name |
| GET | `/items/{id}` | Get an item |
| POST | `/items` | Create an item |
| PUT/PATCH | `/items/{id}` | Update an item |
| DELETE | `/items/{id}` | Delete an item |
| GET | `/customers` | List customers |
| GET | `/customers/{id}` | Get a customer |
| POST | `/customers` | Create a customer |
| PUT/PATCH | `/customers/{id}` | Update a customer |
| DELETE | `/customers/{id}` | Delete a customer |

- **Items:** `category_id` (must exist), `name`, `price` (0 to 999999.99, returned as a number), and `quantity` (integer, 0 or more) are required; `description` is optional. Responses include the item's category.
- **Customers:** `name` is required; `email` (unique), `phone`, and `address` are optional.
- **Search and paging:** `GET /items?search=choc` is a case-insensitive contains match on name. List endpoints return 15 results per page (`?page=2`).
- **Status codes:** 200, 201, 204 (delete), 401 (no or revoked token, bad credentials), 403 (role not allowed), 404, 422 (validation).

Logout deletes only the current token, so it cannot be reused. The user's other tokens are unaffected.

## Role Permissions

Enforced server-side in `app/Policies/ItemPolicy.php` and `app/Policies/CustomerPolicy.php`.

| Operation | Owner | Manager | Cashier |
|------------|:-----:|:-------:|:-------:|
| View items | Yes | Yes | Yes |
| Create items | Yes | Yes | No |
| Update items | Yes | Yes | Yes |
| Delete items | Yes | No | No |
| View customers | Yes | Yes | Yes |
| Create customers | Yes | Yes | Yes |
| Update customers | Yes | Yes | Yes |
| Delete customers | Yes | No | No |

**Assumption:** The assessment does not specify whether cashiers may delete customers. It lists "add, view, and edit" for that role, so this project denies customer deletion. This is an interpretation, not a stated requirement, and is covered by a test.

## Design Notes

- Categories are a separate table; each item belongs to one category.
- Deleting a category that still has items is blocked at the database level.
- Roles are stored as a string column cast to a PHP enum (`owner`, `manager`, `cashier`).
- Prices are stored as `DECIMAL(8,2)`.
- Deletes are hard deletes.

## Documentation

- **ER diagram:** `docs/ER-diagram.md` (renders on GitHub), also available as `docs/ER-diagram.png` and the editable draw.io source.
- **Postman collection:** `docs/postman/IceCreamShop.postman_collection.json`. Import it, run one of the Login requests (the token is saved to a collection variable), then run the rest. Change the `base_url` variable if the server is not running on `http://127.0.0.1:8000`. The delete requests use IDs 7 (item) and 4 (customer), the first records created after seeding.