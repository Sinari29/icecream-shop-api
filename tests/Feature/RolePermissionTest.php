<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [role, resource, action, expected HTTP status]
     * Mirrors the assessment's role rules. The Cashier customer-delete restriction is a documented assumption.
     */
    public static function matrix(): array
    {
        return [
            // ---- Items ----
            'owner view items' => ['owner', 'item', 'view', 200],
            'manager view items' => ['manager', 'item', 'view', 200],
            'cashier view items' => ['cashier', 'item', 'view', 200],

            'owner create items' => ['owner', 'item', 'create', 201],
            'manager create items' => ['manager', 'item', 'create', 201],
            'cashier create items' => ['cashier', 'item', 'create', 403],

            'owner update items' => ['owner', 'item', 'update', 200],
            'manager update items' => ['manager', 'item', 'update', 200],
            'cashier update items' => ['cashier', 'item', 'update', 200],

            'owner delete items' => ['owner', 'item', 'delete', 204],
            'manager delete items' => ['manager', 'item', 'delete', 403],
            'cashier delete items' => ['cashier', 'item', 'delete', 403],

            // ---- Customers ----
            'owner view customers' => ['owner', 'customer', 'view', 200],
            'manager view customers' => ['manager', 'customer', 'view', 200],
            'cashier view customers' => ['cashier', 'customer', 'view', 200],

            'owner create customers' => ['owner', 'customer', 'create', 201],
            'manager create customers' => ['manager', 'customer', 'create', 201],
            'cashier create customers' => ['cashier', 'customer', 'create', 201],

            'owner update customers' => ['owner', 'customer', 'update', 200],
            'manager update customers' => ['manager', 'customer', 'update', 200],
            'cashier update customers' => ['cashier', 'customer', 'update', 200],

            'owner delete customers' => ['owner', 'customer', 'delete', 204],
            'manager delete customers' => ['manager', 'customer', 'delete', 403],
            'cashier delete customers (assumption)' => ['cashier', 'customer', 'delete', 403],
        ];
    }

    #[DataProvider('matrix')]
    public function test_role_permission(string $role, string $resource, string $action, int $expected): void
    {
        $user = User::factory()->{$role}()->create();
        $this->actingAs($user, 'sanctum');

        $response = $resource === 'item'
            ? $this->performItemAction($action)
            : $this->performCustomerAction($action);

        $response->assertStatus($expected);
    }

    private function performItemAction(string $action)
    {
        $category = Category::factory()->create();
        $item = Item::factory()->for($category)->create();

        return match ($action) {
            'view' => $this->getJson("/api/items/{$item->id}"),
            'create' => $this->postJson('/api/items', [
                'category_id' => $category->id,
                'name' => 'New Scoop',
                'price' => 2.50,
                'quantity' => 10,
            ]),
            'update' => $this->patchJson("/api/items/{$item->id}", ['quantity' => 5]),
            'delete' => $this->deleteJson("/api/items/{$item->id}"),
        };
    }

    private function performCustomerAction(string $action)
    {
        $customer = Customer::factory()->create();

        return match ($action) {
            'view' => $this->getJson("/api/customers/{$customer->id}"),
            'create' => $this->postJson('/api/customers', ['name' => 'New Customer']),
            'update' => $this->patchJson("/api/customers/{$customer->id}", ['phone' => '0700000000']),
            'delete' => $this->deleteJson("/api/customers/{$customer->id}"),
        };
    }
}