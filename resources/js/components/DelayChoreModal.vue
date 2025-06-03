<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Clock, X } from 'lucide-vue-next';

interface Chore {
    id: number;
    name: string;
    category: {
        id: number;
        name: string;
        color: string;
    };
    description?: string;
    frequency_type: string;
    frequency_value: number;
    next_due_at: string;
}

interface Props {
    chore: Chore | null;
    open: boolean;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isSubmitting = ref(false);
const selectedDelay = ref<string>('');

const delayOptions = [
    { value: '1 day', label: '1 day' },
    { value: '3 days', label: '3 days' },
    { value: '1 week', label: '1 week' },
    { value: '1 month', label: '1 month' },
];

const isOpen = computed({
    get: () => props.open,
    set: (value) => {
        if (!value) {
            closeModal();
        }
    }
});

const calculateDelayedDate = (delay: string): string => {
    const today = new Date();
    let delayedDate = new Date(today);
    
    switch (delay) {
        case '1 day':
            delayedDate.setDate(today.getDate() + 1);
            break;
        case '3 days':
            delayedDate.setDate(today.getDate() + 3);
            break;
        case '1 week':
            delayedDate.setDate(today.getDate() + 7);
            break;
        case '1 month':
            delayedDate.setMonth(today.getMonth() + 1);
            break;
    }
    
    return delayedDate.toISOString().split('T')[0];
};

const delayChore = async () => {
    if (!props.chore || !selectedDelay.value || isSubmitting.value) return;

    isSubmitting.value = true;

    try {
        const delayedDate = calculateDelayedDate(selectedDelay.value);
        
        await router.put(`/chores/${props.chore.id}`, {
            name: props.chore.name,
            description: props.chore.description,
            category_id: props.chore.category.id,
            frequency_type: props.chore.frequency_type,
            frequency_value: props.chore.frequency_value,
            next_due_at: delayedDate,
        });

        selectedDelay.value = '';
        emit('close');
    } catch (error) {
        console.error('Error delaying chore:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (isSubmitting.value) return;
    selectedDelay.value = '';
    emit('close');
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogDescription class="sr-only">
                Delay Chore
            </DialogDescription>
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <Clock class="h-5 w-5 text-orange-600" />
                    <p>Delay Chore</p>
                </DialogTitle>
            </DialogHeader>

            <div v-if="chore" class="space-y-4">
                <!-- Chore Info -->
                <div class="rounded-lg border p-3 bg-muted/50">
                    <h3 class="font-medium">{{ chore.name }}</h3>
                    <div class="mt-1 flex items-center space-x-2 text-sm text-muted-foreground">
                        <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: chore.category.color }"></div>
                        <span>{{ chore.category.name }}</span>
                    </div>
                </div>

                <!-- Delay Options -->
                <div class="space-y-3">
                    <p class="text-sm font-medium">Delay by:</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="option in delayOptions"
                            :key="option.value"
                            @click="selectedDelay = option.value"
                            :disabled="isSubmitting"
                            :class="[
                                'flex items-center justify-center rounded-lg border p-3 text-sm font-medium transition-colors',
                                selectedDelay === option.value
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-input bg-background hover:bg-accent hover:text-accent-foreground',
                                isSubmitting && 'opacity-50 cursor-not-allowed'
                            ]"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <!-- Actions -->
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="closeModal" :disabled="isSubmitting">
                        <X class="mr-1 h-4 w-4" />
                        Cancel
                    </Button>
                    <Button 
                        @click="delayChore" 
                        :disabled="isSubmitting || !selectedDelay"
                        class="bg-orange-600 hover:bg-orange-700"
                    >
                        <Clock class="mr-1 h-4 w-4" />
                        {{ isSubmitting ? 'Delaying...' : 'Delay' }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template> 