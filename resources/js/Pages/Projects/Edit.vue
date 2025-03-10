<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
});

// Get user and check permissions
const page = usePage();
const user = page.props.auth.user;
const canUpdateProject = computed(() => user.role.abilities.includes('project.update'));
const canDeleteProject = computed(() => user.role.abilities.includes('project.delete'));
const isOwnerOrAdmin = computed(() => user.id === props.project.user_id || user.role.abilities.includes('project.manage'));

// Redirect if no permission
if (!canUpdateProject.value && !isOwnerOrAdmin.value) {
  window.location.href = route('projects.index');
}

// Status options (matching the ones from Index.vue)
const statusOptions = [
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'completed', label: 'Completed' },
  { value: 'closed', label: 'Closed' }
];

// Setup form with project data
const form = useForm({
  name: props.project.name,
  description: props.project.description || '',
  status: props.project.status,
  statuses: props.project.statuses || {}, // This may be needed based on your back-end logic
  owner_id: props.project.user_id,
});

// Confirmation for delete
const showDeleteModal = ref(false);

// Submit form
const submit = () => {
  form.put(route('projects.update', props.project.id), {
    onSuccess: () => {
      // Navigate to the projects index on success
    },
  });
};

// Delete project
const deleteProject = () => {
  if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
    form.delete(route('projects.destroy', props.project.id));
  }
};
</script>

<template>
  <Head title="Edit Project" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Edit Project
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('projects.show', props.project.id)" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
          >
            View Project
          </Link>
          <Link 
            :href="route('projects.index')" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
          >
            Back to Projects
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Form Card -->
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submit">
              <!-- Project Name -->
              <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  id="name" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                  required 
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</div>
              </div>
              
              <!-- Project Description -->
              <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea 
                  v-model="form.description" 
                  id="description" 
                  rows="4" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                ></textarea>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</div>
              </div>
              
              <!-- Project Status -->
              <div class="mb-6">
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
              <div class="mb-6">
                <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Owner</label>
                <select 
                  v-model="form.owner_id" 
                  id="owner_id" 
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" 
                  required
                  :disabled="!user.role.abilities.includes('project.manage')"
                >
                  <!-- If we don't have users list here, at least show current owner -->
                  <option :value="form.owner_id">{{ props.project.owner?.name || 'Current Owner' }}</option>
                </select>
                <div v-if="form.errors.owner_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.owner_id }}</div>
                <p v-if="!user.role.abilities.includes('project.manage')" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Only project managers can change project ownership
                </p>
              </div>
              
              <!-- Action Buttons -->
              <div class="flex justify-between">
                <!-- Submit Button -->
                <div>
                  <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                    :disabled="form.processing"
                  >
                    <svg v-if="form.processing" class="w-5 h-5 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Save Changes
                  </button>
                </div>
                
                <!-- Delete Button -->
                <div v-if="canDeleteProject || isOwnerOrAdmin">
                  <button 
                    type="button" 
                    @click="deleteProject" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                    :disabled="form.processing"
                  >
                    <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Project
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
    </div>
  </AuthenticatedLayout>
</template>