import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useAuth() {
    const loading = ref(false);

    const logout = async () => {
        loading.value = true;
        try {
            await router.post(route('logout'));
        } catch (error) {
            console.error('Logout failed:', error);
        } finally {
            loading.value = false;
        }
    };

    return {
        logout,
        loading,
    };
}
