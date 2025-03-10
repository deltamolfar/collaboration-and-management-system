<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-4 text-3xl font-bold">Admin Dashboard</h1>
    </template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div class="p-4 bg-white rounded shadow">
        <h2 class="text-xl font-semibold">Users</h2>
        <p>Manage all users in the system.</p>
      </div>
      <div class="p-4 bg-white rounded shadow">
        <h2 class="text-xl font-semibold">Roles</h2>
        <p>Manage user roles and permissions.</p>
      </div>
      <div class="p-4 bg-white rounded shadow">
        <h2 class="text-xl font-semibold">Settings</h2>
        <p>Configure global settings for the application.</p>
      </div>
    </div>
  </AdminLayout>
</template>
