import './style.css';
import { createApp } from 'vue';
import App from './App.vue';
import { createRouter, createWebHashHistory } from 'vue-router';

import HomePage from './pages/HomePage.vue';
import RegionSelectPage from './pages/RegionSelectPage.vue';
import VacanciesPage from './pages/VacanciesPage.vue';

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        { path: '/', name: 'home', component: HomePage },
        { path: '/search', name: 'search', component: RegionSelectPage },
        { path: '/vacancies', name: 'vacancies', component: VacanciesPage },
    ],
});

createApp(App).use(router).mount('#app');
