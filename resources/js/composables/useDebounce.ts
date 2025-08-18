import { ref } from 'vue'

export function useDebounce<T extends (...args: any[]) => any>(
    fn: T,
    delay: number
): (...args: Parameters<T>) => void {
    const timeoutId = ref<NodeJS.Timeout | null>(null)

    return (...args: Parameters<T>) => {
        if (timeoutId.value) {
            clearTimeout(timeoutId.value)
        }

        timeoutId.value = setTimeout(() => {
            fn(...args)
        }, delay)
    }
}
