import axios from 'axios';

export function useApi() {
    const api = axios.create({
        baseURL: '/',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
    });

    // Add request interceptor to include CSRF token
    api.interceptors.request.use((config) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        return config;
    });

    // Add response interceptor for error handling
    api.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response?.status === 401) {
                // Redirect to login if unauthorized
                window.location.href = '/login';
            }
            return Promise.reject(error);
        },
    );

    return api;
}
