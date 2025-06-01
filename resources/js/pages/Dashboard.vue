<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    CalendarDays, 
    Clock, 
    AlertTriangle, 
    CheckCircle2, 
    Calendar,
    TrendingUp,
    Activity
} from 'lucide-vue-next';

interface Chore {
    id: number;
    name: string;
    next_due_at: string;
    frequency_type: string;
    frequency_value: number;
    category: {
        id: number;
        name: string;
        color: string;
    };
}

interface ChoreCompletion {
    id: number;
    completed_at: string;
    notes?: string;
    chore: {
        id: number;
        name: string;
        category: {
            id: number;
            name: string;
            color: string;
        };
    };
}

interface Stats {
    total_active_chores: number;
    overdue_count: number;
    due_today_count: number;
    upcoming_count: number;
}

interface Props {
    overdue_chores: Chore[];
    due_today: Chore[];
    upcoming_chores: Chore[];
    recent_completions: ChoreCompletion[];
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const completeChore = (chore: Chore) => {
    router.post(`/chores/${chore.id}/complete`, {}, {
        preserveScroll: true,
    });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = date.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    if (diffDays === -1) return 'Yesterday';
    if (diffDays < 0) return `${Math.abs(diffDays)} days ago`;
    if (diffDays <= 7) return `In ${diffDays} days`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
};

const getFrequencyLabel = (type: string, value: number) => {
    if (type === 'one_off') return 'One-time';
    const unit = type === 'weeks' ? 'week' : type === 'months' ? 'month' : 'year';
    return value === 1 ? `Every ${unit}` : `Every ${value} ${unit}s`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Stats Overview -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                            <Activity class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active</p>
                            <p class="text-2xl font-bold">{{ stats.total_active_chores }}</p>
                        </div>
                    </div>
                </Card>

                <Card class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/20">
                            <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Overdue</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.overdue_count }}</p>
                        </div>
                    </div>
                </Card>

                <Card class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/20">
                            <Clock class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Due Today</p>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.due_today_count }}</p>
                        </div>
                    </div>
                </Card>

                <Card class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/20">
                            <TrendingUp class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Upcoming</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.upcoming_count }}</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column - Chore Lists -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Overdue Chores -->
                    <Card v-if="overdue_chores.length > 0" class="p-6">
                        <div class="mb-4 flex items-center space-x-2">
                            <AlertTriangle class="h-5 w-5 text-red-500" />
                            <h2 class="text-lg font-semibold text-red-600 dark:text-red-400">Overdue Tasks</h2>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="chore in overdue_chores" 
                                :key="chore.id"
                                class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/10"
                            >
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ chore.name }}</h3>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                                        <span class="flex items-center space-x-1">
                                            <div 
                                                class="h-2 w-2 rounded-full" 
                                                :style="{ backgroundColor: chore.category.color }"
                                            ></div>
                                            <span>{{ chore.category.name }}</span>
                                        </span>
                                        <span>{{ formatDate(chore.next_due_at) }}</span>
                                        <span>{{ getFrequencyLabel(chore.frequency_type, chore.frequency_value) }}</span>
                                    </div>
                                </div>
                                <Button 
                                    @click="completeChore(chore)"
                                    size="sm"
                                    class="ml-4"
                                >
                                    <CheckCircle2 class="mr-1 h-4 w-4" />
                                    Complete
                                </Button>
                            </div>
                        </div>
                    </Card>

                    <!-- Due Today -->
                    <Card v-if="due_today.length > 0" class="p-6">
                        <div class="mb-4 flex items-center space-x-2">
                            <Clock class="h-5 w-5 text-orange-500" />
                            <h2 class="text-lg font-semibold text-orange-600 dark:text-orange-400">Due Today</h2>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="chore in due_today" 
                                :key="chore.id"
                                class="flex items-center justify-between rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-900/10"
                            >
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ chore.name }}</h3>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                                        <span class="flex items-center space-x-1">
                                            <div 
                                                class="h-2 w-2 rounded-full" 
                                                :style="{ backgroundColor: chore.category.color }"
                                            ></div>
                                            <span>{{ chore.category.name }}</span>
                                        </span>
                                        <span>{{ getFrequencyLabel(chore.frequency_type, chore.frequency_value) }}</span>
                                    </div>
                                </div>
                                <Button 
                                    @click="completeChore(chore)"
                                    size="sm"
                                    variant="default"
                                    class="ml-4"
                                >
                                    <CheckCircle2 class="mr-1 h-4 w-4" />
                                    Complete
                                </Button>
                            </div>
                        </div>
                    </Card>

                    <!-- Upcoming Chores -->
                    <Card v-if="upcoming_chores.length > 0" class="p-6">
                        <div class="mb-4 flex items-center space-x-2">
                            <CalendarDays class="h-5 w-5 text-blue-500" />
                            <h2 class="text-lg font-semibold">Upcoming Tasks</h2>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="chore in upcoming_chores" 
                                :key="chore.id"
                                class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ chore.name }}</h3>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                                        <span class="flex items-center space-x-1">
                                            <div 
                                                class="h-2 w-2 rounded-full" 
                                                :style="{ backgroundColor: chore.category.color }"
                                            ></div>
                                            <span>{{ chore.category.name }}</span>
                                        </span>
                                        <span>{{ formatDate(chore.next_due_at) }}</span>
                                        <span>{{ getFrequencyLabel(chore.frequency_type, chore.frequency_value) }}</span>
                                    </div>
                                </div>
                                <Button 
                                    @click="completeChore(chore)"
                                    size="sm"
                                    variant="outline"
                                    class="ml-4"
                                >
                                    <CheckCircle2 class="mr-1 h-4 w-4" />
                                    Complete
                                </Button>
                            </div>
                        </div>
                    </Card>

                    <!-- Empty State -->
                    <Card v-if="overdue_chores.length === 0 && due_today.length === 0 && upcoming_chores.length === 0" class="p-8 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                            <CheckCircle2 class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">All caught up!</h3>
                        <p class="text-muted-foreground">No chores are due right now. Great job staying on top of things!</p>
                    </Card>
                </div>

                <!-- Right Column - Recent Activity -->
                <div class="space-y-6">
                    <Card v-if="recent_completions.length > 0" class="p-6">
                        <div class="mb-4 flex items-center space-x-2">
                            <CheckCircle2 class="h-5 w-5 text-green-500" />
                            <h2 class="text-lg font-semibold">Recent Activity</h2>
                        </div>
                        <div class="space-y-4">
                            <div 
                                v-for="completion in recent_completions" 
                                :key="completion.id"
                                class="border-b border-border pb-4 last:border-b-0 last:pb-0"
                            >
                                <div class="flex items-start space-x-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                        <CheckCircle2 class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate">{{ completion.chore.name }}</p>
                                        <div class="mt-1 flex items-center space-x-2 text-sm text-muted-foreground">
                                            <div 
                                                class="h-2 w-2 rounded-full" 
                                                :style="{ backgroundColor: completion.chore.category.color }"
                                            ></div>
                                            <span>{{ completion.chore.category.name }}</span>
                                        </div>
                                        <p v-if="completion.notes" class="mt-1 text-sm text-muted-foreground">
                                            "{{ completion.notes }}"
                                        </p>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            {{ formatDate(completion.completed_at) }} at {{ formatTime(completion.completed_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Quick Stats Card -->
                    <Card class="p-6">
                        <div class="mb-4 flex items-center space-x-2">
                            <Calendar class="h-5 w-5 text-blue-500" />
                            <h2 class="text-lg font-semibold">This Week</h2>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Completed</span>
                                <span class="font-medium">{{ recent_completions.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Due Soon</span>
                                <span class="font-medium">{{ stats.due_today_count + stats.upcoming_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Active</span>
                                <span class="font-medium">{{ stats.total_active_chores }}</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
