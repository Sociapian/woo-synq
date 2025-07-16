<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Priya Sharma',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Traditional Silk Saree',
                'description' => 'Beautiful handcrafted silk saree with intricate embroidery work. Perfect for special occasions and festivals.',
                'price' => 2999.00,
                'image_url' => 'https://via.placeholder.com/300x300?text=Silk+Saree',
                'status' => 'created',
            ],
            [
                'name' => 'Handcrafted Jewelry Set',
                'description' => 'Elegant traditional jewelry set made with authentic materials. Includes necklace, earrings, and bangles.',
                'price' => 1499.00,
                'image_url' => 'https://via.placeholder.com/300x300?text=Jewelry+Set',
                'status' => 'synced',
                'wc_product_id' => 12345,
            ],
            [
                'name' => 'Ayurvedic Wellness Products',
                'description' => 'Natural ayurvedic products for skin care and wellness. Made with traditional Indian herbs and ingredients.',
                'price' => 899.00,
                'image_url' => null,
                'status' => 'failed',
            ],
        ];

        foreach ($products as $productData) {
            $user->products()->create($productData);
        }

        $this->command->info('Test user created: test@example.com / password');
        $this->command->info('Sample products created for testing.');
    }
}
