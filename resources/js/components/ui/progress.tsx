import { computed, defineComponent, h } from 'vue'
import { cn } from '@/lib/utils'

export const Progress = defineComponent({
  name: 'Progress',
  props: {
    className: {
      type: String,
      default: '',
    },
    value: {
      type: Number,
      default: 0,
    },
  },
  setup(props, { attrs }) {
    const progressStyle = computed(() => ({
      transform: `translateX(-${100 - (props.value || 0)}%)`,
    }))

    return () => h('div', {
      'data-slot': 'progress',
      class: cn(
        'bg-primary/20 relative h-2 w-full overflow-hidden rounded-full',
        props.className
      ),
      ...attrs,
    }, [
      h('div', {
        'data-slot': 'progress-indicator',
        class: 'bg-primary h-full w-full flex-1 transition-all',
        style: progressStyle.value,
      }),
    ])
  },
})
