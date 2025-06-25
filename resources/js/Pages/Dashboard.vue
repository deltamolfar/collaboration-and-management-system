<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

// Get the authenticated user
const page = usePage();
const user = page.props.auth.user;

// Initialize data
const stats = ref({
  totalProjects: 0,
  totalTasks: 0,
  completedTasks: 0,
  upcomingDeadlines: 0
});
const recentActivity = ref([]);
const upcomingTasks = ref([]);
const loading = ref(true);

// Get time of day for greeting
const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return "Good Morning";
  if (hour < 17) return "Good Afternoon";
  return "Good Evening";
});

// Format date helper
const formatDate = (dateString) => {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Calculate days remaining helper
const daysRemaining = (dueDate) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(dueDate);
  due.setHours(0, 0, 0, 0);
  const diffTime = due - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

// Load dashboard data
onMounted(async () => {
  try {
    const [statsData, activityData, tasksData] = await Promise.all([
      axios.get(route('api.dashboard.stats')).catch(() => ({ data: { 
        totalProjects: -1, 
        totalTasks: -1, 
        completedTasks: -1, 
        upcomingDeadlines: -1 
      }})),
      axios.get(route('api.dashboard.activity')).catch(() => ({ data: [] })),
      axios.get(route('api.dashboard.upcoming-tasks')).catch(() => ({ data: [] })),
    ]);
    
    stats.value = statsData.data;
    recentActivity.value = activityData.data;
    upcomingTasks.value = tasksData.data;
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-3xl font-bold">{{ greeting }}, {{ user.name }}!</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Here's what's happening with your projects today.</p>
              </div>
              <div v-if="user.role.abilities.includes('project.create')" class="flex space-x-3">
                <Link :href="route('projects.create')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                  New Project
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Projects -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.totalProjects }}</p>
              </div>
            </div>
            <div class="mt-2">
              <Link :href="route('projects.index')" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                View all projects →
              </Link>
            </div>
          </div>

          <!-- Total Tasks -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.totalTasks }}</p>
              </div>
            </div>
            <div class="w-full h-2 mt-2 bg-gray-200 rounded-full dark:bg-gray-700">
              <div class="h-2 bg-green-500 rounded-full" 
                   :style="`width: ${stats.totalTasks ? (stats.completedTasks / stats.totalTasks) * 100 : 0}%`"></div>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ stats.completedTasks }} completed ({{ stats.totalTasks ? Math.round((stats.completedTasks / stats.totalTasks) * 100) : 0 }}%)
            </p>
          </div>

          <!-- Upcoming Deadlines -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Deadlines</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.upcomingDeadlines }}</p>
              </div>
            </div>
            <div class="mt-2">
              <Link :href="route('tasks.index', { filter: 'upcoming' })" class="text-sm text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300">
                View deadlines →
              </Link>
            </div>
          </div>

          <!-- Time Tracked -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Your Timesheet</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">View Hours</p>
              </div>
            </div>
            <div class="mt-2">
              <Link :href="route('timesheet')" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                Go to timesheet →
              </Link>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-3">
          <!-- Upcoming Tasks -->
          <div class="col-span-2 p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upcoming Tasks</h3>
            <div class="mt-4" v-if="loading">
              <div class="flex items-center justify-center h-40">
                <svg class="w-8 h-8 text-gray-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
            <div v-else-if="upcomingTasks.length === 0" class="flex items-center justify-center h-40">
              <p class="text-gray-500 dark:text-gray-400">No upcoming tasks.</p>
            </div>
            <ul v-else class="mt-4 space-y-4">
              <li v-for="task in upcomingTasks" :key="task.id" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                <div class="flex items-start justify-between">
                  <div>
                    <h4 class="font-medium text-gray-900 text-md dark:text-gray-100">{{ task.name }}</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ task.description }}</p>
                    <div class="flex items-center mt-2 space-x-4 text-sm text-gray-500 dark:text-gray-400">
                      <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Due: {{ formatDate(task.due_date) }}</span>
                      </div>
                      <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <span>{{ task.comments_count || 0 }} comments</span>
                      </div>
                    </div>
                  </div>
                  <div>
                    <span :class="{
                      'px-2 py-1 text-xs font-medium rounded-full': true,
                      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': daysRemaining(task.due_date) <= 1,
                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': daysRemaining(task.due_date) > 1 && daysRemaining(task.due_date) <= 3,
                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': daysRemaining(task.due_date) > 3,
                    }">
                      {{ daysRemaining(task.due_date) <= 0 ? 'Due today' : 
                          daysRemaining(task.due_date) === 1 ? 'Tomorrow' : 
                        `${daysRemaining(task.due_date)} days left` }}
                    </span>
                  </div>
                </div>
                <div class="flex justify-end mt-3">
                  <Link :href="route('tasks.show', { project: task.project_id, task: task.id })" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                    View task →
                  </Link>
                </div>
              </li>
            </ul>
          </div>

          <!-- Recent Activity & Announcements -->
          <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
            <div v-if="loading" class="flex items-center justify-center h-20 mt-4">
              <svg class="w-6 h-6 text-gray-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <div v-else-if="recentActivity.length === 0" class="flex items-center justify-center h-20">
              <p class="text-gray-500 dark:text-gray-400">No recent activity.</p>
            </div>
            <ul v-else class="mt-4 space-y-3">
              <li v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center w-8 h-8 bg-gray-200 rounded-full dark:bg-gray-700">
                    <svg v-if="activity.type.includes('task')" class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <svg v-else-if="activity.type.includes('comment')" class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-gray-900 dark:text-gray-200">{{ activity.user_name }}</span>
                    {{ activity.description }}
                  </p>
                  <span class="text-xs text-gray-500 dark:text-gray-500">{{ formatDate(activity.created_at) }}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>