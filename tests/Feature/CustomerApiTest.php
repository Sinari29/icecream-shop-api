<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    private function actAsOwner(): void
    {
        $this->actingAs(User::factory()->owner()->create(), 'sanctum');
    }

    public function test_index_returns_customers(): void
    {
        $this->actAsOwner();
        Customer::factory()->count(3)->create();

        $this->getJson('/api/customers')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'phone', 'address']]]);
    }

    public function test_store_creates_customer_and_returns_201(): void
    {
        $this->actAsOwner();

        $this->postJson('/api/customers', [
            'name' => 'Amal Jayasinghe',
            'email' => 'amal@example.com',
            'phone' => '0770000000',
        ])->assertCreated()->assertJsonPath('data.name', 'Amal Jayasinghe');

        $this->assertDatabaseHas('customers', ['email' => 'amal@example.com']);
    }

    public function test_store_validates_input(): void
    {
        $this->actAsOwner();

        $this->postJson('/api/customers', ['name' => '', 'email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $this->actAsOwner();
        Customer::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/customers', ['name' => 'Dup', 'email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_show_returns_404_for_missing_customer(): void
    {
        $this->actAsOwner();

        $this->getJson('/api/customers/999')->assertNotFound();
    }

    public function test_update_allows_keeping_own_email(): void
    {
        $this->actAsOwner();
        $customer = Customer::factory()->create(['email' => 'me@example.com']);

        $this->patchJson("/api/customers/{$customer->id}", ['email' => 'me@example.com', 'phone' => '0711111111'])
            ->assertOk();
    }

    public function test_update_rejects_another_customers_email(): void
    {
        $this->actAsOwner();
        Customer::factory()->create(['email' => 'other@example.com']);
        $customer = Customer::factory()->create();

        $this->patchJson("/api/customers/{$customer->id}", ['email' => 'other@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_delete_removes_customer(): void
    {
        $this->actAsOwner();
        $customer = Customer::factory()->create();

        $this->deleteJson("/api/customers/{$customer->id}")->assertNoContent();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}