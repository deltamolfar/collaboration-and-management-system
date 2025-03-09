<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';
import { format, parseISO, isValid, startOfWeek, endOfWeek, eachDayOfInterval } from 'date-fns';

const props = defineProps({
  logs: Object,
  filters: Object,
  projects: Array,
  users: Array,
  summary: Object,
  can: Object,
});

const page = usePage();
const user = page.props.auth.user;

const search = ref(props.filters?.search || '');
const selectedUser = ref(props.filters?.user_id || '');
const selectedProject = ref(props.filters?.project_id || '');
const selectedTask = ref(props.filters?.task_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const groupBy = ref(props.filters?.group_by || 'date');
const sortBy = ref(props.filters?.sort_by || 'date');
const sortOrder = ref(props.filters?.sort_order || 'desc');
const viewMode = ref(props.filters?.view_mode || 'detailed');
const showBillableOnly = ref(props.filters?.billable_only === 'true');
const showCurrentUserOnly = ref(props.filters?.current_user_only === 'true');

// Automatically set current user if checkbox is checked
watch(showCurrentUserOnly, (newValue) => {
  if (newValue) {
    selectedUser.value = user.id;
  } else if (selectedUser.value === user.id) {
    selectedUser.value = '';
  }
});

// Calculate date ranges for quick filters
const today = new Date();
const thisWeekStart = format(startOfWeek(today, { weekStartsOn: 1 }), 'yyyy-MM-dd');
const thisWeekEnd = format(endOfWeek(today, { weekStartsOn: 1 }), 'yyyy-MM-dd');
const thisMonthStart = format(new Date(today.getFullYear(), today.getMonth(), 1), 'yyyy-MM-dd');
const thisMonthEnd = format(new Date(today.getFullYear(), today.getMonth() + 1, 0), 'yyyy-MM-dd');

// Date range presets
const dateRangePresets = [
  { label: 'Today', from: format(today, 'yyyy-MM-dd'), to: format(today, 'yyyy-MM-dd') },
  { label: 'This Week', from: thisWeekStart, to: thisWeekEnd },
  { label: 'This Month', from: thisMonthStart, to: thisMonthEnd },
  { label: 'All Time', from: '', to: '' },
];

// Format date helper
const formatDate = (dateString) => {
  if (!dateString || !isValid(parseISO(dateString))) return '';
  return format(parseISO(dateString), 'MMM d, yyyy');
};

// Format time helper
const formatTime = (dateTimeString) => {
  if (!dateTimeString || !isValid(parseISO(dateTimeString))) return '';
  return format(parseISO(dateTimeString), 'h:mm a');
};

// Format duration helper - converts minutes to hours and minutes
const formatDuration = (minutes) => {
  if (!minutes) return '0h';
  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;
  return hours > 0 
    ? remainingMinutes > 0 
      ? `${hours}h ${remainingMinutes}m` 
      : `${hours}h`
    : `${remainingMinutes}m`;
};

// Group logs by date
const groupedLogs = computed(() => {
  if (!props.logs || !props.logs.data) return [];
  
  const grouped = {};
  
  props.logs.data.forEach(log => {
    const dateKey = groupBy.value === 'date' 
      ? format(parseISO(log.created_at), 'yyyy-MM-dd')
      : groupBy.value === 'user' 
        ? `user-${log.user_id}`
        : groupBy.value === 'project' 
          ? `project-${log.task.project_id}`
          : `task-${log.task_id}`;
    
    if (!grouped[dateKey]) {
      grouped[dateKey] = {
        key: dateKey,
        title: groupBy.value === 'date' 
          ? formatDate(log.created_at)
          : groupBy.value === 'user' 
            ? log.user.name
            : groupBy.value === 'project' 
              ? log.task.project.name
              : log.task.name,
        logs: [],
        total_time: 0,
        billable_time: 0,
        non_billable_time: 0
      };
    }
    
    grouped[dateKey].logs.push(log);
    grouped[dateKey].total_time += log.time_spent;
    
    // Assuming there's a billable flag in the log or task
    if (log.task.billable_minutes) {
      grouped[dateKey].billable_time += log.time_spent;
    } else {
      grouped[dateKey].non_billable_time += log.time_spent;
    }
  });
  
  // Convert to array and sort
  const groupedArray = Object.values(grouped);
  
  // Sort by the group key or by total time
  if (sortBy.value === 'time') {
    groupedArray.sort((a, b) => {
      return sortOrder.value === 'asc' 
        ? a.total_time - b.total_time 
        : b.total_time - a.total_time;
    });
  } else {
    groupedArray.sort((a, b) => {
      if (sortOrder.value === 'asc') {
        return a.key < b.key ? -1 : 1;
      } else {
        return a.key > b.key ? -1 : 1;
      }
    });
  }
  
  return groupedArray;
});

// Calculate grand totals
const grandTotal = computed(() => {
  if (!props.summary) return { total: 0, billable: 0, non_billable: 0 };
  return {
    total: props.summary.total_time || 0,
    billable: props.summary.billable_time || 0,
    non_billable: props.summary.non_billable_time || 0
  };
});

// Apply date range preset
const applyDateRangePreset = (preset) => {
  dateFrom.value = preset.from;
  dateTo.value = preset.to;
  filter();
};

// Debounced filter function
const filter = debounce(() => {
  router.get(
    route('timesheet'),
    {
      search: search.value,
      user_id: selectedUser.value,
      project_id: selectedProject.value,
      task_id: selectedTask.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      group_by: groupBy.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
      view_mode: viewMode.value,
      billable_only: showBillableOnly.value ? 'true' : 'false',
      current_user_only: showCurrentUserOnly.value ? 'true' : 'false',
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}, 500);

// Watch for changes in filters
watch([
  search, selectedUser, selectedProject, selectedTask, 
  dateFrom, dateTo, groupBy, showBillableOnly, showCurrentUserOnly
], filter);

// Toggle sort order and update
const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'desc';
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

// Toggle between detailed and summary view
const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'detailed' ? 'summary' : 'detailed';
  filter();
};

// Set current week dates
const setCurrentWeek = () => {
  applyDateRangePreset(dateRangePresets[1]);
};

// Set current month dates
const setCurrentMonth = () => {
  applyDateRangePreset(dateRangePresets[2]);
};

// Initialize with current week on mount if no dates are set
onMounted(() => {
  if (!dateFrom.value && !dateTo.value) {
    setCurrentWeek();
  }
});
</script>

<template>
  <Head title="Timesheet" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Timesheet
        </h2>
        <div class="flex space-x-2">
          <button 
            @click="toggleViewMode" 
            class="inline-flex items-center px-3 py-1 text-sm font-medium text-white transition bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            {{ viewMode === 'detailed' ? 'Summary View' : 'Detailed View' }}
          </button>
        </div>
      </div>
    </template>

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
          <!-- Total Hours Card -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ formatDuration(grandTotal.total) }}</p>
              </div>
            </div>
          </div>

          <!-- Billable Hours Card -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Billable Hours</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ formatDuration(grandTotal.billable) }}</p>
              </div>
            </div>
            <div class="w-full h-2 mt-2 bg-gray-200 rounded-full dark:bg-gray-700">
              <div class="h-2 bg-green-500 rounded-full" 
                   :style="`width: ${grandTotal.total ? (grandTotal.billable / grandTotal.total) * 100 : 0}%`"></div>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ grandTotal.total ? Math.round((grandTotal.billable / grandTotal.total) * 100) : 0 }}% billable
            </p>
          </div>

          <!-- Non-Billable Hours Card -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Non-Billable Hours</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ formatDuration(grandTotal.non_billable) }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Filters Card -->
        <div class="mb-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col space-y-4">
              <!-- Top row filters -->
              <div class="flex flex-col space-y-4 md:flex-row md:items-center md:space-y-0 md:space-x-4">
                <!-- Search -->
                <div class="flex-grow md:max-w-xs">
                  <label for="search" class="sr-only">Search timesheet entries</label>
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
                      placeholder="Search descriptions"
                      class="block w-full py-2 pl-10 pr-3 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    />
                  </div>
                </div>

                <!-- Date range pickers -->
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                  <div>
                    <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input
                      type="date"
                      id="dateFrom"
                      v-model="dateFrom"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    />
                  </div>
                  <div>
                    <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input
                      type="date"
                      id="dateTo"
                      v-model="dateTo"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    />
                  </div>
                </div>

                <!-- Date range presets -->
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Quick Select</label>
                  <div class="flex space-x-2">
                    <button 
                      v-for="preset in dateRangePresets"
                      :key="preset.label"
                      @click="applyDateRangePreset(preset)"
                      class="px-2 py-1 text-xs font-medium text-gray-700 transition bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                    >
                      {{ preset.label }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Bottom row filters -->
              <div class="flex flex-col space-y-4 md:flex-row md:items-end md:space-y-0 md:space-x-4">
                <!-- User filter with search -->
                <div class="w-full md:w-64">
                  <label for="user" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                  <select
                    id="user"
                    v-model="selectedUser"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  >
                    <option value="">All Users</option>
                    <option v-for="userOption in props.users" :key="userOption.id" :value="userOption.id">
                      {{ userOption.name }}
                    </option>
                  </select>
                  
                  <div class="flex items-center mt-2">
                    <input 
                      type="checkbox" 
                      id="currentUserOnly" 
                      v-model="showCurrentUserOnly"
                      class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label for="currentUserOnly" class="block ml-2 text-sm text-gray-700 dark:text-gray-300">
                      Show only my entries
                    </label>
                  </div>
                </div>

                <!-- Project filter -->
                <div class="w-full md:w-64">
                  <label for="project" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                  <select
                    id="project"
                    v-model="selectedProject"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  >
                    <option value="">All Projects</option>
                    <option v-for="project in props.projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>

                <!-- Group by filter -->
                <div class="w-full md:w-auto">
                  <label for="groupBy" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group By</label>
                  <select
                    id="groupBy"
                    v-model="groupBy"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  >
                    <option value="date">Date</option>
                    <option value="user">User</option>
                    <option value="project">Project</option>
                    <option value="task">Task</option>
                  </select>
                </div>

                <!-- Show billable only checkbox -->
                <div class="flex items-center">
                  <input 
                    type="checkbox" 
                    id="billableOnly" 
                    v-model="showBillableOnly"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label for="billableOnly" class="block ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Billable hours only
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timesheet Content -->
        <div v-if="props.logs && props.logs.data.length > 0">
          <!-- Detailed Time Logs -->
          <div v-for="group in groupedLogs" :key="group.key" class="mb-6">
            <div class="mb-2">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ group.title }}</h3>
                <div class="flex items-center space-x-4">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Total: {{ formatDuration(group.total_time) }}
                  </span>
                  <span class="text-sm font-medium text-green-600 dark:text-green-400">
                    Billable: {{ formatDuration(group.billable_time) }}
                  </span>
                  <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">
                    Non-billable: {{ formatDuration(group.non_billable_time) }}
                  </span>
                </div>
              </div>
              
              <!-- Progress bar for billable ratio -->
              <div class="w-full h-1 mt-2 bg-gray-200 rounded-full dark:bg-gray-700">
                <div 
                  class="h-1 bg-green-500 rounded-full" 
                  :style="`width: ${group.total_time ? (group.billable_time / group.total_time) * 100 : 0}%`"
                ></div>
              </div>
            </div>
            
            <!-- Log entries table -->
            <div class="mb-4 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th v-if="groupBy !== 'task'" scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Task</th>
                      <th v-if="groupBy !== 'project'" scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Project</th>
                      <th v-if="groupBy !== 'user'" scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">User</th>
                      <th v-if="groupBy !== 'date'" scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Date</th>
                      <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Time Range</th>
                      <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Duration</th>
                      <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Description</th>
                      <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Billable</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    <tr v-for="log in group.logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                      <td v-if="groupBy !== 'task'" class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                        <Link :href="route('tasks.show', { project: log.task.project_id, task: log.task.id })" class="text-blue-600 hover:underline dark:text-blue-400">
                          {{ log.task.name }}
                        </Link>
                      </td>
                      <td v-if="groupBy !== 'project'" class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                        <Link :href="route('projects.show', { project: log.task.project_id })" class="text-blue-600 hover:underline dark:text-blue-400">
                          {{ log.task.project.name }}
                        </Link>
                      </td>
                      <td v-if="groupBy !== 'user'" class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                        {{ log.user.name }}
                      </td>
                      <td v-if="groupBy !== 'date'" class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                        {{ formatDate(log.created_at) }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                        {{ formatTime(log.time_start) }} - {{ formatTime(log.time_end) }}
                      </td>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-200">
                        {{ formatDuration(log.time_spent) }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ log.description }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        <span v-if="log.task.billable_minutes" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          Yes
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                          No
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-400">
                        <div class="flex items-center space-x-2">
                          <button 
                            v-if="log.user_id === user.id || user.role.abilities.includes('task.log.manage')"
                            @click="editLog(log)"
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                          </button>
                          <button 
                            v-if="log.user_id === user.id || user.role.abilities.includes('task.log.manage')"
                            @click="deleteLog(log)"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Pagination -->
          <Pagination :links="props.logs.links" class="mt-6" />
        </div>
        
        <!-- Empty state -->
        <div v-else class="p-8 text-center bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">No timesheet entries found.</p>
        </div>
    </div>
    </AuthenticatedLayout>
</template>
                    