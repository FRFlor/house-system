<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Plus, Edit, Calendar, Clock } from 'lucide-vue-next'

interface Category {
  id: number
  name: string
  color: string
}

interface Chore {
  id: number
  name: string
  description: string | null
  category: Category
  frequency_type: string
  frequency_value: number
  next_due_at: string | null
  last_completed_at: string | null
  instruction_file_path: string | null
}

interface Props {
  chores: Chore[]
}

const props = defineProps<Props>()

const formatFrequency = (type: string, value: number) => {
  if (type === 'one_off') return 'One-time task'
  const unit = type === 'weeks' ? 'week' : type === 'months' ? 'month' : 'year'
  return value === 1 ? `Every ${unit}` : `Every ${value} ${unit}s`
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Not scheduled'
  return new Date(dateString).toLocaleDateString()
}

const getStatusColor = (nextDueAt: string | null) => {
  if (!nextDueAt) return 'bg-gray-500'
  
  const dueDate = new Date(nextDueAt)
  const today = new Date()
  const diffTime = dueDate.getTime() - today.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'bg-red-500' // Overdue
  if (diffDays === 0) return 'bg-orange-500' // Due today
  if (diffDays <= 3) return 'bg-yellow-500' // Due soon
  return 'bg-green-500' // Future
}

const getStatusText = (nextDueAt: string | null) => {
  if (!nextDueAt) return 'Completed'
  
  const dueDate = new Date(nextDueAt)
  const today = new Date()
  const diffTime = dueDate.getTime() - today.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return `${Math.abs(diffDays)} days overdue`
  if (diffDays === 0) return 'Due today'
  if (diffDays === 1) return 'Due tomorrow'
  return `Due in ${diffDays} days`
}

const editChore = (choreId: number) => {
  router.visit(`/chores/${choreId}/edit`)
}

const createChore = () => {
  router.visit('/chores/create')
}
</script>

<template>
  <Head title="All Chores" />
  
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <Calendar class="h-6 w-6" />
          <h1 class="text-2xl font-bold">All Chores</h1>
        </div>
        <Button @click="createChore" class="flex items-center space-x-2">
          <Plus class="h-4 w-4" />
          <span>Create Chore</span>
        </Button>
      </div>
      
      <!-- Chores Grid -->
      <div v-if="chores.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="chore in chores" :key="chore.id" class="hover:shadow-md transition-shadow">
          <CardHeader class="pb-3">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <CardTitle class="text-lg">{{ chore.name }}</CardTitle>
                <CardDescription v-if="chore.description" class="mt-1">
                  {{ chore.description }}
                </CardDescription>
              </div>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="editChore(chore.id)"
                class="ml-2"
              >
                <Edit class="h-4 w-4" />
              </Button>
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <!-- Category -->
            <div class="flex items-center space-x-2">
              <div 
                class="w-3 h-3 rounded-full" 
                :style="{ backgroundColor: chore.category.color }"
              ></div>
              <span class="text-sm text-muted-foreground">{{ chore.category.name }}</span>
            </div>
            
            <!-- Frequency -->
            <div class="flex items-center space-x-2">
              <Clock class="h-4 w-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">
                {{ formatFrequency(chore.frequency_type, chore.frequency_value) }}
              </span>
            </div>
            
            <!-- Status -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <Badge 
                  :class="getStatusColor(chore.next_due_at)"
                  class="text-white"
                >
                  {{ getStatusText(chore.next_due_at) }}
                </Badge>
              </div>
              <span class="text-sm text-muted-foreground">
                {{ formatDate(chore.next_due_at) }}
              </span>
            </div>
            
            <!-- Last Completed -->
            <div v-if="chore.last_completed_at" class="text-xs text-muted-foreground">
              Last completed: {{ formatDate(chore.last_completed_at) }}
            </div>
          </CardContent>
        </Card>
      </div>
      
      <!-- Empty State -->
      <Card v-else class="text-center py-12">
        <CardContent>
          <Calendar class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
          <CardTitle class="mb-2">No chores yet</CardTitle>
          <CardDescription class="mb-4">
            Get started by creating your first chore to track and manage your household tasks.
          </CardDescription>
          <Button @click="createChore" class="flex items-center space-x-2 mx-auto">
            <Plus class="h-4 w-4" />
            <span>Create Your First Chore</span>
          </Button>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 