<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  projects: Object,
  filters: Object,
  users: Array,
  stats: Object,
});

const page = usePage();
const user = page.props.auth.user;

// Permission checks
const canCreateProject = computed(() => user.role.abilities.includes('project.create'));
const canManageProjects = computed(() => user.role.abilities.includes('project.manage'));

// Filter and sort state
const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedOwner = ref(props.filters?.owner_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortOrder = ref(props.filters?.sort_order || 'desc');
const viewMode = ref(props.filters?.view_mode || 'grid');

// Status options
const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'completed', label: 'Completed' },
  { value: 'closed', label: 'Closed' }
];

// Sort options
const sortOptions = [
  { value: 'name', label: 'Project Name' },
  { value: 'status', label: 'Status' },
  { value: 'created_at', label: 'Creation Date' },
  { value: 'updated_at', label: 'Last Update' },
  { value: 'tasks_count', label: 'Number of Tasks' }
];

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

// Calculate progress percentage based on completed tasks vs all tasks
const calculateProgress = (project) => {
  if (!project.tasks_count) return 0;
  return Math.round((project.completed_tasks_count / project.tasks_count) * 100);
};

// Debounced filter function
const filter = debounce(() => {
  router.get(
    route('projects.index'),
    {
      search: search.value,
      status: selectedStatus.value,
      owner_id: selectedOwner.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
      view_mode: viewMode.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}, 500);

// Watch for changes in filters
watch([search, selectedStatus, selectedOwner, dateFrom, dateTo, viewMode], filter);

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

// Set date ranges for quick filters
const setDateRangeLastMonth = () => {
  const now = new Date();
  dateTo.value = new Date().toISOString().split('T')[0];
  const lastMonth = new Date(now.setMonth(now.getMonth() - 1));
  dateFrom.value = lastMonth.toISOString().split('T')[0];
  filter();
};

const setDateRangeLastWeek = () => {
  const now = new Date();
  dateTo.value = new Date().toISOString().split('T')[0];
  const lastWeek = new Date(now.setDate(now.getDate() - 7));
  dateFrom.value = lastWeek.toISOString().split('T')[0];
  filter();
};

const clearFilters = () => {
  search.value = '';
  selectedStatus.value = '';
  selectedOwner.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  filter();
};

// Set filters to show only user's projects
const showMyProjects = () => {
  selectedOwner.value = user.id;
  filter();
};

const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'grid' ? 'table' : 'grid';
  filter();
};
</script>

<template>
  <Head title="Projects" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Projects
        </h2>
        <div v-if="canCreateProject" class="flex space-x-2">
          <Link :href="route('projects.create')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Project
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 mb-6 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Projects -->
          <div class="p-5 overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
              </div>
              <div class="flex-1 ml-4">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</h5>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ props.stats?.totalProjects || props.projects.data?.length || '0' }}</h3>
              </div>
            </div>
          </div>
          
          <!-- Active Projects -->
          <div class="p-5 overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="flex-1 ml-4">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Projects</h5>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ props.stats?.activeProjects || '0' }}</h3>
              </div>
            </div>
          </div>
          
          <!-- Completed Projects -->
          <div class="p-5 overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="flex-1 ml-4">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Projects</h5>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ props.stats?.completedProjects || '0' }}</h3>
              </div>
            </div>
          </div>
          
          <!-- Total Tasks -->
          <div class="p-5 overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <div class="flex-1 ml-4">
                <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</h5>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ props.stats?.totalTasks || '0' }}</h3>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Filters Card -->
        <div class="mb-6 overflow-hidden bg-white shadow-sm rounded-xl dark:bg-gray-800">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col space-y-4">
              <!-- Top row filters -->
              <div class="flex flex-col space-y-4 md:flex-row md:items-center md:space-y-0 md:space-x-4">
                <!-- Search -->
                <div class="flex-grow md:max-w-xs">
                  <label for="search" class="sr-only">Search projects</label>
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
                      placeholder="Search projects"
                      class="block w-full py-2 pl-10 pr-3 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 sm:text-sm"
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

                <!-- Owner filter -->
                <div class="w-full md:w-auto">
                  <label for="owner" class="sr-only">Owner</label>
                  <select
                    id="owner"
                    v-model="selectedOwner"
                    class="block w-full py-2 pl-3 pr-10 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  >
                    <option value="">All Owners</option>
                    <option v-for="userOption in props.users" :key="userOption.id" :value="userOption.id">
                      {{ userOption.name }}
                    </option>
                  </select>
                </div>
              </div>
              
              <!-- Bottom row filters -->
              <div class="flex flex-col space-y-4 md:flex-row md:items-center md:space-y-0 md:space-x-4">
                <!-- Date from filter -->
                <div class="w-full md:w-auto">
                  <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                  <input
                    type="date"
                    id="date_from"
                    v-model="dateFrom"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  />
                </div>
                
                <!-- Date to filter -->
                <div class="w-full md:w-auto">
                  <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
                  <input
                    type="date"
                    id="date_to"
                    v-model="dateTo"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  />
                </div>
                
                <!-- Quick date filters -->
                <div class="flex flex-wrap gap-2 mt-5 md:mt-0">
                  <button
                    @click="setDateRangeLastWeek"
                    class="px-3 py-2 text-xs text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                  >
                    Last 7 days
                  </button>
                  <button
                    @click="setDateRangeLastMonth"
                    class="px-3 py-2 text-xs text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                  >
                    Last 30 days
                  </button>
                  <button
                    @click="showMyProjects"
                    class="px-3 py-2 text-xs text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                  >
                    My Projects
                  </button>
                  <button
                    @click="clearFilters"
                    class="px-3 py-2 text-xs text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                  >
                    Clear Filters
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sort and View Options -->
        <div class="flex flex-col items-center justify-between mb-4 space-y-3 md:flex-row md:space-y-0">
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-700 dark:text-gray-300">Sort by:</span>
            <select
              v-model="sortBy"
              @change="filter"
              class="h-8 pl-3 pr-10 text-xs border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <button
              @click="toggleSort(sortBy)"
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
          
          <div class="flex items-center space-x-2">
            <button 
              @click="toggleViewMode"
              class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
            >
              <svg v-if="viewMode === 'grid'" class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
              <svg v-else class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
              </svg>
              {{ viewMode === 'grid' ? 'Table View' : 'Grid View' }}
            </button>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="!props.projects?.data?.length" class="p-8 text-center bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No projects found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ search || selectedStatus || dateFrom || dateTo ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new project.' }}
          </p>
          <div v-if="canCreateProject" class="mt-6">
            <Link :href="route('projects.create')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
              <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              New Project
            </Link>
          </div>
        </div>
        
        <!-- Projects Grid View (continued) -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="project in props.projects.data" :key="project.id" class="overflow-hidden transition bg-white rounded-lg shadow hover:shadow-lg dark:bg-gray-800 transform hover:scale-[1.02] duration-200">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 truncate dark:text-white">{{ project.name }}</h3>
                  <span :class="getStatusBadgeClass(project.status)" class="inline-flex items-center mt-1">
                    {{ project.status.replace('_', ' ').charAt(0).toUpperCase() + project.status.slice(1).replace('_', ' ') }}
                  </span>
                </div>
                <div class="flex space-x-1">
                  <Link v-if="canManageProjects || project.user_id === user.id" :href="route('projects.edit', project.id)" class="p-2 text-gray-500 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Edit Project">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                </div>
              </div>
              
              <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <p class="h-10 line-clamp-2">{{ project.description || 'No description provided' }}</p>
              </div>
              
              <!-- Project details -->
              <div class="mt-4">
                <div class="flex items-center justify-between mb-1 text-xs">
                  <span class="text-gray-600 dark:text-gray-400">Progress</span>
                  <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ calculateProgress(project) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                  <div class="h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-500" :style="{ width: `${calculateProgress(project)}%` }"></div>
                </div>
              </div>
              
              <!-- Project stats -->
              <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500 dark:text-gray-400">Tasks</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ project.tasks_count || 0 }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500 dark:text-gray-400">Created</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(project.created_at) }}</span>
                </div>
              </div>
              
              <!-- Project owner and buttons -->
              <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                  <span class="text-xs text-gray-500 dark:text-gray-400">Owner: </span>
                  <span class="ml-1 text-sm font-medium text-gray-900 dark:text-white">{{ project.user?.name || 'Unknown' }}</span>
                </div>
                <Link :href="route('projects.show', project.id)" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                  View Project
                  <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Projects Table View -->
        <div v-else class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
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
                      Project 
                      <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'name' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
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
                      <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'status' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </th>
                  <th 
                    @click="toggleSort('created_at')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Created At
                      <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'created_at' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </th>
                  <th 
                    @click="toggleSort('tasks_count')" 
                    scope="col" 
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400"
                  >
                    <div class="flex items-center">
                      Tasks
                      <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortBy === 'tasks_count' && sortOrder === 'desc' }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                    Owner
                  </th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="project in props.projects.data" :key="project.id">
                  <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ project.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ project.description || 'No description provided' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                    <span :class="getStatusBadgeClass(project.status)">
                      {{ project.status.replace('_', ' ').charAt(0).toUpperCase() + project.status.slice(1).replace('_', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(project.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ project.tasks_count || 0 }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ project.user?.name || 'Unknown' }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium whitespace-nowrap dark:text-white">
                    <Link :href="route('projects.show', project.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</Link>
                    <Link v-if="canManageProjects || project.user_id === user.id" :href="route('projects.edit', project.id)" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Edit</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination :links="props.projects.links" @pageChanged="filter" />
  </AuthenticatedLayout>
</template>
