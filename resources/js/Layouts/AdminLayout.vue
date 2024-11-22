<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
  <AuthenticatedLayout>
    <div class="grid grid-cols-12 m-4 mt-2">
      <div class="flex flex-col col-span-4 p-1 bg-gray-300 rounded-l-md">
        <h1 class="mt-2 text-2xl font-extrabold text-center">Admin Dashboard</h1>
        <a :href="route('admin.users.index')">
            <div class="rounded-t-md page-btn">Users</div>
        </a>
        <a :href="route('admin.roles.index')">
            <div class="page-btn">Roles</div>
        </a>
        <a :href="route('admin.settings.index')">
            <div class="page-btn">Miscellaneous</div>
        </a>
      </div>
      <div class="col-span-8 p-2 bg-gray-200 rounded-r-md">
        <slot name="header" />
        <slot />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.page-btn {
  padding: 10px;
  margin: 5px 0;
  background-color: #f0f0f0;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.3s;
}

.page-btn:hover {
  background-color: #e0e0e0;
}
</style>
