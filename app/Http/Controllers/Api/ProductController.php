<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        
        $products = Product::with(['translations' => function($query) use ($locale) {
            $query->where('locale', $locale);
        }, 'features.translations' => function($query) use ($locale) {
            $query->where('locale', $locale);
        }])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'nullable|string|unique:products,slug',
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean',
            'translations' => 'required|array',
            'translations.*.locale' => 'required|string|in:en,kh,zh,tw',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.subtitle' => 'nullable|string',
            'translations.*.description' => 'nullable|string',
            'translations.*.highlight_text' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*.sort_order' => 'integer',
            'features.*.translations' => 'required|array',
            'features.*.translations.*.locale' => 'required|string|in:en,kh,zh,tw',
            'features.*.translations.*.feature_text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $imagePath = $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null;
            
            $product = Product::create([
                'slug' => $request->slug ?? Str::slug($request->translations[0]['title'] ?? Str::random(10)),
                'image' => $imagePath,
                'status' => $request->status ?? true,
            ]);

            foreach ($request->translations as $trans) {
                $product->translations()->create($trans);
            }

            if ($request->has('features')) {
                foreach ($request->features as $featureData) {
                    $feature = $product->features()->create(['sort_order' => $featureData['sort_order'] ?? 0]);
                    foreach ($featureData['translations'] as $fTrans) {
                        $feature->translations()->create($fTrans);
                    }
                }
            }

            return response()->json(['success' => true, 'data' => $product->load('translations', 'features.translations')], 201);
        });
    }

    public function show($id, Request $request)
    {
        $product = Product::with(['translations', 'features.translations'])->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'slug' => 'nullable|string|unique:products,slug,' . $id,
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean',
            'translations' => 'sometimes|required|array',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $product) {
            if ($request->hasFile('image')) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $product->image = $request->file('image')->store('products', 'public');
            }

            $product->update($request->only(['slug', 'status']));

            if ($request->has('translations')) {
                $product->translations()->delete();
                foreach ($request->translations as $trans) {
                    $product->translations()->create($trans);
                }
            }

            if ($request->has('features')) {
                $product->features()->delete(); // Cascades to feature translations
                foreach ($request->features as $featureData) {
                    $feature = $product->features()->create(['sort_order' => $featureData['sort_order'] ?? 0]);
                    foreach ($featureData['translations'] as $fTrans) {
                        $feature->translations()->create($fTrans);
                    }
                }
            }

            return response()->json(['success' => true, 'data' => $product->load('translations', 'features.translations')]);
        });
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
