import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import AboutView from '../views/AboutView.vue';
import ContactView from '../views/ContactView.vue';
import HomeView from '../views/HomeView.vue';
import ServicesView from '../views/ServicesView.vue';

const routes: RouteRecordRaw[] = [
    { path: '/', name: 'home', component: HomeView, meta: { title: 'Home' } },
    { path: '/about', name: 'about', component: AboutView, meta: { title: 'About' } },
    { path: '/services', name: 'services', component: ServicesView, meta: { title: 'Services' } },
    { path: '/contact', name: 'contact', component: ContactView, meta: { title: 'Contact' } },
    { path: '/:pathMatch(.*)*', redirect: '/' },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.afterEach((route) => {
    document.title = `${String(route.meta.title ?? 'Home')} | PHP Test`;
});

export default router;
