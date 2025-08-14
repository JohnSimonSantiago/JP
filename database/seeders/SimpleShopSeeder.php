<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shop;
use App\Models\ShopItem;

class SimpleShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update the current user (Simon) to have a shop since they're already a shop_owner
        $simon = User::where('name', 'Simon')->first();
        
        if ($simon && $simon->role === 'shop_owner') {
            // Create a shop for Simon if they don't have one
            $shop = Shop::firstOrCreate(
                ['owner_id' => $simon->id],
                [
                    'name' => "Simon's Store",
                    'description' => 'Welcome to my awesome shop! Find great items here.',
                    'is_active' => true,
                    'is_verified' => true  // Auto-verify for testing
                ]
            );
            
            // Add some test items to Simon's shop
            $testItems = [
                [
                    'name' => 'Power Boost',
                    'description' => 'Increase your power for 1 hour',
                    'price' => 100,
                    'stock' => 50
                ],
                [
                    'name' => 'Cool Avatar',
                    'description' => 'A really cool avatar for your profile',
                    'price' => 250,
                    'stock' => 25
                ],
                [
                    'name' => 'Premium Badge',
                    'description' => 'Exclusive premium badge',
                    'price' => 500,
                    'stock' => 10
                ]
            ];
            
            foreach ($testItems as $itemData) {
                ShopItem::firstOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'name' => $itemData['name']
                    ],
                    array_merge($itemData, [
                        'shop_id' => $shop->id,
                        'is_active' => true
                    ])
                );
            }
            
            echo "Created shop and items for Simon\n";
        }
        
        // Create a few more test shop owners and shops
        for ($i = 1; $i <= 2; $i++) {
            $owner = User::firstOrCreate(
                ['name' => "Shop Owner {$i}"],
                [
                    'name' => "Shop Owner {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'shop_owner',
                    'points' => 1000,
                    'level' => 1
                ]
            );
            
            $shop = Shop::firstOrCreate(
                ['owner_id' => $owner->id],
                [
                    'name' => "Test Shop {$i}",
                    'description' => "This is test shop number {$i}",
                    'is_active' => true,
                    'is_verified' => true
                ]
            );
            
            // Add a few items to each shop
            ShopItem::firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'name' => "Test Item {$i}"
                ],
                [
                    'shop_id' => $shop->id,
                    'name' => "Test Item {$i}",
                    'description' => "This is a test item from shop {$i}",
                    'price' => 50 * $i,
                    'stock' => 20,
                    'is_active' => true
                ]
            );
        }
        
        echo "Created test shops and items\n";
    }
}