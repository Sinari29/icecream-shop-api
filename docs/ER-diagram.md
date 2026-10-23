# Entity-Relationship Diagram

```mermaid
erDiagram
    CATEGORIES ||--o{ ITEMS : "has many"

    USERS {
        bigint id PK
        varchar_255 name
        varchar_255 email UK
        timestamp email_verified_at
        varchar_255 password
        varchar_20 role "owner | manager | cashier"
        varchar_100 remember_token
    }

    CATEGORIES {
        bigint id PK
        varchar_255 name UK
        text description
    }

    ITEMS {
        bigint id PK
        bigint category_id FK
        varchar_255 name "indexed"
        text description
        decimal price "8,2"
        int_10 quantity "unsigned, default 0"
    }

    CUSTOMERS {
        bigint id PK
        varchar_255 name
        varchar_255 email UK "nullable"
        varchar_30 phone "nullable"
        text address "nullable"
    }
```

**Notes**
- One category has many items; each item belongs to exactly one category. The foreign key `items.category_id` uses `RESTRICT` on delete.
- `users` and `customers` are standalone tables. Users authenticate against the API; customers are shop records.
