<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    // All roles may view, create and update customers.
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return true;
    }

    // Owner only. Cashier exclusion is a documented assumption
    public function delete(User $user, Customer $customer): bool
    {
        return $user->role === Role::Owner;
    }
}