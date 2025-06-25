<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  project: {
    type: Object,
    required: true
  },
  users: {
    type: Array,
    required: true
  }
});

// Status options
const statusOptions = [
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'client_test', label: 'Client Test' },
  { value: 'completed', label: 'Completed' }
];

// Today's date for the min attribute of due date
const today = new Date().toISOString().split('T')[0];

// Setup form
const form = useForm({
  name: '',
  description: '',
  status: 'open',
  due_date: '',
  assignees: [],
});

const isSubmitting = ref(false);

// Compute project progress
const progress = computed(() => {
  if (!props.project.tasks_count) return 0;
  return Math.round((completed_tasks_count.value / ((props.project.tasks.length == 0) ? 1 : props.project.tasks.length)) * 100);
});

const submit = () => {
  isSubmitting.value = true;
  form.post(route('tasks.store', props.project.id), {
    onSuccess: () => {
      isSubmitting.value = false;
    },
    onError: () => {
      isSubmitting.value = false;
    }
  });
};
</script>

<template>
  <Head title="Create Task" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Create Task for {{ project.name }}
          </h2>
          <span :class="{
            'px-2 py-0.5 text-xs font-medium rounded-full': true,
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': project.status === 'open',
            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': project.status === 'in_progress',
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': project.status === 'paused',
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': project.status === 'completed',
            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': project.status === 'closed',
          }">
            {{ project.status?.replace('_', ' ').charAt(0).toUpperCase() + project.status?.slice(1).replace('_', ' ') || 'Unknown' }}
          </span>
        </div>
        
        <div class="flex space-x-2">
          <Link 
            :href="route('projects.show', project.id)" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Project
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <!-- Task Form -->
          <div class="md:col-span-2">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
              <div class="p-6 text-gray-900 dark:text-gray-100">
                <form @submit.prevent="submit">
                  <div class="space-y-6">
                    <!-- Task Name -->
                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task Name</label>
                      <input 
                        v-model="form.name" 
                        type="text" 
                        id="name" 
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                        placeholder="Enter task name"
                        required 
                      />
                      <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</div>
                    </div>
                    
                    <!-- Task Description -->
                    <div>
                      <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                      <textarea 
                        v-model="form.description" 
                        id="description" 
                        rows="4" 
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        placeholder="Provide a detailed description of what needs to be done"
                        required
                      ></textarea>
                      <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</div>
                    </div>
                    
                    <!-- Task Status -->
                    <div>
                      <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                      <select 
                        v-model="form.status" 
                        id="status" 
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                        required
                      >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                          {{ option.label }}
                        </option>
                      </select>
                      <div v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</div>
                    </div>

                    <!-- Due Date -->
                    <div>
                      <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                      <input 
                        v-model="form.due_date" 
                        type="date" 
                        id="due_date" 
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        :min="today"
                        required
                      />
                      <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.due_date }}</div>
                    </div>
                    
                    <!-- Assignees -->
                    <div>
                      <label for="assignees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                      <select 
                        v-model="form.assignees" 
                        id="assignees" 
                        multiple
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                        required
                      >
                        <option v-for="user in users" :key="user.id" :value="user.id">
                          {{ user.name }} {{ user.id === project.user_id ? '(Project Owner)' : '' }}
                        </option>
                      </select>
                      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple users</p>
                      <div v-if="form.errors.assignees" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.assignees }}</div>
                    </div>

                    <!-- Divider -->
                    <div class="relative py-3">
                      <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                      </div>
                      <div class="relative flex justify-center">
                        <span class="px-3 text-sm text-gray-500 bg-white dark:text-gray-400 dark:bg-gray-800">Task Creation</span>
                      </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                      <Link 
                        :href="route('projects.show', project.id)" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                      >
                        Cancel
                      </Link>
                      
                      <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                        :disabled="form.processing || isSubmitting"
                      >
                        <svg v-if="isSubmitting" class="w-5 h-5 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Task
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Project Info Sidebar -->
          <div>
            <!-- Project Card -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Project Details</h3>
                <div class="mt-4 space-y-4">
                  <!-- Project name -->
                  <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Name</h4>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ project.name }}</p>
                  </div>
                  
                  <!-- Project owner -->
                  <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Owner</h4>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ project.user?.name || 'Unknown' }}
                    </p>
                  </div>
                  
                  <!-- Project progress -->
                  <div>
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Progress</h4>
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ progress ?? 0 }}%</span>
                    </div>
                    <div class="w-full h-2 mt-2 bg-gray-200 rounded-full dark:bg-gray-700">
                      <div class="h-2 bg-indigo-600 rounded-full dark:bg-indigo-500" :style="{ width: `${progress}%` }"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1 text-xs text-gray-500 dark:text-gray-400">
                      <span>{{ project.completed_tasks_count || 0 }} completed</span>
                      <span>{{ project.tasks_count || 0 }} total tasks</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Task Creation Guide -->
            <div class="p-6 mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Task Creation Tips</h3>
                  <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <ul class="pl-5 space-y-1 list-disc">
                      <li>Give your task a clear, specific name</li>
                      <li>Include all necessary details in the description</li>
                      <li>Set a realistic due date</li>
                      <li>Assign the task to the appropriate team members</li>
                      <li>You can change the task status later as work progresses</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>