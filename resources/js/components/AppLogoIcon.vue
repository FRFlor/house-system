<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img 
        src="/images/house.png" 
        alt="House System Logo" 
        :class="['w-full h-full object-contain', className]" 
        v-bind="$attrs"
    />
</template>
