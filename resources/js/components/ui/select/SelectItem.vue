<script setup lang="ts">
import { inject, computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  value: string
  class?: string
}

const props = defineProps<Props>()

const select = inject('select') as any

const itemClasses = computed(() => {
  return cn(
    'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
    props.class
  )
})

const handleClick = () => {
  select.updateValue(props.value)
}
</script>

<template>
  <div :class="itemClasses" @click="handleClick">
    <slot />
  </div>
</template> 