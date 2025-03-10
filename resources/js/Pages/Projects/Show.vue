<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  project: {
    type: Object,
    required: true
  },
});

const page = usePage();
const user = page.props.auth.user;

// Permission checks
const canCreateTask = computed(() => user.role.abilities.includes('task.create'));
const canManageProject = computed(() => user.role.abilities.includes('project.manage') || props.project.user_id === user.id);
const canUpdateProject = computed(() => user.role.abilities.includes('project.update') || props.project.user_id === user.id);

// Tasks filtering
const search = ref('');
const selectedStatus = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');

// Status options matching the ones from Index.vue
const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'completed', label: 'Completed' },
  { value: 'closed', label: 'Closed' }
];

// Calculate project progress
const progress = computed(() => {
  if (!props.project.tasks_count || props.project.tasks_count === 0) return 0;
  return Math.round((props.project.completed_tasks_count / props.project.tasks_count) * 100);
});

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Get status badge class based on status
const getStatusBadgeClass = (status) => {
  const classes = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return `${classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'} px-2 py-1 text-xs font-medium rounded-full`;
};

// Calculate time left for due date
const calculateTimeLeft = (dueDate) => {
  if (!dueDate) return { text: 'No date', class: 'text-gray-500 dark:text-gray-400' };
  
  const now = new Date();
  const due = new Date(dueDate);
  const diffTime = due - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) {
    return { text: `Overdue by ${Math.abs(diffDays)} days`, class: 'text-red-600 dark:text-red-400 font-medium' };
  } else if (diffDays === 0) {
    return { text: 'Due today', class: 'text-orange-500 dark:text-orange-400 font-medium' };
  } else if (diffDays <= 3) {
    return { text: `${diffDays} days left`, class: 'text-yellow-600 dark:text-yellow-400 font-medium' };
  } else {
    return { text: `${diffDays} days left`, class: 'text-green-600 dark:text-green-400' };
  }
};

// Filter tasks
const filteredTasks = computed(() => {
  if (!props.project.tasks) return [];
  
  return props.project.tasks.filter(task => {
    // Status filter
    if (selectedStatus.value && task.status !== selectedStatus.value) return false;
    
    // Search filter
    if (search.value) {
      const searchTerm = search.value.toLowerCase();
      return task.name.toLowerCase().includes(searchTerm) || 
             (task.description && task.description.toLowerCase().includes(searchTerm));
    }
    
    return true;
  }).sort((a, b) => {
    // Sort logic
    const direction = sortOrder.value === 'asc' ? 1 : -1;
    
    if (sortBy.value === 'due_date') {
      if (!a.due_date) return direction; // Move items without due date to the end
      if (!b.due_date) return -direction;
      return direction * (new Date(a.due_date) - new Date(b.due_date));
    } else if (sortBy.value === 'name') {
      return direction * a.name.localeCompare(b.name);
    } else if (sortBy.value === 'status') {
      return direction * a.status.localeCompare(b.status);
    } else {
      return direction * (new Date(a[sortBy.value]) - new Date(b[sortBy.value]));
    }
  });
});

// Toggle sort order and update
const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'asc';
  }
};
</script>

