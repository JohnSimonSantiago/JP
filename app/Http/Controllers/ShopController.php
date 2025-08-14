<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopItem;
use App\Models\ShopReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Display all shops
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $sort = $request->get('sort', 'name'); // name, rating, followers, newest
            
            $query = Shop::with(['owner:id,name', 'reviews'])
                ->public()
                ->withCount(['activeItems', 'followers', 'reviews']);
            
            // Search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                          $ownerQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            // Sorting
            switch ($sort) {
                case 'rating':
                    $query->withAvg('reviews', 'rating')
                          ->orderByDesc('reviews_avg_rating')
                          ->orderBy('name');
                    break;
                case 'followers':
                    $query->orderByDesc('followers_count')->orderBy('name');
                    break;
                case 'newest':
                    $query->orderByDesc('created_at');
                    break;
                default: // name
                    $query->orderBy('name');
                    break;
            }
            
            $shops = $query->paginate(12);
            
            return response()->json([
                'success' => true,
                'shops' => $shops,
                'user' => Auth::user() // This will be null if not authenticated
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shops',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific shop with its items
     */
    public function show(Shop $shop, Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user can view this shop
            if (!$shop->canBeViewedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found or not available'
                ], 404);
            }
            
            // Load shop with relationships
            $shop->load([
                'owner:id,name,profile_image',
                'reviews' => function ($query) {
                    $query->with('user:id,name,profile_image')
                          ->orderByDesc('created_at')
                          ->limit(5);
                },
                'followers:id,name'
            ]);
            
            // Get shop items with filters
            $search = $request->get('search');
            $sort = $request->get('sort', 'name');
            
            $itemsQuery = $shop->activeItems()->with(['purchases' => function ($query) {
                $query->where('status', 'completed');
            }]);
            
            if ($search) {
                $itemsQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Sort items
            switch ($sort) {
                case 'price_low':
                    $itemsQuery->orderBy('price');
                    break;
                case 'price_high':
                    $itemsQuery->orderByDesc('price');
                    break;
                case 'newest':
                    $itemsQuery->orderByDesc('created_at');
                    break;
                case 'popular':
                    $itemsQuery->withCount(['purchases' => function ($q) {
                        $q->where('status', 'completed');
                    }])->orderByDesc('purchases_count');
                    break;
                default:
                    $itemsQuery->orderBy('name');
                    break;
            }
            
            $items = $itemsQuery->paginate(20);
            
            // Check if user is following this shop
            $isFollowing = $user ? $shop->isFollowedBy($user) : false;
            
            // Check if user can review this shop
            $canReview = $user ? $user->canReviewShop($shop) : false;
            
            return response()->json([
                'success' => true,
                'shop' => $shop,
                'items' => $items,
                'is_following' => $isFollowing,
                'can_review' => $canReview,
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Follow/Unfollow a shop
     */
    public function toggleFollow(Shop $shop)
    {
        $user = Auth::user();
        
        if (!$shop->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }
        
        try {
            $following = $shop->toggleFollow($user);
            
            return response()->json([
                'success' => true,
                'following' => $following,
                'message' => $following ? 'Shop followed!' : 'Shop unfollowed!',
                'follower_count' => $shop->fresh()->follower_count
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update follow status'
            ], 500);
        }
    }

    /**
     * Add a review to a shop
     */
    public function addReview(Shop $shop, Request $request)
    {
        $request->validate(ShopReview::rules());
        
        $user = Auth::user();
        
        if (!$user->canReviewShop($shop)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot review this shop'
            ], 403);
        }
        
        try {
            $review = ShopReview::create([
                'shop_id' => $shop->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);
            
            $review->load('user:id,name,profile_image');
            
            return response()->json([
                'success' => true,
                'message' => 'Review added successfully!',
                'review' => $review,
                'shop_rating' => $shop->fresh()->average_rating
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add review'
            ], 500);
        }
    }

    /**
     * Get shop reviews
     */
    public function getReviews(Shop $shop, Request $request)
    {
        try {
            $rating = $request->get('rating');
            
            $query = $shop->reviews()->with('user:id,name,profile_image');
            
            if ($rating) {
                $query->where('rating', $rating);
            }
            
            $reviews = $query->orderByDesc('created_at')->paginate(10);
            
            // Get rating distribution
            $ratingDistribution = $shop->reviews()
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderByDesc('rating')
                ->get()
                ->pluck('count', 'rating');
            
            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'rating_distribution' => $ratingDistribution,
                'average_rating' => $shop->average_rating,
                'total_reviews' => $shop->total_reviews
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews'
            ], 500);
        }
    }

    /**
     * Shop Owner: Create a new shop
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canCreateShop()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot create a shop'
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:shops,name',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096'
        ]);
        
        try {
            $data = $request->only(['name', 'description']);
            $data['owner_id'] = $user->id;
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('shop-logos', 'public');
            }
            
            // Handle banner upload
            if ($request->hasFile('banner')) {
                $data['banner'] = $request->file('banner')->store('shop-banners', 'public');
            }
            
            $shop = Shop::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Shop created successfully! It will be activated after admin verification.',
                'shop' => $shop
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Shop Owner: Update shop
     */
    public function update(Shop $shop, Request $request)
    {
        if (!$shop->canBeEditedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $request->validate([
            'name' => 'string|max:255|unique:shops,name,' . $shop->id,
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
            'settings' => 'nullable|array'
        ]);
        
        try {
            $data = $request->only(['name', 'description', 'settings']);
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                if ($shop->logo) {
                    Storage::disk('public')->delete($shop->logo);
                }
                $data['logo'] = $request->file('logo')->store('shop-logos', 'public');
            }
            
            // Handle banner upload
            if ($request->hasFile('banner')) {
                if ($shop->banner) {
                    Storage::disk('public')->delete($shop->banner);
                }
                $data['banner'] = $request->file('banner')->store('shop-banners', 'public');
            }
            
            $shop->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Shop updated successfully',
                'shop' => $shop->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Shop Owner: Get own shop dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->hasShop()) {
            return response()->json([
                'success' => false,
                'message' => 'No shop found'
            ], 404);
        }
        
        try {
            $shop = $user->ownedShop;
            $statistics = $shop->getStatistics();
            
            // Get recent orders
            $recentOrders = $shop->purchases()
                ->with(['user:id,name', 'shopItem:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
            
            // Get pending orders
            $pendingOrders = $shop->purchases()
                ->pending()
                ->with(['user:id,name', 'shopItem:id,name'])
                ->orderBy('created_at')
                ->get();
            
            return response()->json([
                'success' => true,
                'shop' => $shop,
                'statistics' => $statistics,
                'recent_orders' => $recentOrders,
                'pending_orders' => $pendingOrders
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all shops (including inactive/unverified)
     */
    public function adminIndex()
    {
        $this->checkAdminAccess();
        
        try {
            $shops = Shop::with(['owner:id,name,email'])
                ->withCount(['activeItems', 'followers', 'reviews'])
                ->orderByDesc('created_at')
                ->paginate(20);
            
            return response()->json([
                'success' => true,
                'shops' => $shops
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shops',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Verify/unverify a shop
     */
    public function toggleVerification(Shop $shop)
    {
        $this->checkAdminAccess();
        
        try {
            $shop->update(['is_verified' => !$shop->is_verified]);
            
            return response()->json([
                'success' => true,
                'message' => $shop->is_verified ? 'Shop verified' : 'Shop unverified',
                'shop' => $shop->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status'
            ], 500);
        }
    }

    /**
     * Admin: Toggle shop active status
     */
    public function toggleActive(Shop $shop)
    {
        $this->checkAdminAccess();
        
        try {
            $shop->update(['is_active' => !$shop->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => $shop->is_active ? 'Shop activated' : 'Shop deactivated',
                'shop' => $shop->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shop status'
            ], 500);
        }
    }

    /**
     * Check if user is admin
     */
    private function checkAdminAccess()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
    }
}