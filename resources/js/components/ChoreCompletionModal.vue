<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { CheckCircle2, X } from 'lucide-vue-next';

interface Chore {
    id: number;
    name: string;
    category: {
        name: string;
        color: string;
    };
    description?: string;
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

const notes = ref('');
const isSubmitting = ref(false);

const isOpen = computed({
    get: () => props.open,
    set: (value) => {
        if (!value) {
            closeModal();
        }
    }
});

const completeChore = async () => {
    if (!props.chore || isSubmitting.value) return;

    isSubmitting.value = true;

    try {
        await router.post(`/chores/${props.chore.id}/complete`, {
            notes: notes.value || null,
        });

        notes.value = '';
        emit('close');
    } catch (error) {
        console.error('Error completing chore:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (isSubmitting.value) return;
    notes.value = '';
    emit('close');
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogDescription class="sr-only">
                Complete Chore
            </DialogDescription>
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <CheckCircle2 class="h-5 w-5 text-green-600" />
                    <p>Complete Chore</p>
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

                <!-- Notes Field -->
                <div class="space-y-2">
                    <Label for="notes">Notes (optional)</Label>
                    <textarea 
                        id="notes" 
                        v-model="notes" 
                        placeholder="Add any notes about this completion..." 
                        rows="3"
                        :disabled="isSubmitting"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                    />
                </div>
            </div>

            <DialogFooter>
                <!-- Actions -->
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="closeModal" :disabled="isSubmitting">
                        <X class="mr-1 h-4 w-4" />
                        Cancel
                    </Button>
                    <Button @click="completeChore" :disabled="isSubmitting">
                        <CheckCircle2 class="mr-1 h-4 w-4" />
                        {{ isSubmitting ? 'Completing...' : 'Complete' }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>