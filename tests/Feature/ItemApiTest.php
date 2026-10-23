<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    private function actAsOwner(): void
    {
        $this->actingAs(User::factory()->owner()->create(), 'sanctum');
    }

    public function test_index_returns_items_with_category(): void
    {
        $this->actAsOwner();
        Item::factory()->count(3)->create();

        $this->getJson('/api/items')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'quantity', 'category' => ['id', 'name']]]]);
    }

    public function test_search_filters_items_by_name(): void
    {
        $this->actAsOwner();
        $category = Category::factory()->create();
        Item::factory()->for($category)->create(['name' => 'Chocolate Fudge']);
        Item::factory()->for($category)->create(['name' => 'Double Chocolate']);
        Item::factory()->for($category)->create(['name' => 'Vanilla Bean']);

        $this->getJson('/api/items?search=choc')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/items?search=zzz')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_store_creates_item_and_returns_201(): void
    {
        $this->actAsOwner();
        $category = Category::factory()->create();

        $this->postJson('/api/items', [
            'category_id' => $category->id,
            'name' => 'Mint Chip',
            'price' => 3.25,
            'quantity' => 12,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mint Chip')
            ->assertJsonPath('data.price', 3.25);

        $this->assertDatabaseHas('items', ['name' => 'Mint Chip', 'category_id' => $category->id]);
    }

    public function test_store_validates_input(): void
    {
        $this->actAsOwner();

        $this->postJson('/api/items', [
            'category_id' => 9999,
            'name' => '',
            'price' => -1,
            'quantity' => 'abc',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'name', 'price', 'quantity']);
    }

    public function test_store_rejects_price_too_large_for_column(): void
    {
        $this->actAsOwner();
        $category = Category::factory()->create();

        $this->postJson('/api/items', [
            'category_id' => $category->id,
            'name' => 'Gold Scoop',
            'price' => 1000000,
            'quantity' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['price']);
    }

    public function test_show_returns_404_for_missing_item(): void
    {
        $this->actAsOwner();

        $this->getJson('/api/items/999')->assertNotFound();
    }

    public function test_update_changes_only_sent_fields(): void
    {
        $this->actAsOwner();
        $item = Item::factory()->create(['name' => 'Original', 'quantity' => 10]);

        $this->patchJson("/api/items/{$item->id}", ['quantity' => 99])->assertOk();

        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'Original', 'quantity' => 99]);
    }

    public function test_delete_removes_item(): void
    {
        $this->actAsOwner();
        $item = Item::factory()->create();

        $this->deleteJson("/api/items/{$item->id}")->assertNoContent();

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}