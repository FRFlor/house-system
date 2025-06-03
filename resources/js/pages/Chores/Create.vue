<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, AlertCircle } from 'lucide-vue-next'

interface Category {
  id: number
  name: string
  color: string
}

interface Props {
  categories: Category[]
}

const props = defineProps<Props>()

const form = useForm({
  name: '',
  description: '',
  category_id: null as number | null,
  category_name: '',
  frequency_type: '',
  frequency_value: 1,
  next_due_at: '',
  instruction_file_path: '',
})

const isNewCategory = ref(false)

const selectedCategory = computed(() => {
  if (isNewCategory.value) return null
  return props.categories.find(cat => cat.id === form.category_id)
})

const frequencyOptions = [
  { value: 'weeks', label: 'Weeks' },
  { value: 'months', label: 'Months' },
  { value: 'years', label: 'Years' },
  { value: 'one_off', label: 'One-time task' },
]

const isOneOff = computed(() => form.frequency_type === 'one_off')

const handleCategoryChange = (value: string) => {
  if (value === 'new') {
    isNewCategory.value = true
    form.category_id = null
    form.category_name = ''
  } else {
    isNewCategory.value = false
    form.category_id = parseInt(value)
    form.category_name = ''
  }
}

const submit = () => {
  // Prepare the data based on whether we're creating a new category or using existing
  if (isNewCategory.value) {
    // Creating new category - clear category_id, keep category_name
    form.category_id = null
  } else {
    // Using existing category - keep category_id, clear category_name
    form.category_name = ''
  }
  
  form.post('/chores', {
    onSuccess: () => {
      // Form will redirect to dashboard on success
    },
  })
}

const cancel = () => {
  router.visit('/dashboard')
}

// Set default date to tomorrow
const tomorrow = new Date()
tomorrow.setDate(tomorrow.getDate() + 1)
const defaultDate = tomorrow.toISOString().split('T')[0]

if (!form.next_due_at) {
  form.next_due_at = defaultDate
}
</script>

<template>
  <Head title="Create Chore" />
  
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center space-x-2">
        <Plus class="h-6 w-6" />
        <h1 class="text-2xl font-bold">Create New Chore</h1>
      </div>
      
      <!-- Form -->
      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Chore Details</CardTitle>
          <CardDescription>
            Create a new chore to track and manage your household tasks.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">Name *</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="e.g., Clean the kitchen"
                :class="{ 'border-red-500': form.errors.name }"
              />
              <div v-if="form.errors.name" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.name }}</span>
              </div>
            </div>
            
            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Description</Label>
              <textarea
                id="description"
                v-model="form.description"
                placeholder="Optional description or notes about this chore..."
                rows="3"
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                :class="{ 'border-red-500': form.errors.description }"
              />
              <div v-if="form.errors.description" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.description }}</span>
              </div>
            </div>
            
            <!-- Category -->
            <div class="space-y-2">
              <Label for="category">Category *</Label>
              <Select :value="form.category_id?.toString() || ''" @update:value="handleCategoryChange">
                <SelectTrigger :class="form.errors.category_id || form.errors.category_name ? 'border-red-500' : ''">
                  <SelectValue 
                    placeholder="Select a category" 
                    :displayValue="selectedCategory?.name"
                  />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="category in categories" 
                    :key="category.id" 
                    :value="category.id.toString()"
                  >
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-3 h-3 rounded-full" 
                        :style="{ backgroundColor: category.color }"
                      ></div>
                      <span>{{ category.name }}</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="new">
                    <span class="text-blue-600">+ Create new category</span>
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.category_id" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.category_id }}</span>
              </div>
              <div v-if="form.errors.category_name" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.category_name }}</span>
              </div>
            </div>
            
            <!-- New Category Name -->
            <div v-if="isNewCategory" class="space-y-2">
              <Label for="category_name">New Category Name *</Label>
              <Input
                id="category_name"
                v-model="form.category_name"
                placeholder="Enter new category name"
                :class="{ 'border-red-500': form.errors.category_name }"
              />
              <div v-if="form.errors.category_name" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.category_name }}</span>
              </div>
            </div>
            
            <!-- Frequency -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="frequency_type">Frequency Type *</Label>
                <select
                  id="frequency_type"
                  v-model="form.frequency_type"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  :class="{ 'border-red-500': form.errors.frequency_type }"
                >
                  <option value="">Select frequency</option>
                  <option 
                    v-for="option in frequencyOptions" 
                    :key="option.value" 
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </select>
                <div v-if="form.errors.frequency_type" class="flex items-center space-x-1 text-sm text-red-600">
                  <AlertCircle class="h-4 w-4" />
                  <span>{{ form.errors.frequency_type }}</span>
                </div>
              </div>
              
              <div v-if="!isOneOff" class="space-y-2">
                <Label for="frequency_value">Every *</Label>
                <Input
                  id="frequency_value"
                  v-model.number="form.frequency_value"
                  type="number"
                  min="1"
                  placeholder="1"
                  :class="{ 'border-red-500': form.errors.frequency_value }"
                />
                <div v-if="form.errors.frequency_value" class="flex items-center space-x-1 text-sm text-red-600">
                  <AlertCircle class="h-4 w-4" />
                  <span>{{ form.errors.frequency_value }}</span>
                </div>
              </div>
            </div>
            
            <!-- Next Due Date -->
            <div class="space-y-2">
              <Label for="next_due_at">
                {{ isOneOff ? 'Due Date *' : 'Next Due Date *' }}
              </Label>
              <Input
                id="next_due_at"
                v-model="form.next_due_at"
                type="date"
                :class="{ 'border-red-500': form.errors.next_due_at }"
              />
              <div v-if="form.errors.next_due_at" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.next_due_at }}</span>
              </div>
              <p class="text-sm text-muted-foreground">
                {{ isOneOff ? 'When should this task be completed?' : 'When should this chore first be due?' }}
              </p>
            </div>
            
            <!-- Instruction File Path -->
            <div class="space-y-2">
              <Label for="instruction_file_path">Instruction File Path</Label>
              <Input
                id="instruction_file_path"
                v-model="form.instruction_file_path"
                placeholder="e.g., /instructions/cleaning-guide.pdf"
                :class="{ 'border-red-500': form.errors.instruction_file_path }"
              />
              <div v-if="form.errors.instruction_file_path" class="flex items-center space-x-1 text-sm text-red-600">
                <AlertCircle class="h-4 w-4" />
                <span>{{ form.errors.instruction_file_path }}</span>
              </div>
              <p class="text-sm text-muted-foreground">
                Optional path to detailed instructions or documentation
              </p>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-4">
              <Button 
                type="button" 
                variant="outline" 
                @click="cancel"
                :disabled="form.processing"
              >
                Cancel
              </Button>
              <Button 
                type="submit" 
                :disabled="form.processing"
                class="min-w-[120px]"
              >
                <Plus v-if="!form.processing" class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Creating...' : 'Create Chore' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 