<template>
  <Head :title="project.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link 
            :href="route('projects.index')" 
            class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
          >
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            All Projects
          </Link>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ project.name }}
          </h2>
          <span :class="getStatusBadgeClass(project.status)" class="inline-flex items-center">
            {{ project.status.replace('_', ' ').charAt(0).toUpperCase() + project.status.slice(1).replace('_', ' ') }}
          </span>
        </div>
        
        <div class="flex space-x-2">
          <Link 
            v-if="canUpdateProject" 
            :href="route('projects.edit', project.id)" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
          >
            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Project
          </Link>
          
          <Link 
            v-if="canCreateTask" 
            :href="route('tasks.create', project.id)" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
          >
            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Task
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Project Overview Card -->
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <!-- Project details -->
              <div class="md:col-span-2">
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Project Details</h3>
                
                <!-- Description -->
                <div class="mb-6">
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h4>
                  <div class="mt-1 text-gray-900 dark:text-gray-100">
                    <p v-if="project.description">{{ project.description }}</p>
                    <p v-else class="italic text-gray-500 dark:text-gray-400">No description provided</p>
                  </div>
                </div>
                
                <!-- Progress bar -->
                <div class="mb-6">
                  <div class="flex items-center justify-between mb-1 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Project Progress</span>
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ progress }}%</span>
                  </div>
                  <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                    <div class="h-2 bg-indigo-600 rounded-full dark:bg-indigo-500" :style="{ width: `${progress}%` }"></div>
                  </div>
                  <div class="flex items-center justify-between mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ project.completed_tasks_count || 0 }} completed</span>
                    <span>{{ project.tasks_count || 0 }} total tasks</span>
                  </div>
                </div>
              </div>
              
              <!-- Project stats & meta -->
              <div class="space-y-6">
                <!-- Project stats cards -->
                <div class="grid grid-cols-2 gap-4">
                  <!-- Created Date -->
                  <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</h5>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatDate(project.created_at) }}</p>
                  </div>
                  
                  <!-- Updated Date -->
                  <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Updated</h5>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatDate(project.updated_at) }}</p>
                  </div>
                </div>
                
                <!-- Project Owner -->
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                  <h5 class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">Project Owner</h5>
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="flex items-center justify-center w-10 h-10 font-bold text-indigo-600 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-300">
                        {{ project.owner?.name.charAt(0).toUpperCase() || 'U' }}
                      </div>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ project.owner?.name || 'Unknown User' }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Project Manager</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tasks Card -->
        <div class="mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Tasks</h3>
            
            <!-- Tasks Filters -->
            <div class="flex flex-col mb-6 space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
              <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                <!-- Search field -->
                <div>
                  <label for="task-search" class="sr-only">Search tasks</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                    </div>
                    <input
                      type="text"
                      id="task-search"
                      v-model="search"
                      placeholder="Search tasks"
                      class="block w-full py-2 pl-10 pr-3 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                    />
                  </div>
                </div>
                
                <!-- Status filter -->
                <div>
                  <label for="task-status" class="sr-only">Status</label>
                  <select
                    id="task-status"
                    v-model="selectedStatus"
                    class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  >
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </div>
              </div>
              
              <!-- Sorting options -->
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-700 dark:text-gray-300">Sort by:</span>
                <select
                  v-model="sortBy"
                  class="h-8 pl-3 pr-10 text-xs border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                  <option value="name">Task Name</option>
                  <option value="status">Status</option>
                  <option value="created_at">Creation Date</option>
                  <option value="due_date">Due Date</option>
                </select>
                <button
                  @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                  <span class="sr-only">Toggle sort order</span>
                  <svg 
                    v-if="sortOrder === 'asc'" 
                    class="w-5 h-5" 
                    xmlns="http://www.w3.org/2000/svg" 
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                  <svg 
                    v-else 
                    class="w-5 h-5" 
                    xmlns="http://www.w3.org/2000/svg" 
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                  >
                    <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- No tasks message -->
            <div v-if="!filteredTasks.length" class="py-8 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ search || selectedStatus ? 'No tasks match your filters' : 'No tasks yet' }}
              </h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ search || selectedStatus ? 'Try changing your search criteria.' : 'Get started by creating a new task.' }}
              </p>
              <div v-if="canCreateTask && !search && !selectedStatus" class="mt-6">
                <Link :href="route('tasks.create', project.id)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                  <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  New Task
                </Link>
              </div>
            </div>
            
            <!-- Tasks table -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <!-- Task name column -->
                    <th 
                      @click="toggleSort('name')" 
                      scope="col" 
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                    >
                      <div class="flex items-center">
                        Task 
                        <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'name' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </th>
                    
                    <!-- Status column -->
                    <th 
                      @click="toggleSort('status')" 
                      scope="col" 
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                    >
                      <div class="flex items-center">
                        Status
                        <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'status' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </th>
                    
                    <!-- Assignees column -->
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                      Assignees
                    </th>
                    
                    <!-- Due date column -->
                    <th 
                      @click="toggleSort('due_date')" 
                      scope="col" 
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                    >
                      <div class="flex items-center">
                        Due Date
                        <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'due_date' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </th>
                    
                    <!-- Actions column -->
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                  <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <!-- Task name and description -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">{{ task.name }}</span>
                        <span class="max-w-xs text-xs text-gray-500 truncate dark:text-gray-400">{{ task.description }}</span>
                      </div>
                    </td>
                    
                    <!-- Task status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(task.status)">
                        {{ task.status.replace('_', ' ').charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ') }}
                      </span>
                    </td>
                    
                    <!-- Assignees -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-wrap gap-1">
                        <span 
                          v-for="user in task.users" 
                          :key="user.id"
                          class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300"
                        >
                          {{ user.name }}
                        </span>
                        <span v-if="!task.users || task.users.length === 0" class="text-xs text-gray-500 dark:text-gray-400">
                          Unassigned
                        </span>
                      </div>
                    </td>
                    
                    <!-- Due date -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="calculateTimeLeft(task.due_date).class">
                        {{ task.due_date ? formatDate(task.due_date) : 'No due date' }}
                        <span v-if="task.due_date" class="block text-xs">
                          {{ calculateTimeLeft(task.due_date).text }}
                        </span>
                      </span>
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <Link 
                        :href="route('tasks.show', { project: project.id, task: task.id })" 
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        View
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
