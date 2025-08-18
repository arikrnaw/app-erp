import { defineComponent, h, ref, provide, inject, computed, type Ref } from 'vue'
import { cn } from '@/lib/utils'

interface TabsContextType {
  activeTab: Ref<string>
  setActiveTab: (value: string) => void
}

const TabsContext = Symbol('TabsContext') as symbol

export const Tabs = defineComponent({
  name: 'Tabs',
  props: {
    className: {
      type: String,
      default: '',
    },
    defaultValue: {
      type: String,
      default: '',
    },
    value: {
      type: String,
      default: '',
    },
  },
  emits: ['update:value'],
  setup(props, { slots, attrs, emit }) {
    const activeTab = ref(props.value || props.defaultValue || '')
    
    const setActiveTab = (value: string) => {
      activeTab.value = value
      emit('update:value', value)
    }

    provide(TabsContext, {
      activeTab,
      setActiveTab,
    } as TabsContextType)

    return () => h('div', {
      'data-slot': 'tabs',
      class: cn('flex flex-col gap-2', props.className),
      ...attrs,
    }, slots.default?.())
  },
})

export const TabsList = defineComponent({
  name: 'TabsList',
  props: {
    className: {
      type: String,
      default: '',
    },
  },
  setup(props, { slots, attrs }) {
    return () => h('div', {
      'data-slot': 'tabs-list',
      class: cn(
        'bg-muted text-muted-foreground inline-flex h-9 w-fit items-center justify-center rounded-lg p-[3px]',
        props.className
      ),
      role: 'tablist',
      ...attrs,
    }, slots.default?.())
  },
})

export const TabsTrigger = defineComponent({
  name: 'TabsTrigger',
  props: {
    className: {
      type: String,
      default: '',
    },
    value: {
      type: String,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  setup(props, { slots, attrs }) {
    const tabsContext = inject(TabsContext) as TabsContextType | undefined
    
    if (!tabsContext) {
      throw new Error('TabsTrigger must be used within Tabs')
    }

    const isActive = computed(() => tabsContext.activeTab.value === props.value)
    
    const handleClick = () => {
      if (!props.disabled) {
        tabsContext.setActiveTab(props.value)
      }
    }

    return () => h('button', {
      'data-slot': 'tabs-trigger',
      'data-state': isActive.value ? 'active' : 'inactive',
      class: cn(
        'data-[state=active]:bg-background dark:data-[state=active]:text-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:outline-ring dark:data-[state=active]:border-input dark:data-[state=active]:bg-input/30 text-foreground dark:text-muted-foreground inline-flex h-[calc(100%-1px)] flex-1 items-center justify-center gap-1.5 rounded-md border border-transparent px-2 py-1 text-sm font-medium whitespace-nowrap transition-[color,box-shadow] focus-visible:ring-[3px] focus-visible:outline-1 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:shadow-sm [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*="size-"])]:size-4',
        props.className
      ),
      role: 'tab',
      'aria-selected': isActive.value,
      disabled: props.disabled,
      onClick: handleClick,
      ...attrs,
    }, slots.default?.())
  },
})

export const TabsContent = defineComponent({
  name: 'TabsContent',
  props: {
    className: {
      type: String,
      default: '',
    },
    value: {
      type: String,
      required: true,
    },
  },
  setup(props, { slots, attrs }) {
    const tabsContext = inject(TabsContext) as TabsContextType | undefined
    
    if (!tabsContext) {
      throw new Error('TabsContent must be used within Tabs')
    }

    const isActive = computed(() => tabsContext.activeTab.value === props.value)

    return () => isActive.value ? h('div', {
      'data-slot': 'tabs-content',
      'data-state': 'active',
      class: cn('flex-1 outline-none', props.className),
      role: 'tabpanel',
      ...attrs,
    }, slots.default?.()) : null
  },
})
