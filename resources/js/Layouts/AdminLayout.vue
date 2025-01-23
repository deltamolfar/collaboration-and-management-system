<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
  <AuthenticatedLayout class="h-full min-h-screen">
    <div style="min-height: 90vh;" class="grid h-full min-h-full grid-cols-12 m-4 mt-2">
      <div class="flex flex-col col-span-4 bg-gray-100 dark:bg-gray-700 rounded-l-md">
        <h1 class="my-2 text-2xl font-extrabold text-center dark:text-white">Admin Dashboard</h1>
        <a :href="route('admin.dashboard')">
            <div class="rounded-t-md page-btn">Dashboard</div>
        </a>
        <a :href="route('admin.users.index')">
            <div class="rounded-t-md page-btn">Users</div>
        </a>
        <a :href="route('admin.roles.index')">
            <div class="page-btn">Roles</div>
        </a>
        <a :href="route('admin.settings.index')">
            <div class="rounded-b-md page-btn">Miscellaneous</div>
        </a>
      </div>
      <div class="col-span-8 p-2 bg-gray-200 dark:text-white dark:bg-gray-600 rounded-r-md">
        <slot name="header" />
        <slot />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.page-btn {
  padding: 10px;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.3s;
  @apply hover:bg-gray-500 m-0 dark:text-white font-semibold text-lg;
}
</style>
