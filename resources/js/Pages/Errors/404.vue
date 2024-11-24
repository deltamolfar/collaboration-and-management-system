<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>


<template>
  <AuthenticatedLayout>
    <div class="flex items-center justify-center" style="height: 50vh;">
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">404</h1>
        <p class="text-xl font-semibold text-gray-800 dark:text-gray-200">Page not found</p>
        <p class="text-lg text-gray-800 dark:text-gray-200">Sorry, the page you are looking for could not be found.</p>
        <a href="/" class="text-blue-500 hover:underline">Go back home</a>
      </div>
    </div>
  </AuthenticatedLayout>
</template>