<!-- filepath: /home/deltamolfar/Documents/Dev/deltamolfar/pnu-diplom/resources/js/Pages/Tasks/Index.vue -->
<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  tasks: Object,
  filters: Object,
  projects: Array,
  can: Object,
});

const page = usePage();
const user = page.props.auth.user;

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedProject = ref(props.filters.project || '');
const selectedFilter = ref(props.filters.filter || '');
const sortBy = ref(props.filters.sort_by || 'due_date');
const sortOrder = ref(props.filters.sort_order || 'asc');

// Status options
const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'client_test', label: 'Client Test' },
  { value: 'completed', label: 'Completed' },
];

// Filter options
const filterOptions = [
  { value: '', label: 'All Tasks' },
  { value: 'upcoming', label: 'Due Soon (7 days)' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'completed', label: 'Completed' },
];

// Sort options
const sortOptions = [
  { value: 'due_date', label: 'Due Date' },
  { value: 'name', label: 'Task Name' },
  { value: 'status', label: 'Status' },
  { value: 'created_at', label: 'Creation Date' },
];

// Function to calculate time left
const calculateTimeLeft = (dueDate) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(dueDate);
  due.setHours(0, 0, 0, 0);
  const diffTime = due - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) {
    return { text: `${Math.abs(diffDays)} days overdue`, class: 'text-red-600 dark:text-red-400' };
  } else if (diffDays === 0) {
    return { text: 'Due today', class: 'text-orange-600 dark:text-orange-400' };
  } else if (diffDays === 1) {
    return { text: 'Due tomorrow', class: 'text-orange-600 dark:text-orange-400' };
  } else if (diffDays <= 3) {
    return { text: `${diffDays} days left`, class: 'text-yellow-600 dark:text-yellow-400' };
  } else {
    return { text: `${diffDays} days left`, class: 'text-green-600 dark:text-green-400' };
  }
};

// Get status badge class based on status
const getStatusBadgeClass = (status) => {
  const classes = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    client_test: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  };
  return `${classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'} px-2 py-1 text-xs font-medium rounded-full`;
};

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Debounced filter function
const filter = debounce(() => {
  router.get(
    route('tasks.index'),
    {
      search: search.value,
      status: selectedStatus.value,
      project: selectedProject.value,
      filter: selectedFilter.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}, 500);

// Watch for changes in filters
watch([search, selectedStatus, selectedProject, selectedFilter], filter);

// Toggle sort order and update
const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'asc';
  }
  filter();
};

// Compute sort icon based on current sort
const sortIcon = computed(() => {
  return (column) => {
    if (sortBy.value !== column) return 'fas fa-sort';
    return sortOrder.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
  };
});
</script>

