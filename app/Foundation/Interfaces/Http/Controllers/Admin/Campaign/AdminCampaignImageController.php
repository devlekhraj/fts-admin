<?php

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Campaign;



use Jed\Banners\App\Banner;
use Illuminate\Http\Request;
use Jed\Banners\App\BannerImage;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;

class AdminCampaignImageController extends Controller
{
    // public function index(Request $request)
    // {
    //     $banners = Banner::orderByDesc('created_at')->get();
    //     return response()->json([
    //         'success' => true,
    //         'data' => BannerResource::collection($banners)
    //     ], 200);
    // }

    // public function storeOrUpdate(Request $request)
    // {
    //     $this->validate($request, [
    //         'link' => 'nullable|string',
    //         'start_date' => 'nullable|date',
    //         'end_date' => 'nullable|date',
    //         'content' => 'nullable|string',
    //         'image' => 'nullable|file|image', // required only if creating
    //         'banner_id' => 'required|exists:banners,id',
    //         'id' => 'nullable|exists:banner_images,id',
    //     ]);

    //     $id = $request->id;

    //     if ($request->filled('id')) {
    //         // Update existing
    //         $banner_image = BannerImage::find($id);
    //         $banner_image->update([
    //             'link' => $request->link,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'content' => $request->content,
    //         ]);

    //         if ($request->hasFile('image')) {
    //             $banner_image->clearMediaCollection(); // optional: clear previous image
    //             $banner_image->addMedia($request->file('image'))->toMediaCollection();
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Banner image successfully updated',
    //         ]);
    //     } else {
    //         // Create new
    //         $this->validate($request, [
    //             'image' => 'required|file|image', // enforce required for creation
    //         ]);


    //         $banner_image = BannerImage::create([
    //             'banner_id' => $request->banner_id,
    //             'link' => $request->link,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'content' => $request->content,
    //         ]);

    //         $banner_image->addMedia($request->file('image'))->toMediaCollection();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Banner image successfully added',
    //         ]);
    //     }
    // }


    // public function show(Request $request, $id)
    // {
    //     $banner = Banner::find($id);
    //     $banner->load('banners');
    //     return response()->json([
    //         'success' => true,
    //         'data' => new BannerResource($banner),
    //     ], 200);
    // }
}
