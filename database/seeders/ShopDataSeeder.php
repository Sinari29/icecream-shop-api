<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class ShopDataSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Chocolate' => [
                ['Chocolate Fudge Scoop', 'Rich dark chocolate with fudge swirls', 2.50, 40],
                ['Double Chocolate Chip', 'Chocolate base with chocolate chips', 2.75, 30],
            ],
            'Vanilla' => [
                ['Classic Vanilla Scoop', 'Madagascar vanilla bean', 2.25, 50],
                ['Vanilla Cookie Dough', 'Vanilla with cookie dough pieces', 2.75, 25],
            ],
            'Strawberry' => [
                ['Strawberry Cream Scoop', 'Fresh strawberry and cream', 2.50, 35],
                ['Strawberry Cheesecake', 'Strawberry with cheesecake chunks', 3.00, 20],
            ],
        ];

        foreach ($catalog as $categoryName => $items) {
            $category = Category::updateOrCreate(
                ['name' => $categoryName],
                ['description' => "$categoryName flavored ice cream"]
            );

            foreach ($items as [$name, $description, $price, $quantity]) {
                $category->items()->updateOrCreate(
                    ['name' => $name],
                    ['description' => $description, 'price' => $price, 'quantity' => $quantity]
                );
            }
        }

        $customers = [
            ['Nimal Perera', 'nimal@example.com', '0771234567', '12 Lake Road, Colombo'],
            ['Kamala Silva', 'kamala@example.com', '0712345678', '45 Hill Street, Kandy'],
            ['Sunil Fernando', 'sunil@example.com', '0703456789', '8 Beach Avenue, Galle'],
        ];

        foreach ($customers as [$name, $email, $phone, $address]) {
            Customer::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'address' => $address]
            );
        }
    }
}