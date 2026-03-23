<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductListController extends Controller
{
    public function getList(Request $request): JsonResponse
    {
        try {
            $products = ProductModel::query()->with('defaultFile');

            if ($request->has('brand_id')) {
                $products = $products->where('brand_id', $request->input('brand_id'));
            }

            if ($request->has('category_id')) {
                $products = $products->whereHas('categories', function ($query) use ($request) {
                    $query->where('product_categories.id', $request->input('category_id'));
                });
            }

            if ($request->has('name')) {
                $search = '%' . $request->input('name') . '%';

                $products = $products->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('highlights', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('short_description', 'like', $search)
                        ->orWhereRaw("JSON_SEARCH(attributes, 'all', ?) IS NOT NULL", [$search]);
                });
            }

            if ($request->has('search')) {
                $search = '%' . $request->input('search') . '%';

                $products = $products->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('highlights', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('short_description', 'like', $search)
                        ->orWhereRaw("JSON_SEARCH(attributes, 'all', ?) IS NOT NULL", [$search]);
                });
            }

            if ($categoryId = $request->input('category_id')) {
                $products->whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('product_categories.id', $categoryId);
                });
            }

            if ($brandId = $request->input('brand_id')) {
                $products->where('brand_id', $brandId);
            }

            if ($campaignId = $request->input('campaign_id')) {
                $products->whereDoesntHave('campaignProducts', function ($query) use ($campaignId) {
                    $query->where('campaign_id', $campaignId);
                });
            }

            $perPage = $request->input('per_page', 20);
            $paginatedProducts = $products->select('id', 'name', 'price', 'slug')->paginate($perPage);
            $productSummaries = $paginatedProducts->getCollection()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price ?? null,
                    'thumb' => $product->thumb,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $productSummaries,
                'meta' => [
                    'current_page' => $paginatedProducts->currentPage(),
                    'per_page' => $paginatedProducts->perPage(),
                    'total' => $paginatedProducts->total(),
                    'last_page' => $paginatedProducts->lastPage(),
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving products.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
