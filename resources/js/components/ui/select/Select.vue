<script setup lang="ts">
import { provide, ref, computed } from 'vue'

interface Props {
  modelValue?: string
  value?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:value': [value: string]
}>()

const isOpen = ref(false)
const selectedValue = ref(props.modelValue || props.value || '')

const updateValue = (value: string) => {
  selectedValue.value = value
  emit('update:modelValue', value)
  emit('update:value', value)
  isOpen.value = false
}

provide('select', {
  isOpen,
  selectedValue,
  updateValue
})
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template> 