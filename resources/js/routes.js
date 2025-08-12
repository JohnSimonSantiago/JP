import Signup from "@/pages/Signup.vue";
import Dashboard from "./pages/Dashboard.vue";
import Leaderboards from "./pages/Leaderboards.vue";
import Profile from "./pages/Profile.vue";
import Trade from "./pages/Trade.vue";
import LoginNew from "./pages/LoginNew.vue";
import Bet from "./pages/Bet.vue";
import Shop from "./pages/Shop.vue";

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
    {
        path: "/shop",
        name: "shop",
        component: Shop,
    },
];
