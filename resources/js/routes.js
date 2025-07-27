import Reservations from "@/pages/Reservations.vue";
import Signup from "@/pages/Signup.vue";
import Dashboard from "./pages/Dashboard.vue";
import LoginNew from "./pages/LoginNew.vue";

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
        path: "/reservations",
        name: "reservations",
        component: Reservations,
    },
    ,
];
