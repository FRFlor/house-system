<script setup lang="ts">
import { inject, computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()

const select = inject('select') as any

const contentClasses = computed(() => {
  return cn(
    'absolute top-full left-0 z-50 w-full mt-1 bg-popover text-popover-foreground shadow-md border rounded-md p-1',
    props.class
  )
})
</script>

<template>
  <div v-if="select.isOpen.value" :class="contentClasses">
    <slot />
  </div>
</template> 