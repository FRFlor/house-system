<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Edit, X, Plus, Trash2 } from 'lucide-vue-next';

interface ChoreCompletion {
    id: number;
    completed_at: string;
    notes?: string;
    expenses: Expense[];
    chore: {
        id: number;
        name: string;
        description?: string;
        category: {
            id: number;
            name: string;
            color: string;
        };
    };
}

interface Expense {
    id?: number;
    description: string;
    amount: number;
}

interface Props {
    completion: ChoreCompletion | null;
    open: boolean;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const notes = ref('');
const completedAt = ref('');
const expenses = ref<Expense[]>([]);
const isSubmitting = ref(false);

const isOpen = computed({
    get: () => props.open,
    set: (value) => {
        if (!value) {
            closeModal();
        }
    },
});

// Watch for completion changes to populate form
watch(() => props.completion, (completion) => {
    if (completion) {
        notes.value = completion.notes || '';
        // Convert UTC timestamp to local datetime-local format
        completedAt.value = convertUTCToLocalDateTime(completion.completed_at);
        expenses.value = completion.expenses.map(expense => ({
            description: expense.description,
            amount: expense.amount,
        }));
    }
}, { immediate: true });

// Helper function to convert UTC timestamp to local datetime-local format
const convertUTCToLocalDateTime = (utcTimestamp: string) => {
    // Create Date object from UTC timestamp
    const utcDate = new Date(utcTimestamp);
    // Convert to local time by adjusting for timezone offset
    const localTime = new Date(utcDate.getTime() - (utcDate.getTimezoneOffset() * 60000));
    // Format as YYYY-MM-DDTHH:mm for datetime-local input
    return localTime.toISOString().slice(0, 16);
};

const addExpense = () => {
    expenses.value = [...expenses.value, { description: '', amount: 0 }];
};

const removeExpense = (index: number) => {
    expenses.value = expenses.value.toSpliced(index, 1);
};

const updateCompletion = async () => {
    if (!props.completion || isSubmitting.value) return;

    isSubmitting.value = true;

    try {
        const validExpenses = expenses.value
            .filter((expense) => expense.description.trim() && expense.amount > 0)
            .map((expense) => ({
                description: expense.description.trim(),
                amount: expense.amount
            }));

        // Convert local datetime to UTC for server
        let completedAtUTC = null;
        if (completedAt.value) {
            // Create a Date object from the datetime-local value (this will be in local time)
            const localDate = new Date(completedAt.value);
            // Convert to UTC ISO string for the server
            completedAtUTC = localDate.toISOString();
        }

        await router.put(`/completions/${props.completion.id}`, {
            notes: notes.value || null,
            completed_at: completedAtUTC,
            expenses: validExpenses.length > 0 ? validExpenses : null,
        });

        emit('close');
    } catch (error) {
        console.error('Error updating completion:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    if (isSubmitting.value) return;
    emit('close');
};

const totalExpenses = computed(() => {
    return expenses.value
        .filter((expense) => {
            return expense.amount;
        })
        .reduce((total, expense) => {
            const amount = expense.amount;
            return total + amount;
        }, 0);
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogDescription class="sr-only"> Edit Completion Details </DialogDescription>
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <Edit class="h-5 w-5 text-blue-600" />
                    <p>Edit Completion Details</p>
                </DialogTitle>
            </DialogHeader>

            <div v-if="completion" class="space-y-4">
                <!-- Chore Info -->
                <div class="bg-muted/50 rounded-lg border p-3">
                    <h3 class="font-medium">{{ completion.chore.name }}</h3>
                    <div class="text-muted-foreground mt-1 flex items-center space-x-2 text-sm">
                        <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: completion.chore.category.color }"></div>
                        <span>{{ completion.chore.category.name }}</span>
                    </div>
                </div>

                <!-- Completed At Field -->
                <div class="space-y-2">
                    <Label for="completed_at">Completed At</Label>
                    <Input
                        id="completed_at"
                        v-model="completedAt"
                        type="datetime-local"
                        :disabled="isSubmitting"
                    />
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
                        class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full resize-none rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                </div>

                <!-- Expenses Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label>Expenses (optional)</Label>
                        <Button @click="addExpense" variant="outline" size="sm" :disabled="isSubmitting">
                            <Plus class="mr-1 h-4 w-4" />
                            Add
                        </Button>
                    </div>

                    <div v-for="(expense, index) in expenses" :key="index" class="flex items-center space-x-2">
                        <div class="flex-1">
                            <Input
                                v-model="expense.description"
                                placeholder="Description..."
                                :disabled="isSubmitting"
                            />
                        </div>
                        <div class="w-24">
                            <Input
                                v-model.number="expense.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :disabled="isSubmitting"
                            />
                        </div>
                        <Button
                            @click="removeExpense(index)"
                            variant="outline"
                            size="sm"
                            :disabled="isSubmitting"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>

                    <div v-if="expenses.length > 0 && totalExpenses > 0" class="text-right text-sm text-muted-foreground">
                        Total: ${{ totalExpenses.toFixed(2) }}
                    </div>

                    <div v-if="expenses.length === 0" class="text-muted-foreground rounded-lg border border-dashed py-4 text-center text-sm">
                        No expenses added. Click "Add" to track costs for this chore.
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
                    <Button @click="updateCompletion" :disabled="isSubmitting">
                        <Edit class="mr-1 h-4 w-4" />
                        {{ isSubmitting ? 'Updating...' : 'Update' }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template> 