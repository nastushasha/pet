import axios from 'axios';

const raw = import.meta.env.VITE_API_BASE_URL;
const baseURL = typeof raw === 'string' ? raw.replace(/\/$/, '') : '';

export const api = axios.create({
    baseURL,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});
