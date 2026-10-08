import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import AboutView from '../views/AboutView.vue';
import ContactView from '../views/ContactView.vue';
import HomeView from '../views/HomeView.vue';
import ServicesView from '../views/ServicesView.vue';
import PolicyCreateView from '../views/PolicyCreateView.vue';
import PolicyDetailView from '../views/PolicyDetailView.vue';
import PolicyEditView from '../views/PolicyEditView.vue';
import PolicyListView from '../views/PolicyListView.vue';

const routes: RouteRecordRaw[] = [
    { path: '/', name: 'home', component: HomeView, meta: { title: 'Home' } },
    { path: '/about', name: 'about', component: AboutView, meta: { title: 'About' } },
    { path: '/services', name: 'services', component: ServicesView, meta: { title: 'Services' } },
    { path: '/contact', name: 'contact', component: ContactView, meta: { title: 'Contact' } },
    { path: '/policies', name: 'policies', component: PolicyListView, meta: { title: '保单列表' } },
    { path: '/policies/create', name: 'policy-create', component: PolicyCreateView, meta: { title: '新建保单' } },
    { path: '/policies/:id', name: 'policy-detail', component: PolicyDetailView, meta: { title: '保单详情' } },
    { path: '/policies/:id/edit', name: 'policy-edit', component: PolicyEditView, meta: { title: '编辑保单' } },
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
