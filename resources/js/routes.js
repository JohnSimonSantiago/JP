import Signup from "@/pages/Signup.vue";
import Dashboard from "./pages/Dashboard.vue";
import Leaderboards from "./pages/Leaderboards.vue";
import Profile from "./pages/Profile.vue";
import UserProfile from "./pages/UserProfile.vue"; // NEW: For viewing other users
import Trade from "./pages/Trade.vue";
import LoginNew from "./pages/LoginNew.vue";
import Bet from "./pages/Bet.vue";
import ShopsIndex from "./pages/ShopsIndex.vue"; // Lists all shops
import ShopView from "./pages/ShopView.vue"; // Individual shop with items
import MyShop from "./pages/MyShop.vue"; // Shop owner dashboard
import AdminPointPricing from "./pages/AdminPointPricing.vue"; // Admin point pricing management
import PointShop from "./pages/PointShop.vue";
import AdminUserApproval from "./pages/AdminUserApproval.vue";

const requiresShopOwner = (to, from, next) => {
    // Get user from localStorage or your auth store
    const user = JSON.parse(localStorage.getItem("user") || "null");

    if (!user) {
        // Not logged in, redirect to login
        next("/");
        return;
    }

    if (user.role !== "shop_owner" && user.role !== "admin") {
        // Not a shop owner, redirect to dashboard
        next("/dashboard");
        return;
    }

    // User is shop owner or admin, allow access
    next();
};

const requiresAdmin = (to, from, next) => {
    // Get user from localStorage or your auth store
    const user = JSON.parse(localStorage.getItem("user") || "null");

    if (!user) {
        // Not logged in, redirect to login
        next("/");
        return;
    }

    if (user.role !== "admin") {
        // Not an admin, redirect to dashboard
        next("/dashboard");
        return;
    }

    // User is admin, allow access
    next();
};

export const routes = [
    {
        path: "/",
        name: "loginnew",
        component: LoginNew,
    },
    {
        path: "/signup",
        name: "signup",
        component: Signup,
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
    },
    {
        path: "/leaderboards",
        name: "leaderboards",
        component: Leaderboards,
    },
    {
        path: "/profile",
        name: "profile",
        component: Profile,
    },
    {
        path: "/profile/:userId",
        name: "user-profile",
        component: UserProfile,
        props: true, // Pass route params as props
    },
    {
        path: "/trade",
        name: "trade",
        component: Trade,
    },
    {
        path: "/bet",
        name: "bet",
        component: Bet,
    },

    // NEW MULTI-SHOP ROUTES
    {
        path: "/shops",
        name: "shops",
        component: ShopsIndex, // Shows all shops in grid/list view
    },
    {
        path: "/shop/:id",
        name: "shop-view",
        component: ShopView, // Shows individual shop with its items
        props: true, // Pass route params as props
    },

    // Shop owner dashboard
    {
        path: "/my-shop",
        name: "my-shop",
        component: MyShop,
        beforeEnter: requiresShopOwner, // Route protection
    },

    // Admin point pricing management
    {
        path: "/admin/point-pricing",
        name: "admin-point-pricing",
        component: AdminPointPricing,
        beforeEnter: requiresAdmin, // Admin only access
    },

    {
        path: "/point-shop",
        name: "PointShop",
        component: PointShop,
    },
    {
        path: "/admin/users",
        name: "admin-user-approval",
        component: AdminUserApproval,
        beforeEnter: requiresAdmin, // Admin only access
    },
];
