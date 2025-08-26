import { useToast } from '@/composables/useToast';

declare global {
    interface Window {
        toast: ReturnType<typeof useToast>;
    }
}

export default {
    install() {
        const toast = useToast();

        // Make toast available globally
        if (typeof window !== 'undefined') {
            window.toast = toast;
        }
    },
};
