<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Shop;
use App\Models\ShopItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        $admin = User::create([
            'name'       => 'admin',
            'email'      => 'admin@levelup.test',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'is_approved' => true,
        ]);

        // 3 shop owners with shops and items
        $shopData = [
            ['username' => 'shop1', 'shop' => 'Snack Haven',    'items' => ['Chips', 'Juice', 'Candy']],
            ['username' => 'shop2', 'shop' => 'Tech Corner',    'items' => ['USB Hub', 'Mouse Pad', 'Cable']],
            ['username' => 'shop3', 'shop' => 'Style Studio',   'items' => ['Tote Bag', 'Keychain', 'Sticker Pack']],
        ];

        foreach ($shopData as $sd) {
            $owner = User::create([
                'name'       => $sd['username'],
                'email'      => $sd['username'] . '@levelup.test',
                'password'   => Hash::make('password'),
                'role'       => 'shop_owner',
                'is_approved' => true,
            ]);

            $shop = Shop::create([
                'owner_id'    => $owner->id,
                'name'        => $sd['shop'],
                'description' => 'Test shop for ' . $sd['shop'],
                'is_active'   => true,
                'is_verified' => true,
            ]);

            foreach ($sd['items'] as $itemName) {
                ShopItem::create([
                    'shop_id'    => $shop->id,
                    'name'       => $itemName,
                    'description' => 'Test item: ' . $itemName,
                    'cash_price' => rand(1, 10) * 10,
                    'price'      => rand(5, 50),
                    'stock'      => 100,
                    'is_active'  => true,
                ]);
            }
        }

        // 10 regular test users (test1 to test10)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name'       => 'test' . $i,
                'email'      => 'test' . $i . '@levelup.test',
                'password'   => Hash::make('password'),
                'role'       => 'user',
                'is_approved' => true,
            ]);
        }
    }
}