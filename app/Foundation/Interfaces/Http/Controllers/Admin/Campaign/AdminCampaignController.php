<?php

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Campaign;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\DiscountCampaignModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\DiscountCampaignProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Foundation\Interfaces\Http\Resources\CampaignResource;

class AdminCampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = DiscountCampaignModel::with(['defaultFile'])->withCount('products')->orderByDesc('created_at');

        if ($request->has('name') && $request->filled('name')) {
            $query->where('title', 'like', '%' . $request->get('name') . '%');
        }

        $data = $query->get();
        return response()->json([
            'success' => true,
            'data' => CampaignResource::collection($data),
        ], 200);
    }

    public function storeUpdate(Request $request)
    {
        $validated = $request->validate([
            'id'   => 'nullable|exists:discount_campaigns,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:discount_campaigns,slug,' . $request->id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);

        $isUpdate = !empty($validated['id']);

        if ($isUpdate) {
            $campaign = DiscountCampaignModel::findOrFail($validated['id']);
            unset($validated['id']); // Prevent mass assignment of id
            $campaign->update($validated);
        } else {
            $campaign = DiscountCampaignModel::create($validated);
        }

        return response()->json([
            'message' => $isUpdate ? 'Banner updated successfully.' : 'Banner created successfully.',
            'data' => $campaign,
        ]);
    }

    public function show(Request $request, $id)
    {
        $campaign = DiscountCampaignModel::with(['files', 'defaultFile'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign),
        ], 200);
    }

    public function storeImage(Request $request, $id)
    {
        $campaign = DiscountCampaignModel::findOrFail($id);

        $validated = $request->validate([
            'image' => 'required|image|max:10240',
            'link' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'alt_text' => 'nullable|string',
        ]);

        try {
            /** @var \App\Foundation\Shared\Application\Contracts\FileUploadService $fileUploadService */
            $fileUploadService = app(\App\Foundation\Shared\Application\Contracts\FileUploadService::class);
            $uploadResult = $fileUploadService->uploadImageAsWebp($request->file('image'), 'campaigns');
            
            $fileData = $uploadResult['file_data'];
            
            DB::table('file_usages')->insert([
                'file_id' => $fileData['id'],
                'usage_type' => 'campaigns',
                'usage_id' => $campaign->id,
                'alt_text' => $validated['alt_text'] ?? $campaign->title,
                'meta' => json_encode([
                    'link' => $validated['link'] ?? null,
                    'start_date' => $validated['start_date'] ?? null,
                    'end_date' => $validated['end_date'] ?? null,
                    'is_default' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully.',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignProducts(Request $request, $id)
    {

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'discount_type' => 'nullable|numeric|in:1,2',
            'discount_value' => 'nullable|numeric|min:0',
        ]);
        $campaign = DiscountCampaignModel::findOrFail($id);

        $productIds = $request->input('product_ids', []);


        // Get existing product IDs for this campaign
        $existingIds = $campaign->products()->pluck('product_id')->toArray();

        // Filter out product IDs that are already assigned
        $newProductIds = array_diff($productIds, $existingIds);

        // Assign only new products
        if (!empty($newProductIds)) {
            $campaign->products()->createMany(
                collect($newProductIds)->map(fn($productId) => [
                    'product_id' => $productId,
                    'discount_type' => $validated['discount_type'] ?? 1, // default value, adjust as needed
                    'discount_value' => $validated['discount_value'] ?? 0, // default value, adjust as needed
                ])->toArray()
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Products assigned to campaign successfully.',
            'data' => $campaign->products()->get(), // Updated list
        ], 200);
    }
    public function getCampaignProducts(Request $request, $id)
    {
        $perPage = is_numeric($request->per_page) && $request->per_page > 0 ? (int) $request->per_page : 30;

        // Get the campaign
        $campaign = DiscountCampaignModel::findOrFail($id);

        // Base query: join with products
        $productsQuery = $campaign->products()->with('product')->orderByDesc('created_at');

        // Filter by product name
        if ($request->filled('name')) {
            $productsQuery->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }


        // Paginate
        $paginated = $productsQuery->paginate($perPage);

        // Transform
        $data = $paginated->getCollection()->transform(function ($discountProduct) {
         
            $product = $discountProduct->product;

            $discountedPrice = $this->calculateDiscountedPrice(
                $product->price, 
                $discountProduct->discount_type, 
                $discountProduct->discount_value
            );

            return [
                'id'             => $discountProduct->id,
                'product_id'     => $product->id,
                'name'           => $product->name,
                'thumb' => [
                    'url' => $product->defaultFile->first()?->url,
                    'alt_text' => $product->defaultFile->first()?->alt_text,
                ],
                'price'          => [
                    "original_price" => $product->price,
                    "discounted_price" => $discountedPrice,
                ],
                'discount' => [ 
                    "type" => $discountProduct->discount_type_label,
                    "value" => $discountProduct->discount_value,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ], 200);
    }

    public function updateCampaignDiscount(Request $request, $campaignId){
        $validated = $request->validate([
            'discount_type'=>'nullable|numeric|in:1,2',
            'discount_value'=> 'nullable|numeric|min:0',
        ]);
        
        try {
            $campaign = DiscountCampaignModel::findOrFail($campaignId);
            $campaign->products()->update([
                "discount_type" => $validated['discount_type'],
                "discount_value" => $validated['discount_value'],
            ]);
            return response([
                "message" => "Discount updated successfully",
            ],200);
        } catch (\Throwable $th) {
            return response([
                "message" => $th->getMessage(),
            ],500);
        }

        

    }


    public function togglePublished($id, Request $request)
    {
        $campaign = DiscountCampaignModel::findOrFail($id);

        $is_published = $request->boolean('is_published') ? 1 : 0;

        $campaign->is_published = $is_published;

        $campaign->save();


        $status = $campaign->is_published ? 'published' : 'unpublished';

        return response()->json([
            'success' => true,
            'message' => "{$campaign->title} is now {$status}",
        ]);
    }





    public function delete(Request $request, $id)
    {
        $banner = DiscountCampaignModel::find($id);
        $banner->delete();
        return response()->json([
            'success' => true,
            'message' => $banner->name . " is deleted",
        ], 200);
    }
    public function removeCampaignProduct(Request $request, $id)
    {
        $product = DiscountCampaignProductModel::find($id);
        $product->delete();
        return response()->json([
            'success' => true,
            'message' => "Item is deleted",
        ], 200);
    }

    public function updateCampaignProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'discount_type'  => 'nullable|numeric|in:1,2',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $product = DiscountCampaignProductModel::findOrFail($id);
        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campaign product updated successfully.',
            'data'    => $product, // optional: return updated product
        ], 200);
    }

    private function calculateDiscountedPrice($originalPrice, $discountType, $discountValue)
    {
        if ($discountType == 1) { // Fixed
            return max(0, $originalPrice - $discountValue);
        }

        if ($discountType == 2) { // Percentage
            return max(0, $originalPrice - ($originalPrice * $discountValue / 100));
        }

        return $originalPrice;
    }
}
