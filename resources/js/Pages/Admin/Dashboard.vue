<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-4 text-3xl font-bold">Admin Dashboard</h1>
    </template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <a :href="route('admin.users.index')" class="p-4 bg-white rounded shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
        <h2 class="text-xl font-semibold">Users</h2>
        <p>Manage all users in the system.</p>
      </a>
      <a :href="route('admin.roles.index')" class="p-4 bg-white rounded shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
        <h2 class="text-xl font-semibold">Roles</h2>
        <p>Manage user roles and permissions.</p>
      </a>
      <a :href="route('admin.webhooks.index')" class="p-4 bg-white rounded shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
        <h2 class="text-xl font-semibold">Webhooks</h2>
        <p>Configure system event webhooks.</p>
      </a>
      <a :href="route('admin.settings.index')" class="p-4 bg-white rounded shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
        <h2 class="text-xl font-semibold">Settings</h2>
        <p>Configure global settings for the application.</p>
      </a>
    </div>
  </AdminLayout>
</template>
