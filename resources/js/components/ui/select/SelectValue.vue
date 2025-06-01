<script setup lang="ts">
import { inject, computed } from 'vue'

interface Props {
  placeholder?: string
  displayValue?: string
}

const props = defineProps<Props>()

const select = inject('select') as any

const displayText = computed(() => {
  if (props.displayValue) return props.displayValue
  return select.selectedValue.value || props.placeholder || 'Select...'
})
</script>

<template>
  <span>{{ displayText }}</span>
</template> 