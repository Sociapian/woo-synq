<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.woocommerce.base_url'), '/');
        $this->consumerKey = config('services.woocommerce.consumer_key');
        $this->consumerSecret = config('services.woocommerce.consumer_secret');
    }

    /**
     * Create a product in WooCommerce
     */
    public function createProduct(array $productData): array
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withoutVerifying() // Disable SSL verification for development
                ->post($this->baseUrl . '/wp-json/wc/v3/products', [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'regular_price' => (string) $productData['price'],
                    'images' => $productData['image_url'] ? [
                        ['src' => $productData['image_url']]
                    ] : [],
                    'status' => 'publish',
                    'currency' => 'INR'
                ]);
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'wc_product_id' => $data['id'],
                    'data' => $data
                ];
            }
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update a product in WooCommerce
     */
    public function updateProduct(int $wcProductId, array $productData): array
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withoutVerifying() // Disable SSL verification for development
                ->put($this->baseUrl . "/wp-json/wc/v3/products/{$wcProductId}", [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'regular_price' => (string) $productData['price'],
                    'images' => $productData['image_url'] ? [
                        ['src' => $productData['image_url']]
                    ] : [],
                    'currency' => 'INR'
                ]);
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data
                ];
            }
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a product in WooCommerce
     */
    public function deleteProduct(int $wcProductId): array
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->withoutVerifying() // Disable SSL verification for development
                ->delete($this->baseUrl . "/wp-json/wc/v3/products/{$wcProductId}");
            if ($response->successful()) {
                return [
                    'success' => true
                ];
            }
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test WooCommerce connection
     */
    public function testConnection(): array
    {
        try {
            $testUrl = $this->baseUrl . '/wp-json/wc/v3/products?per_page=1';
            $response = null;
            $lastError = null;
            try {
                $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                    ->timeout(30)
                    ->withoutVerifying()
                    ->get($testUrl);
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
            if (!$response || !$response->successful()) {
                try {
                    $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                        ->timeout(30)
                        ->withOptions([
                            'verify' => false,
                            'curl' => [
                                CURLOPT_SSL_VERIFYPEER => false,
                                CURLOPT_SSL_VERIFYHOST => false,
                            ]
                        ])
                        ->get($testUrl);
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }
            }
            if (!$response || !$response->successful()) {
                $httpUrl = str_replace('https://', 'http://', $testUrl);
                try {
                    $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                        ->timeout(30)
                        ->get($httpUrl);
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }
            }
            if ($response) {
                if ($response->successful()) {
                    return [
                        'success' => true,
                        'message' => 'WooCommerce connection successful',
                        'status' => $response->status()
                    ];
                }
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status' => $response->status(),
                    'url' => $testUrl
                ];
            }
            return [
                'success' => false,
                'error' => $lastError ?? 'All connection methods failed',
                'url' => $testUrl
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $this->baseUrl . '/wp-json/wc/v3/products?per_page=1'
            ];
        }
    }
} 