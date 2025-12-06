<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $query = Product::where('merchant_id', $merchantId)
            ->with(['category', 'images']);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($product) {
                $primaryImage = $product->images->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price_cents' => $product->price_cents,
                    'category_id' => $product->category_id,
                    'category' => $product->category,
                    'store_id' => $product->store_id,
                    'is_active' => $product->is_active,
                    'is_shippable' => $product->is_shippable,
                    'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                    'images_count' => $product->images->count(),
                ];
            });

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'store' => $store,
            'user' => $user,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        // Only managers and owners can create products
        if ($user->role === 'staff') {
            abort(403, 'Staff members cannot create products.');
        }

        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Store::where('merchant_id', $merchantId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Create', [
            'store' => $store,
            'user' => $user,
            'categories' => $categories,
            'stores' => $stores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only managers and owners can create products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot create products.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_shippable' => ['boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'length_cm' => ['nullable', 'integer', 'min:0'],
            'width_cm' => ['nullable', 'integer', 'min:0'],
            'height_cm' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ]);

        // Convert price to cents
        $validated['price_cents'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        // Remove images from validated data (handled separately)
        $images = $request->file('images');
        unset($validated['images']);

        $validated['merchant_id'] = $request->user()->merchant_id;

        $product = Product::create($validated);

        // Handle image uploads
        if ($images) {
            foreach ($images as $index => $image) {
                $path = $image->store('products', 'public');

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Log product creation
        AuditLogger::logCreated('Product', $product->id, [
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'category_id' => $product->category_id,
            'is_active' => $product->is_active,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Product $product): Response
    {
        $user = $request->user();

        // Only managers and owners can edit products
        if ($user->role === 'staff') {
            abort(403, 'Staff members cannot edit products.');
        }

        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $merchantId) {
            abort(403);
        }

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Store::where('merchant_id', $merchantId)
            ->orderBy('name')
            ->get();

        $product->load(['category', 'customizationGroups.options', 'images']);

        return Inertia::render('Products/Edit', [
            'store' => $store,
            'user' => $user,
            'product' => $product,
            'categories' => $categories,
            'stores' => $stores,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Only managers and owners can update products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot update products.');
        }

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_shippable' => ['boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'length_cm' => ['nullable', 'integer', 'min:0'],
            'width_cm' => ['nullable', 'integer', 'min:0'],
            'height_cm' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);

        // Convert price to cents
        $validated['price_cents'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        // Remove images and delete_images from validated data (handled separately)
        $images = $request->file('images');
        $deleteImages = $request->input('delete_images', []);
        unset($validated['images'], $validated['delete_images']);

        // Track changes for audit log
        $changes = [];
        foreach ($validated as $key => $newValue) {
            $oldValue = $product->getOriginal($key);
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $product->update($validated);

        // Handle image deletions
        if (!empty($deleteImages)) {
            $imagesToDelete = \App\Models\ProductImage::whereIn('id', $deleteImages)
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Handle new image uploads
        if ($images) {
            $currentMaxOrder = $product->images()->max('sort_order') ?? -1;

            foreach ($images as $index => $image) {
                $path = $image->store('products', 'public');

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $currentMaxOrder + $index + 1,
                    'is_primary' => $product->images()->count() === 0 && $index === 0,
                ]);
            }
        }

        // Log product update if there were changes
        if (!empty($changes)) {
            AuditLogger::logUpdated('Product', $product->id, $changes);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        // Only managers and owners can delete products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot delete products.');
        }

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        // Delete product images from storage and database
        $images = $product->images;
        foreach ($images as $image) {
            // Delete the physical file from storage
            if (\Storage::disk('public')->exists($image->image_path)) {
                \Storage::disk('public')->delete($image->image_path);
            }
            // Delete the database record
            $image->delete();
        }

        // Log product deletion before deleting
        AuditLogger::logDeleted('Product', $product->id, [
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'category_id' => $product->category_id,
            'images_deleted' => $images->count(),
        ]);

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Import products from CSV file.
     */
    public function import(Request $request)
    {
        // Only managers and owners can import products
        if ($request->user()->role === 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Staff members cannot import products.',
            ], 403);
        }

        // Set execution time to 5 minutes for large imports with images
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // 5MB max
        ]);

        $merchantId = $request->user()->merchant_id;
        $storeId = session('store_id');

        $file = $request->file('csv_file');
        $imported = 0;
        $failed = 0;
        $errors = [];

        try {
            // Read and parse CSV
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (empty($csvData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file is empty.',
                ], 422);
            }

            // Get headers from first row
            $headers = array_map('trim', array_map('strtolower', $csvData[0]));

            // Validate required headers
            if (!in_array('name', $headers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file must contain a "name" column.',
                ], 422);
            }

            if (!in_array('price_cents', $headers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file must contain a "price_cents" column.',
                ], 422);
            }

            // Process each row (skip header)
            for ($i = 1; $i < count($csvData); $i++) {
                $row = $csvData[$i];

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Map CSV columns to array
                    $data = array_combine($headers, $row);

                    // Validate and prepare product data
                    $productData = [
                        'merchant_id' => $merchantId,
                        'store_id' => $storeId,
                        'name' => trim($data['name'] ?? ''),
                        'description' => trim($data['description'] ?? ''),
                        'price_cents' => (int) ($data['price_cents'] ?? 0),
                        'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                        'is_shippable' => isset($data['is_shippable']) ? (bool) $data['is_shippable'] : true,
                        'weight_grams' => isset($data['weight_grams']) && $data['weight_grams'] !== '' ? (int) $data['weight_grams'] : null,
                        'length_cm' => isset($data['length_cm']) && $data['length_cm'] !== '' ? (int) $data['length_cm'] : null,
                        'width_cm' => isset($data['width_cm']) && $data['width_cm'] !== '' ? (int) $data['width_cm'] : null,
                        'height_cm' => isset($data['height_cm']) && $data['height_cm'] !== '' ? (int) $data['height_cm'] : null,
                    ];

                    // Validate required fields
                    if (empty($productData['name'])) {
                        $errors[] = "Row {$i}: Product name is required.";
                        $failed++;
                        continue;
                    }

                    if ($productData['price_cents'] <= 0) {
                        $errors[] = "Row {$i}: Price must be greater than 0.";
                        $failed++;
                        continue;
                    }

                    // Create product
                    $product = Product::create($productData);

                    // Handle image URLs if provided
                    $imageUrls = [];
                    for ($j = 1; $j <= 5; $j++) {
                        $key = $j === 1 ? 'image_url' : "image_url_{$j}";
                        if (isset($data[$key]) && !empty(trim($data[$key]))) {
                            $imageUrls[] = trim($data[$key]);
                        }
                    }

                    if (!empty($imageUrls)) {
                        foreach ($imageUrls as $index => $imageUrl) {
                            try {
                                // Download and store the image
                                $imageContents = @file_get_contents($imageUrl);

                                if ($imageContents === false) {
                                    $errors[] = "Row {$i}: Failed to download image from {$imageUrl}";
                                    continue;
                                }

                                // Generate unique filename
                                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                                if (empty($extension) || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    $extension = 'jpg'; // Default extension
                                }

                                $filename = uniqid('product_') . '.' . $extension;
                                $path = 'products/' . $filename;

                                // Store the image
                                \Storage::disk('public')->put($path, $imageContents);

                                // Create ProductImage record
                                \App\Models\ProductImage::create([
                                    'product_id' => $product->id,
                                    'image_path' => $path,
                                    'sort_order' => $index,
                                    'is_primary' => $index === 0,
                                ]);
                            } catch (\Exception $imgException) {
                                $errors[] = "Row {$i}: Failed to process image {$imageUrl}: " . $imgException->getMessage();
                            }
                        }
                    }

                    // Log product creation
                    AuditLogger::logCreated('Product', $product->id, [
                        'name' => $product->name,
                        'price_cents' => $product->price_cents,
                        'source' => 'CSV Import',
                        'images_imported' => count($imageUrls),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$i}: " . $e->getMessage();
                    $failed++;
                }
            }

            $message = "Import completed: {$imported} products imported";
            if ($failed > 0) {
                $message .= ", {$failed} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process CSV file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete products.
     */
    public function bulkDelete(Request $request)
    {
        // Only managers and owners can delete products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot delete products.');
        }

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $merchantId = $request->user()->merchant_id;
        $productIds = $validated['product_ids'];

        // Get products and ensure they belong to the merchant
        $products = Product::whereIn('id', $productIds)
            ->where('merchant_id', $merchantId)
            ->get();

        if ($products->count() !== count($productIds)) {
            return back()->withErrors(['error' => 'Some products could not be found or do not belong to your merchant.']);
        }

        $count = $products->count();
        $totalImagesDeleted = 0;

        // Delete products
        foreach ($products as $product) {
            // Delete product images from storage and database
            $images = $product->images;
            foreach ($images as $image) {
                // Delete the physical file from storage
                if (\Storage::disk('public')->exists($image->image_path)) {
                    \Storage::disk('public')->delete($image->image_path);
                }
                // Delete the database record
                $image->delete();
                $totalImagesDeleted++;
            }

            // Log deletion
            AuditLogger::logDeleted('Product', $product->id, [
                'name' => $product->name,
                'price_cents' => $product->price_cents,
                'bulk_operation' => true,
                'images_deleted' => $images->count(),
            ]);

            $product->delete();
        }

        $message = "{$count} product" . ($count > 1 ? 's' : '') . " deleted successfully";
        if ($totalImagesDeleted > 0) {
            $message .= " ({$totalImagesDeleted} image" . ($totalImagesDeleted > 1 ? 's' : '') . " removed)";
        }

        return redirect()->route('products.index')->with('success', $message . ".");
    }

    /**
     * Bulk update products.
     */
    public function bulkUpdate(Request $request)
    {
        // Only managers and owners can update products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot update products.');
        }

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'exists:products,id'],
            'is_active' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $merchantId = $request->user()->merchant_id;
        $productIds = $validated['product_ids'];

        // Get products and ensure they belong to the merchant
        $products = Product::whereIn('id', $productIds)
            ->where('merchant_id', $merchantId)
            ->get();

        if ($products->count() !== count($productIds)) {
            return back()->withErrors(['error' => 'Some products could not be found or do not belong to your merchant.']);
        }

        // Prepare update data (only include fields that were provided)
        $updateData = [];
        if (isset($validated['is_active']) && $validated['is_active'] !== '') {
            $updateData['is_active'] = (bool) $validated['is_active'];
        }
        if (isset($validated['category_id']) && $validated['category_id'] !== '') {
            $updateData['category_id'] = $validated['category_id'];
        }

        // If no fields to update, return
        if (empty($updateData)) {
            return back()->with('info', 'No changes were made.');
        }

        $count = 0;

        // Update products
        foreach ($products as $product) {
            $changes = [];
            foreach ($updateData as $key => $newValue) {
                $oldValue = $product->getOriginal($key);
                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }

            if (!empty($changes)) {
                $product->update($updateData);

                // Log update
                AuditLogger::logUpdated('Product', $product->id, array_merge(
                    $changes,
                    ['bulk_operation' => true]
                ));

                $count++;
            }
        }

        return redirect()->route('products.index')
            ->with('success', "{$count} product" . ($count > 1 ? 's' : '') . " updated successfully.");
    }
}
