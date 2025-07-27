import "./bootstrap";
import { createApp } from "vue/dist/vue.esm-bundler";
import { createRouter, createWebHistory } from "vue-router";
import { routes } from "./routes";
import Layout1 from "@/Layout/Layout1.vue";
import VCalendar from "v-calendar";
import "v-calendar/style.css";
import { setupCalendar, Calendar, DatePicker } from "v-calendar";
import "v-calendar/style.css";
import PrimeVue from "primevue/config";
import "primevue/resources/themes/aura-light-green/theme.css";
import ToastService from "primevue/toastservice";
import "primeicons/primeicons.css";
import { createPinia } from "pinia";

const pinia = createPinia();
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
}); 
const app = createApp({});

app.use(pinia);
app.use(PrimeVue);
app.use(setupCalendar, {});
app.use(router);
app.component("Layout", Layout1);
app.mount("#app");

app.component("VCalendar", Calendar);
app.component("VDatePicker", DatePicker);
app.use(ToastService);
