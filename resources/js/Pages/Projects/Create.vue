<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
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
  { value: 'completed', label: 'Completed' },
  { value: 'closed', label: 'Closed' }
];

// Setup form
const form = useForm({
  name: '',
  description: '',
  status: 'open',
  user_id: '',
});

const isSubmitting = ref(false);

const submit = () => {
  isSubmitting.value = true;
  form.post(route('projects.store'), {
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
  <Head title="Create Project" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Create New Project
        </h2>
        <Link 
          :href="route('projects.index')" 
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Projects
        </Link>
      </div>
    </template>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <!-- Project Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  id="name" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                  placeholder="Enter project name"
                  required 
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</div>
              </div>
              
              <!-- Project Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea 
                  v-model="form.description" 
                  id="description" 
                  rows="4" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  placeholder="Provide a description of your project"
                ></textarea>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</div>
              </div>
              
              <!-- Project Status -->
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
              
              <!-- Project Owner -->
              <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Owner</label>
                <select 
                  v-model="form.user_id" 
                  id="user_id" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                  required
                >
                  <option value="" disabled>Select an owner</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
                <div v-if="form.errors.user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.user_id }}</div>
              </div>
              
              <!-- Action Buttons -->
              <div class="flex justify-end space-x-3">
                <Link 
                  :href="route('projects.index')" 
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
                  Create Project
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Project Creation Guide Card -->
      <div class="p-6 mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Project Creation Guide</h3>
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              <p>Creating a new project is the first step in organizing your work:</p>
              <ul class="pl-5 mt-2 space-y-1 list-disc">
                <li>Give your project a clear, descriptive name</li>
                <li>Provide enough details in the description to understand the project's goals</li>
                <li>Set the initial status based on whether work is ready to begin</li>
                <li>Assign an owner who will be responsible for the project</li>
                <li>After creation, you can add team members and create tasks</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>