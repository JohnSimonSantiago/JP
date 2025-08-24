<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    /**
     * Check if user is admin middleware
     */
    private function checkAdmin()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }
        return null;
    }

    /**
     * Display all gallery images (public access)
     */
    public function index()
    {
        try {
            // For now, check if GalleryImage model exists
            if (!class_exists('App\Models\GalleryImage')) {
                return response()->json([
                    'success' => true,
                    'images' => [], // Return empty array for testing
                    'message' => 'GalleryImage model not found - migration may not have been run'
                ]);
            }

            $images = GalleryImage::where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->with(['creator', 'updater'])
                ->get();

            return response()->json([
                'success' => true,
                'images' => $images
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'images' => [], // Return empty array to prevent frontend errors
                'error' => $e->getMessage(),
                'message' => 'Gallery table may not exist yet'
            ]);
        }
    }

    /**
     * Test endpoint to verify controller and auth are working
     */
    

    /**
     * Store a new gallery image (admin only)
     */
   /**
     * Store a new gallery image (admin only) - UPDATED
     */
    public function store(Request $request)
    {
        // Check admin privileges
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;

        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image_url' => 'required|url',
                'link_url' => 'nullable|url',              // NEW: Optional URL validation
                'open_in_new_tab' => 'sometimes|boolean',   // NEW: Optional boolean
                'display_order' => 'sometimes|integer|min:0',
                'is_active' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $image = GalleryImage::create([
                'title' => $request->title,
                'description' => $request->description,
                'image_url' => $request->image_url,
                'link_url' => $request->link_url,                    // NEW
                'open_in_new_tab' => $request->open_in_new_tab ?? true, // NEW: Default true
                'display_order' => $request->display_order ?? 0,
                'is_active' => $request->is_active ?? true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image added successfully',
                'image' => $image->load(['creator', 'updater'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a gallery image (admin only) - UPDATED
     */
    public function update(Request $request, $id)
    {
        // Check admin privileges
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;

        try {
            $image = GalleryImage::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'image_url' => 'sometimes|url',
                'link_url' => 'nullable|url',              // NEW: Optional URL validation
                'open_in_new_tab' => 'sometimes|boolean',   // NEW: Optional boolean
                'display_order' => 'sometimes|integer|min:0',
                'is_active' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only([
                'title', 
                'description', 
                'image_url', 
                'link_url',          // NEW
                'open_in_new_tab',   // NEW
                'display_order', 
                'is_active'
            ]);
            $updateData['updated_by'] = Auth::id();

            $image->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'image' => $image->fresh(['creator', 'updater'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific gallery image
     */
    public function show($id)
    {
        try {
            $image = GalleryImage::with(['creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'image' => $image
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

   
    /**
     * Remove a gallery image (admin only)
     */
    public function destroy($id)
    {
        // Check admin privileges
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;

        try {
            $image = GalleryImage::findOrFail($id);
            
            // If there's a local file, delete it
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image file (admin only)
     */
    public function uploadImage(Request $request)
    {
        // Check admin privileges
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;

        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('gallery', $filename, 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'image_url' => asset($url),
                    'image_path' => $path,
                    'filename' => $filename
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder images (admin only)
     */
    public function reorder(Request $request)
    {
        // Check admin privileges
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;

        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array',
                'images.*.id' => 'required|exists:gallery_images,id',
                'images.*.display_order' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->images as $imageData) {
                GalleryImage::where('id', $imageData['id'])
                    ->update([
                        'display_order' => $imageData['display_order'],
                        'updated_by' => Auth::id()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}