<template>
  <Head title="Tasks" />

  <AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Tasks
        </h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <!-- Filter bar -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:space-y-0 md:space-x-4">
              <!-- Search -->
              <div class="flex-grow md:max-w-xs">
                <label for="search" class="sr-only">Search tasks</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                  <input
                    type="text"
                    id="search"
                    v-model="search"
                    placeholder="Search tasks"
                    class="block w-full py-2 pl-10 pr-3 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  />
                </div>
              </div>

              <!-- Status filter -->
              <div class="w-full md:w-auto">
                <label for="status" class="sr-only">Status</label>
                <select
                  id="status"
                  v-model="selectedStatus"
                  class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                  <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <!-- Project filter -->
              <div class="w-full md:w-auto">
                <label for="project" class="sr-only">Project</label>
                <select
                  id="project"
                  v-model="selectedProject"
                  class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                  <option value="">All Projects</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
              </div>

              <!-- Time filter -->
              <div class="w-full md:w-auto">
                <label for="filter" class="sr-only">Filter</label>
                <select
                  id="filter"
                  v-model="selectedFilter"
                  class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                >
                  <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Tasks table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th 
                    @click="toggleSort('name')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Task 
                      <svg class="w-4 h-4 ml-1" :class="sortIcon('name')" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path v-if="sortBy === 'name' && sortOrder === 'asc'" d="M288 352L192 256 96 352H288z"></path>
                        <path v-else-if="sortBy === 'name' && sortOrder === 'desc'" d="M32 160L128 256 224 160H32z"></path>
                        <path v-else d="M137.4 41.4c12.5-12.5 32.8-12.5 45.3 0l128 128c9.2 9.2 11.9 22.9 6.9 34.9s-16.6 19.8-29.6 19.8H32c-12.9 0-24.6-7.8-29.6-19.8s-2.2-25.7 6.9-34.9l128-128zm0 429.3l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128c-12.5 12.5-32.8 12.5-45.3 0z"></path>
                      </svg>
                    </div>
                  </th>
                  <th 
                    @click="toggleSort('project_id')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Project
                      <svg class="w-4 h-4 ml-1" :class="sortIcon('project_id')" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path v-if="sortBy === 'project_id' && sortOrder === 'asc'" d="M288 352L192 256 96 352H288z"></path>
                        <path v-else-if="sortBy === 'project_id' && sortOrder === 'desc'" d="M32 160L128 256 224 160H32z"></path>
                        <path v-else d="M137.4 41.4c12.5-12.5 32.8-12.5 45.3 0l128 128c9.2 9.2 11.9 22.9 6.9 34.9s-16.6 19.8-29.6 19.8H32c-12.9 0-24.6-7.8-29.6-19.8s-2.2-25.7 6.9-34.9l128-128zm0 429.3l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128c-12.5 12.5-32.8 12.5-45.3 0z"></path>
                      </svg>
                    </div>
                  </th>
                  <th 
                    @click="toggleSort('status')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Status
                      <svg class="w-4 h-4 ml-1" :class="sortIcon('status')" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path v-if="sortBy === 'status' && sortOrder === 'asc'" d="M288 352L192 256 96 352H288z"></path>
                        <path v-else-if="sortBy === 'status' && sortOrder === 'desc'" d="M32 160L128 256 224 160H32z"></path>
                        <path v-else d="M137.4 41.4c12.5-12.5 32.8-12.5 45.3 0l128 128c9.2 9.2 11.9 22.9 6.9 34.9s-16.6 19.8-29.6 19.8H32c-12.9 0-24.6-7.8-29.6-19.8s-2.2-25.7 6.9-34.9l128-128zm0 429.3l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128c-12.5 12.5-32.8 12.5-45.3 0z"></path>
                      </svg>
                    </div>
                  </th>
                  <th 
                    @click="toggleSort('due_date')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Due Date
                      <svg class="w-4 h-4 ml-1" :class="sortIcon('due_date')" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path v-if="sortBy === 'due_date' && sortOrder === 'asc'" d="M288 352L192 256 96 352H288z"></path>
                        <path v-else-if="sortBy === 'due_date' && sortOrder === 'desc'" d="M32 160L128 256 224 160H32z"></path>
                        <path v-else d="M137.4 41.4c12.5-12.5 32.8-12.5 45.3 0l128 128c9.2 9.2 11.9 22.9 6.9 34.9s-16.6 19.8-29.6 19.8H32c-12.9 0-24.6-7.8-29.6-19.8s-2.2-25.7 6.9-34.9l128-128zm0 429.3l-128-128c-9.2-9.2-11.9-22.9-6.9-34.9s16.6-19.8 29.6-19.8H288c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9l-128 128c-12.5 12.5-32.8 12.5-45.3 0z"></path>
                      </svg>
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                    Time Left
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                <tr v-if="tasks.data.length === 0">
                  <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                    No tasks found matching your criteria.
                  </td>
                </tr>
                <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <div class="flex flex-col">
                      <span>{{ task.name }}</span>
                      <span class="max-w-xs text-xs text-gray-500 truncate dark:text-gray-400">{{ task.description }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                    <Link :href="route('projects.show', task.project.id)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                      {{ task.project.name }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 text-sm whitespace-nowrap">
                    <span :class="getStatusBadgeClass(task.status)">
                      {{ task.status.replace('_', ' ').charAt(0).toUpperCase() + task.status.replace('_', ' ').slice(1) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                    {{ formatDate(task.due_date) }}
                  </td>
                  <td class="px-6 py-4 text-sm whitespace-nowrap">
                    <span :class="calculateTimeLeft(task.due_date).class">
                      {{ calculateTimeLeft(task.due_date).text }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                    <Link
                      :href="route('tasks.show', { project: task.project.id, task: task.id })"
                      class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      View
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <Pagination :links="tasks.links" />
          </div>
        </div>
        
        <!-- No Access Message -->
        <div v-if="tasks.data.length === 0 && !search && !selectedStatus && !selectedProject && !selectedFilter" class="p-6 mt-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.485 3.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 3.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No tasks available</h3>
              <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                <p>
                  You don't have any tasks assigned to you or you don't have access to any tasks. 
                  Please contact your project manager if you believe this is an error.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>