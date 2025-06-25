<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
  <AuthenticatedLayout class="h-full">
    <div class="flex h-full min-h-full">
      <aside class="w-1/4 p-4 text-white bg-gray-800">
        <h1 class="mb-4 text-2xl font-bold">Admin Dashboard</h1>
        <nav>
          <ul>
            <li class="mb-2">
              <a :href="route('admin.dashboard')" class="block px-4 py-2 rounded hover:bg-gray-700">Dashboard</a>
            </li>
            <li class="mb-2">
              <a :href="route('admin.users.index')" class="block px-4 py-2 rounded hover:bg-gray-700">Users</a>
            </li>
            <li class="mb-2">
              <a :href="route('admin.roles.index')" class="block px-4 py-2 rounded hover:bg-gray-700">Roles</a>
            </li>
            <li class="mb-2">
              <a :href="route('admin.webhooks.index')" class="block px-4 py-2 rounded hover:bg-gray-700">Webhooks</a>
            </li>
            <li class="mb-2">
              <a :href="route('admin.settings.index')" class="block px-4 py-2 rounded hover:bg-gray-700">Settings</a>
            </li>
          </ul>
        </nav>
      </aside>
      <main class="w-3/4 p-4 bg-gray-100 dark:bg-gray-900 dark:text-white">
        <slot name="header" />
        <slot />
      </main>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
aside {
  min-height: 93.5vh;
}
</style>
