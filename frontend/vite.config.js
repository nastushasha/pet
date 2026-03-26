import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const apiTarget = env.VITE_DEV_API_PROXY ?? 'http://127.0.0.1:8000';

    return {
        plugins: [
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            port: 5173,
            proxy: {
                '/areas': { target: apiTarget, changeOrigin: true },
                '/cities': { target: apiTarget, changeOrigin: true },
                '/vacancies': { target: apiTarget, changeOrigin: true },
                '/test': { target: apiTarget, changeOrigin: true },
            },
        },
    };
});
