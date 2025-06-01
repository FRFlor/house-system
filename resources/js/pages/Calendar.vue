<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ChoreCompletionModal from '@/components/ChoreCompletionModal.vue'
import { ref, computed } from 'vue'
import { ChevronLeft, ChevronRight, CheckCircle2, Clock, Calendar as CalendarIcon } from 'lucide-vue-next'

interface Category {
  id: number
  name: string
  color: string
}

interface Chore {
  id: number
  name: string
  description?: string
  next_due_at: string
  frequency_type: string
  frequency_value: number
  category: Category
}

interface ChoreCompletion {
  id: number
  completed_at: string
  notes?: string
  chore: {
    id: number
    name: string
    description?: string
    category: Category
  }
}

interface Props {
  chores: Chore[]
  completions: ChoreCompletion[]
  currentMonth: number
  currentYear: number
}

const props = defineProps<Props>()

const selectedChore = ref<Chore | null>(null)
const isModalOpen = ref(false)

const openCompletionModal = (chore: Chore) => {
  selectedChore.value = chore
  isModalOpen.value = true
}

const closeCompletionModal = () => {
  selectedChore.value = null
  isModalOpen.value = false
}

// Calendar logic
const currentDate = computed(() => new Date(props.currentYear, props.currentMonth - 1, 1))
const monthName = computed(() => currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }))

const daysInMonth = computed(() => {
  return new Date(props.currentYear, props.currentMonth, 0).getDate()
})

const firstDayOfWeek = computed(() => {
  return new Date(props.currentYear, props.currentMonth - 1, 1).getDay()
})

const calendarDays = computed(() => {
  const days = []
  
  // Add empty cells for days before the first day of the month
  for (let i = 0; i < firstDayOfWeek.value; i++) {
    days.push(null)
  }
  
  // Add all days of the month
  for (let day = 1; day <= daysInMonth.value; day++) {
    days.push(day)
  }
  
  return days
})

const getChoresForDate = (day: number) => {
  if (!day) return []
  
  const dateStr = new Date(props.currentYear, props.currentMonth - 1, day).toISOString().split('T')[0]
  
  return props.chores.filter(chore => {
    const choreDate = new Date(chore.next_due_at).toISOString().split('T')[0]
    return choreDate === dateStr
  })
}

const getCompletionsForDate = (day: number) => {
  if (!day) return []
  
  const dateStr = new Date(props.currentYear, props.currentMonth - 1, day).toISOString().split('T')[0]
  
  return props.completions.filter(completion => {
    const completionDate = new Date(completion.completed_at).toISOString().split('T')[0]
    return completionDate === dateStr
  })
}

const navigateMonth = (direction: 'prev' | 'next') => {
  let newMonth = props.currentMonth
  let newYear = props.currentYear
  
  if (direction === 'prev') {
    newMonth--
    if (newMonth < 1) {
      newMonth = 12
      newYear--
    }
  } else {
    newMonth++
    if (newMonth > 12) {
      newMonth = 1
      newYear++
    }
  }
  
  router.get('/calendar', { month: newMonth, year: newYear })
}

const isToday = (day: number) => {
  if (!day) return false
  
  const today = new Date()
  return (
    day === today.getDate() &&
    props.currentMonth === today.getMonth() + 1 &&
    props.currentYear === today.getFullYear()
  )
}
</script>

<template>
  <Head title="Calendar" />
  
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <CalendarIcon class="h-6 w-6" />
          <h1 class="text-2xl font-bold">Calendar</h1>
        </div>
        
        <div class="flex items-center space-x-2">
          <Button variant="outline" size="sm" @click="navigateMonth('prev')">
            <ChevronLeft class="h-4 w-4" />
          </Button>
          <span class="text-lg font-medium min-w-[200px] text-center">{{ monthName }}</span>
          <Button variant="outline" size="sm" @click="navigateMonth('next')">
            <ChevronRight class="h-4 w-4" />
          </Button>
        </div>
      </div>
      
      <!-- Calendar Grid -->
      <Card>
        <CardHeader>
          <CardTitle>{{ monthName }}</CardTitle>
        </CardHeader>
        <CardContent>
          <!-- Days of week header -->
          <div class="grid grid-cols-7 gap-1 mb-2">
            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                 :key="day" 
                 class="p-2 text-center text-sm font-medium text-muted-foreground">
              {{ day }}
            </div>
          </div>
          
          <!-- Calendar days -->
          <div class="grid grid-cols-7 gap-1">
            <div v-for="(day, index) in calendarDays" 
                 :key="index" 
                 class="min-h-[120px] border rounded-lg p-2"
                 :class="{
                   'bg-muted/50': !day,
                   'bg-blue-50 border-blue-200': day && isToday(day),
                   'hover:bg-muted/50': day && !isToday(day)
                 }">
              
              <!-- Day number -->
              <div v-if="day" class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium"
                      :class="{ 'text-blue-600 font-bold': isToday(day) }">
                  {{ day }}
                </span>
              </div>
              
              <!-- Chores for this day -->
              <div v-if="day" class="space-y-1">
                <!-- Pending chores -->
                <div v-for="chore in getChoresForDate(day)" 
                     :key="`chore-${chore.id}`"
                     class="text-xs p-1 rounded cursor-pointer hover:opacity-80"
                     :style="{ backgroundColor: chore.category.color + '20', borderLeft: `3px solid ${chore.category.color}` }"
                     @click="openCompletionModal(chore)">
                  <div class="flex items-center space-x-1">
                    <Clock class="h-3 w-3" />
                    <span class="truncate">{{ chore.name }}</span>
                  </div>
                </div>
                
                <!-- Completed chores -->
                <div v-for="completion in getCompletionsForDate(day)" 
                     :key="`completion-${completion.id}`"
                     class="text-xs p-1 rounded"
                     :style="{ backgroundColor: completion.chore.category.color + '10', borderLeft: `3px solid ${completion.chore.category.color}` }">
                  <div class="flex items-center space-x-1">
                    <CheckCircle2 class="h-3 w-3 text-green-600" />
                    <span class="truncate">{{ completion.chore.name }}</span>
                  </div>
                  <div v-if="completion.notes" class="text-xs text-muted-foreground mt-1 truncate">
                    {{ completion.notes }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    
    <ChoreCompletionModal
      :open="isModalOpen"
      :chore="selectedChore"
      @close="closeCompletionModal"
    />
  </AppLayout>
</template> 