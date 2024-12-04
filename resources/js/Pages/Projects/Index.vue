<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    projects: {
        type: Object,
        required: true,
    },
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="p-2">
      <h2 class="mb-4 text-2xl font-bold dark:text-white">Projects Overview</h2>
      <div class="flex flex-col w-full" v-if="projects.length>0">
        <a
          v-for="project in projects"
          :key="project.id"
          class="flex justify-between p-4 text-white bg-gray-600 rounded-lg shadow cursor-pointer hover:bg-gray-500"
          :href="route('projects.show', project)"
        >
          <div class="flex flex-col">
            <h3 class="text-xl font-semibold">{{ project.name }}</h3>
            <p class="text-gray-600">{{ project.description }}</p>
          </div>
          <p>{{ project.status }}</p>
          <p>{{ project.updated_at }}</p>
        </a>
      </div>
      <div v-else>
        <p>No projects found.</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>