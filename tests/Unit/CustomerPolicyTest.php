<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Customer;
use App\Models\User;
use App\Policies\CustomerPolicy;
use PHPUnit\Framework\TestCase;

class CustomerPolicyTest extends TestCase
{
    private function userWithRole(Role $role): User
    {
        return (new User)->forceFill(['role' => $role]);
    }

    public function test_cashier_can_create_view_update_but_not_delete(): void
    {
        $policy = new CustomerPolicy;
        $cashier = $this->userWithRole(Role::Cashier);

        $this->assertTrue($policy->viewAny($cashier));
        $this->assertTrue($policy->create($cashier));
        $this->assertTrue($policy->update($cashier, new Customer));
        $this->assertFalse($policy->delete($cashier, new Customer));
    }

    public function test_manager_cannot_delete(): void
    {
        $policy = new CustomerPolicy;
        $manager = $this->userWithRole(Role::Manager);

        $this->assertTrue($policy->create($manager));
        $this->assertTrue($policy->update($manager, new Customer));
        $this->assertFalse($policy->delete($manager, new Customer));
    }

    public function test_owner_can_delete(): void
    {
        $policy = new CustomerPolicy;

        $this->assertTrue($policy->delete($this->userWithRole(Role::Owner), new Customer));
    }
}