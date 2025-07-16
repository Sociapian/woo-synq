<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected WooCommerceService $wooCommerceService;

    public function __construct(WooCommerceService $wooCommerceService)
    {
        $this->wooCommerceService = $wooCommerceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $products = $request->user()->products()
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json([
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'image_url' => 'nullable|url',
            ]);
            $product = $request->user()->products()->create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'image_url' => $validated['image_url'] ?? null,
                'status' => Product::STATUS_CREATED,
            ]);
            // Sync to WooCommerce
            try {
                $syncResult = $this->wooCommerceService->createProduct($product->toArray());
                if ($syncResult['success']) {
                    $product->update([
                        'wc_product_id' => $syncResult['wc_product_id'],
                        'status' => Product::STATUS_SYNCED,
                    ]);
                    $message = 'Product created and synced to WooCommerce successfully';
                } else {
                    $product->update(['status' => Product::STATUS_FAILED]);
                    $message = 'Product created but failed to sync to WooCommerce';
                }
            } catch (\Exception $e) {
                $product->update(['status' => Product::STATUS_FAILED]);
                $message = 'Product created but failed to sync to WooCommerce';
            }
            return response()->json([
                'message' => $message,
                'product' => $product->fresh()
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        // Ensure user can only access their own products
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure user can only update their own products
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_url' => $request->image_url,
        ]);

        // Sync to WooCommerce if product was previously synced
        if ($product->wc_product_id) {
            try {
                $syncResult = $this->wooCommerceService->updateProduct($product->wc_product_id, $product->toArray());
                
                if ($syncResult['success']) {
                    $product->update(['status' => Product::STATUS_SYNCED]);
                    $message = 'Product updated and synced to WooCommerce successfully';
                } else {
                    $product->update(['status' => Product::STATUS_FAILED]);
                    $message = 'Product updated but failed to sync to WooCommerce';
                    
                    Log::error('WooCommerce update failed', [
                        'product_id' => $product->id,
                        'wc_product_id' => $product->wc_product_id,
                        'error' => $syncResult['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                $product->update(['status' => Product::STATUS_FAILED]);
                $message = 'Product updated but failed to sync to WooCommerce';
                
                Log::error('Exception during WooCommerce update', [
                    'product_id' => $product->id,
                    'wc_product_id' => $product->wc_product_id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $message = 'Product updated successfully';
        }

        return response()->json([
            'message' => $message,
            'product' => $product->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        // Ensure user can only delete their own products
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete from WooCommerce if product was synced
        if ($product->wc_product_id) {
            try {
                $this->wooCommerceService->deleteProduct($product->wc_product_id);
            } catch (\Exception $e) {
                Log::error('Failed to delete product from WooCommerce', [
                    'product_id' => $product->id,
                    'wc_product_id' => $product->wc_product_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Retry sync to WooCommerce
     */
    public function retrySync(Request $request, Product $product)
    {
        // Ensure user can only retry sync for their own products
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            if ($product->wc_product_id) {
                // Update existing product
                $syncResult = $this->wooCommerceService->updateProduct($product->wc_product_id, $product->toArray());
            } else {
                // Create new product
                $syncResult = $this->wooCommerceService->createProduct($product->toArray());
            }
            
            if ($syncResult['success']) {
                $product->update([
                    'wc_product_id' => $syncResult['wc_product_id'] ?? $product->wc_product_id,
                    'status' => Product::STATUS_SYNCED,
                ]);
                
                $message = 'Product synced to WooCommerce successfully';
            } else {
                $product->update(['status' => Product::STATUS_FAILED]);
                $message = 'Failed to sync product to WooCommerce';
                
                Log::error('WooCommerce sync retry failed', [
                    'product_id' => $product->id,
                    'error' => $syncResult['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            $product->update(['status' => Product::STATUS_FAILED]);
            $message = 'Failed to sync product to WooCommerce';
            
            Log::error('Exception during WooCommerce sync retry', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'message' => $message,
            'product' => $product->fresh()
        ]);
    }
}
