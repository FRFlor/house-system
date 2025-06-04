<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { CheckCircle2, X, Plus, Trash2 } from 'lucide-vue-next';

interface Chore {
    id: number;
    name: string;
    category: {
        name: string;
        color: string;
    };
    description?: string;
}

interface Expense {
    description: string;
    amount: number;
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

const addExpense = () => {
    expenses.value = [...expenses.value, { description: '', amount: 0 }];
};

const removeExpense = (index: number) => {
    expenses.value = expenses.value.toSpliced(index, 1);
};

const completeChore = async () => {
    if (!props.chore || isSubmitting.value) return;

    isSubmitting.value = true;

    try {
        const validExpenses = expenses.value
            .filter((expense) => expense.description.trim() && expense.amount > 0)
            .map((expense) => ({
                description: expense.description.trim(),
                amount: expense.amount
            }));

        await router.post(`/chores/${props.chore.id}/complete`, {
            notes: notes.value || null,
            expenses: validExpenses.length > 0 ? validExpenses : null,
        });

        notes.value = '';
        expenses.value = [];
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
    expenses.value = [];
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
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogDescription class="sr-only"> Complete Chore </DialogDescription>
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <CheckCircle2 class="h-5 w-5 text-green-600" />
                    <p>Complete Chore</p>
                </DialogTitle>
            </DialogHeader>

            <div v-if="chore" class="space-y-4">
                <!-- Chore Info -->
                <div class="bg-muted/50 rounded-lg border p-3">
                    <h3 class="font-medium">{{ chore.name }}</h3>
                    <div class="text-muted-foreground mt-1 flex items-center space-x-2 text-sm">
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
                        class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full resize-none rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                </div>

                <!-- Expenses Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label>Expenses (optional)</Label>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addExpense"
                            :disabled="isSubmitting"
                            class="flex items-center space-x-1"
                        >
                            <Plus class="h-4 w-4" />
                            <span>Add</span>
                        </Button>
                    </div>

                    <!-- Expense Items -->
                    <div v-if="expenses.length > 0" class="space-y-3">
                        <div v-for="(expense, index) in expenses" :key="index" class="flex items-end space-x-2">
                            <div class="flex-1">
                                <Label :for="`item-${index}`" class="text-sm">Item</Label>
                                <Input
                                    :id="`item-${index}`"
                                    v-model="expense.description"
                                    placeholder="e.g., Fridge Cleaner"
                                    :disabled="isSubmitting"
                                    class="mt-1"
                                />
                            </div>
                            <div class="w-32">
                                <Label :for="`amount-${index}`" class="text-sm">Amount (CAD)</Label>
                                <Input
                                    :id="`amount-${index}`"
                                    v-model="expense.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    :disabled="isSubmitting"
                                    class="mt-1"
                                />
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="removeExpense(index)"
                                :disabled="isSubmitting"
                                class="flex-shrink-0"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Total -->
                        <div v-if="totalExpenses > 0" class="flex justify-end">
                            <div class="text-sm font-medium">Total: ${{ totalExpenses.toFixed(2) }} CAD</div>
                        </div>
                    </div>

                    <!-- Empty State -->
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
                    <Button @click="completeChore" :disabled="isSubmitting">
                        <CheckCircle2 class="mr-1 h-4 w-4" />
                        {{ isSubmitting ? 'Completing...' : 'Complete' }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
