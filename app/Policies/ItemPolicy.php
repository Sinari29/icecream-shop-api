<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    // Every authenticated role may view items.
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Item $item): bool
    {
        return true;
    }

    // Owner and Manager only. Cashier cannot create items.
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Owner, Role::Manager], true);
    }

    // All roles may edit existing items.
    public function update(User $user, Item $item): bool
    {
        return true;
    }

    // Owner only.
    public function delete(User $user, Item $item): bool
    {
        return $user->role === Role::Owner;
    }
}