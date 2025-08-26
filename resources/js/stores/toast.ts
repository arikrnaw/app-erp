import { ref } from 'vue';

export interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message?: string;
    duration?: number;
    autoClose?: boolean;
}

// Global toast state
const toasts = ref<Toast[]>([]);
let nextId = 1;

export const useToastStore = () => {
    const addToast = (toast: Omit<Toast, 'id'>) => {
        const id = `toast-${nextId++}`;
        const newToast: Toast = {
            id,
            type: toast.type || 'info',
            title: toast.title,
            message: toast.message,
            duration: toast.duration ?? 5000,
            autoClose: toast.autoClose ?? true,
        };

        toasts.value.push(newToast);

        // Auto remove toast after duration
        if (newToast.autoClose && newToast.duration && newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }

        return id;
    };

    const removeToast = (id: string) => {
        const index = toasts.value.findIndex((toast) => toast.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const clearToasts = () => {
        toasts.value = [];
    };

    // Convenience methods for different toast types
    const success = (title: string, message?: string, options?: Partial<Toast>) => {
        return addToast({ type: 'success', title, message, ...options });
    };

    const error = (title: string, message?: string, options?: Partial<Toast>) => {
        return addToast({ type: 'error', title, message, ...options });
    };

    const warning = (title: string, message?: string, options?: Partial<Toast>) => {
        return addToast({ type: 'warning', title, message, ...options });
    };

    const info = (title: string, message?: string, options?: Partial<Toast>) => {
        return addToast({ type: 'info', title, message, ...options });
    };

    return {
        toasts,
        addToast,
        removeToast,
        clearToasts,
        success,
        error,
        warning,
        info,
    };
};
