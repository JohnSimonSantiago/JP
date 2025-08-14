import Signup from "@/pages/Signup.vue";
import Dashboard from "./pages/Dashboard.vue";
import Leaderboards from "./pages/Leaderboards.vue";
import Profile from "./pages/Profile.vue";
import Trade from "./pages/Trade.vue";
import LoginNew from "./pages/LoginNew.vue";
import Bet from "./pages/Bet.vue";
import ShopsIndex from "./pages/ShopsIndex.vue"; // Lists all shops
import ShopView from "./pages/ShopView.vue"; // Individual shop with items
import MyShop from "./pages/MyShop.vue"; // Shop owner dashboard (optional)

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

    // Optional: Shop owner dashboard
    {
        path: "/my-shop",
        name: "my-shop",
        component: MyShop, // Shop owner management dashboard
        meta: { requiresAuth: true, role: "shop_owner" },
    },
];
