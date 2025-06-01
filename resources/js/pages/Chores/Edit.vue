<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Save } from 'lucide-vue-next'

interface Category {
  id: number
  name: string
  color: string
}

interface Chore {
  id: number
  name: string
  description: string | null
  category_id: number
  frequency_type: string
  frequency_value: number
  next_due_at: string | null
  last_completed_at: string | null
  instruction_file_path: string | null
}

interface Props {
  chore: Chore
  categories: Category[]
}

const props = defineProps<Props>()

const form = useForm({
  name: props.chore.name,
  description: props.chore.description || '',
  category_id: props.chore.category_id,
  category_name: '',
  frequency_type: props.chore.frequency_type,
  frequency_value: props.chore.frequency_value,
  next_due_at: props.chore.next_due_at ? props.chore.next_due_at.split('T')[0] : ''
})

const isNewCategory = ref(false)

const selectedCategory = computed(() => {
  if (isNewCategory.value) return null
  return props.categories.find(cat => cat.id === form.category_id)
})

const frequencyTypeDisplay = computed(() => {
  const frequencyTypes = {
    'one_off': 'One-time task',
    'weeks': 'Weekly',
    'months': 'Monthly', 
    'years': 'Yearly'
  }
  return frequencyTypes[form.frequency_type as keyof typeof frequencyTypes] || ''
})

const handleCategoryChange = (value: string) => {
  if (value === 'new') {
    isNewCategory.value = true
    form.category_id = 0
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
    form.category_id = 0
  } else {
    // Using existing category - keep category_id, clear category_name
    form.category_name = ''
  }
  
  form.put(`/chores/${props.chore.id}`, {
    onSuccess: () => {
      router.visit('/chores')
    }
  })
}

const goBack = () => {
  router.visit('/chores')
}
</script>

<template>
  <Head title="Edit Chore" />
  
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center space-x-4">
        <Button variant="ghost" size="sm" @click="goBack">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <h1 class="text-2xl font-bold">Edit Chore</h1>
      </div>
      
      <!-- Form -->
      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Chore Details</CardTitle>
          <CardDescription>
            Update the details for this chore
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
                placeholder="Enter chore name"
                :class="{ 'border-red-500': form.errors.name }"
              />
              <p v-if="form.errors.name" class="text-sm text-red-500">
                {{ form.errors.name }}
              </p>
            </div>
            
            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Enter chore description (optional)"
                rows="3"
              />
            </div>
            
            <!-- Category -->
            <div class="space-y-2">
              <Label for="category">Category *</Label>
              <Select :value="form.category_id.toString()" @update:value="handleCategoryChange">
                <SelectTrigger :class="{ 'border-red-500': form.errors.category_id }">
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
              <p v-if="form.errors.category_id" class="text-sm text-red-500">
                {{ form.errors.category_id }}
              </p>
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
              <p v-if="form.errors.category_name" class="text-sm text-red-500">
                {{ form.errors.category_name }}
              </p>
            </div>
            
            <!-- Frequency Type -->
            <div class="space-y-2">
              <Label for="frequency_type">Frequency Type *</Label>
              <Select v-model="form.frequency_type">
                <SelectTrigger :class="{ 'border-red-500': form.errors.frequency_type }">
                  <SelectValue 
                    placeholder="Select frequency type" 
                    :displayValue="frequencyTypeDisplay"
                  />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="one_off">One-time task</SelectItem>
                  <SelectItem value="weeks">Weekly</SelectItem>
                  <SelectItem value="months">Monthly</SelectItem>
                  <SelectItem value="years">Yearly</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.frequency_type" class="text-sm text-red-500">
                {{ form.errors.frequency_type }}
              </p>
            </div>
            
            <!-- Frequency Value -->
            <div v-if="form.frequency_type !== 'one_off'" class="space-y-2">
              <Label for="frequency_value">Frequency Value *</Label>
              <Input
                id="frequency_value"
                v-model.number="form.frequency_value"
                type="number"
                min="1"
                placeholder="Enter frequency value"
                :class="{ 'border-red-500': form.errors.frequency_value }"
              />
              <p v-if="form.errors.frequency_value" class="text-sm text-red-500">
                {{ form.errors.frequency_value }}
              </p>
              <p class="text-sm text-muted-foreground">
                How often this chore should be done (e.g., every 2 weeks)
              </p>
            </div>
            
            <!-- Next Due Date -->
            <div class="space-y-2">
              <Label for="next_due_at">Next Due Date</Label>
              <Input
                id="next_due_at"
                v-model="form.next_due_at"
                type="date"
                :class="{ 'border-red-500': form.errors.next_due_at }"
              />
              <p v-if="form.errors.next_due_at" class="text-sm text-red-500">
                {{ form.errors.next_due_at }}
              </p>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end space-x-2">
              <Button type="button" variant="outline" @click="goBack">
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                <Save class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template> 