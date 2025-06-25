<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
  <AuthenticatedLayout class="h-full">
    <div class="flex h-full min-h-screen bg-gray-100 dark:bg-gray-900">
      <!-- Sidebar -->
      <aside class="flex flex-col w-64 h-screen px-6 py-8 shadow-lg bg-gradient-to-b from-gray-900 to-gray-800">
        <!-- Logo / Title -->
        <div class="flex items-center mb-8 space-x-3">
<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M10.8613 8.36335C11.3679 7.45445 11.6213 7 12 7C12.3787 7 12.6321 7.45445 13.1387 8.36335L13.2698 8.59849C13.4138 8.85677 13.4858 8.98591 13.598 9.07112C13.7103 9.15633 13.8501 9.18796 14.1296 9.25122L14.3842 9.30881C15.3681 9.53142 15.86 9.64273 15.977 10.0191C16.0941 10.3955 15.7587 10.7876 15.088 11.572L14.9144 11.7749C14.7238 11.9978 14.6285 12.1092 14.5857 12.2471C14.5428 12.385 14.5572 12.5336 14.586 12.831L14.6122 13.1018C14.7136 14.1482 14.7644 14.6715 14.4579 14.9041C14.1515 15.1367 13.6909 14.9246 12.7697 14.5005L12.5314 14.3907C12.2696 14.2702 12.1387 14.2099 12 14.2099C11.8613 14.2099 11.7304 14.2702 11.4686 14.3907L11.2303 14.5005C10.3091 14.9246 9.84847 15.1367 9.54206 14.9041C9.23565 14.6715 9.28635 14.1482 9.38776 13.1018L9.41399 12.831C9.44281 12.5336 9.45722 12.385 9.41435 12.2471C9.37147 12.1092 9.27617 11.9978 9.08557 11.7749L8.91204 11.572C8.2413 10.7876 7.90593 10.3955 8.02297 10.0191C8.14001 9.64273 8.63194 9.53142 9.61581 9.30881L9.87035 9.25122C10.1499 9.18796 10.2897 9.15633 10.402 9.07112C10.5142 8.98591 10.5862 8.85677 10.7302 8.59849L10.8613 8.36335Z" stroke="#828cff" stroke-width="1.5"></path> <path d="M3 10.4167C3 7.21907 3 5.62028 3.37752 5.08241C3.75503 4.54454 5.25832 4.02996 8.26491 3.00079L8.83772 2.80472C10.405 2.26824 11.1886 2 12 2C12.8114 2 13.595 2.26824 15.1623 2.80472L15.7351 3.00079C18.7417 4.02996 20.245 4.54454 20.6225 5.08241C21 5.62028 21 7.21907 21 10.4167C21 10.8996 21 11.4234 21 11.9914C21 14.4963 20.1632 16.4284 19 17.9041M3.19284 14C4.05026 18.2984 7.57641 20.5129 9.89856 21.5273C10.62 21.8424 10.9807 22 12 22C13.0193 22 13.38 21.8424 14.1014 21.5273C14.6796 21.2747 15.3324 20.9478 16 20.5328" stroke="#828cff" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>          <span class="text-2xl font-bold tracking-wide text-white">Admin Panel</span>
        </div>
        <!-- User Profile -->
        <div class="flex items-center mb-8 space-x-3">
          <div class="flex items-center justify-center w-12 h-12 text-xl font-bold bg-indigo-600 rounded-full">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <div class="font-semibold text-white">{{ user.name }}</div>
            <div class="text-xs text-indigo-200">{{ user.email }}</div>
          </div>
        </div>
        <!-- Navigation -->
        <nav class="flex-1">
          <ul class="space-y-2">
            <li>
              <Link :href="route('admin.dashboard')" class="flex items-center px-4 py-2 text-indigo-200 transition rounded-lg hover:bg-indigo-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"></path>
                </svg>
                Dashboard
              </Link>
            </li>
            <li>
              <Link :href="route('admin.users.index')" class="flex items-center px-4 py-2 text-indigo-200 transition rounded-lg hover:bg-indigo-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75"></path>
                </svg>
                Users
              </Link>
            </li>
            <li>
              <Link :href="route('admin.roles.index')" class="flex items-center px-4 py-2 text-indigo-200 transition rounded-lg hover:bg-indigo-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.104.896-2 2-2s2 .896 2 2-.896 2-2 2-2-.896-2-2zm-6 8v-2a4 4 0 014-4h4a4 4 0 014 4v2"></path>
                </svg>
                Roles
              </Link>
            </li>
            <li>
              <Link :href="route('admin.webhooks.index')" class="flex items-center px-4 py-2 text-indigo-200 transition rounded-lg hover:bg-indigo-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"></path>
                </svg>
                Webhooks
              </Link>
            </li>
            <li>
              <Link :href="route('admin.settings.index')" class="flex items-center px-4 py-2 text-indigo-200 transition rounded-lg hover:bg-indigo-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Settings
              </Link>
            </li>
          </ul>
        </nav>
        <div class="pt-8 mt-auto text-xs text-indigo-300">
          &copy; {{ new Date().getFullYear() }} Admin Panel
        </div>
      </aside>
      <!-- Main Content -->
      <main class="flex-1 min-h-screen p-8 overflow-y-auto bg-gray-50 dark:bg-gray-900 dark:text-white">
        <slot name="header" />
        <slot />
      </main>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
aside::-webkit-scrollbar {
  width: 8px;
}
aside::-webkit-scrollbar-thumb {
  background: #4f46e5;
  border-radius: 4px;
}
</style>