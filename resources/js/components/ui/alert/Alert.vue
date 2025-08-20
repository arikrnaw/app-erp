<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { AlertCircle, AlertTriangle, Info, CheckCircle } from 'lucide-vue-next';

interface Props {
    variant?: 'default' | 'destructive' | 'warning' | 'info' | 'success';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const variantClasses = computed(() => {
    const variants = {
        default: 'border-border bg-background text-foreground',
        destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
        warning: 'border-yellow-500/50 text-yellow-600 dark:text-yellow-400 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400',
        info: 'border-blue-500/50 text-blue-600 dark:text-blue-400 [&>svg]:text-blue-600 dark:[&>svg]:text-blue-400',
        success: 'border-green-500/50 text-green-600 dark:text-green-400 [&>svg]:text-green-600 dark:[&>svg]:text-green-400',
    };
    return variants[props.variant];
});

const icon = computed(() => {
    const icons = {
        default: Info,
        destructive: AlertCircle,
        warning: AlertTriangle,
        info: Info,
        success: CheckCircle,
    };
    return icons[props.variant];
});
</script>

<template>
    <div :class="cn(
        'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
        variantClasses
    )">
        <component :is="icon" class="h-4 w-4" />
        <slot />
    </div>
</template>
