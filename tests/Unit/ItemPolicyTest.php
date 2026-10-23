<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Item;
use App\Models\User;
use App\Policies\ItemPolicy;
use PHPUnit\Framework\TestCase;

class ItemPolicyTest extends TestCase
{
    private function userWithRole(Role $role): User
    {
        return (new User)->forceFill(['role' => $role]);
    }

    public function test_cashier_can_view_and_update_but_not_create_or_delete(): void
    {
        $policy = new ItemPolicy;
        $cashier = $this->userWithRole(Role::Cashier);

        $this->assertTrue($policy->viewAny($cashier));
        $this->assertTrue($policy->update($cashier, new Item));
        $this->assertFalse($policy->create($cashier));
        $this->assertFalse($policy->delete($cashier, new Item));
    }

    public function test_manager_can_create_but_not_delete(): void
    {
        $policy = new ItemPolicy;
        $manager = $this->userWithRole(Role::Manager);

        $this->assertTrue($policy->create($manager));
        $this->assertTrue($policy->update($manager, new Item));
        $this->assertFalse($policy->delete($manager, new Item));
    }

    public function test_owner_has_full_access(): void
    {
        $policy = new ItemPolicy;
        $owner = $this->userWithRole(Role::Owner);

        $this->assertTrue($policy->viewAny($owner));
        $this->assertTrue($policy->create($owner));
        $this->assertTrue($policy->update($owner, new Item));
        $this->assertTrue($policy->delete($owner, new Item));
    }
}