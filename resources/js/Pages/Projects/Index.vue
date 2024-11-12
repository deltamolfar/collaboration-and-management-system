<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    paginatedProjects: {
        type: Object,
        required: true,
    },
});

const projects = props.paginatedProjects.data;
console.log(projects);
</script>

<template>
    <AuthenticatedLayout>
        <h2 class="mb-4 text-2xl font-bold">Projects Overview</h2>
        <div class="flex flex-col w-full">
            <div
                v-for="project in projects"
                :key="project.id"
                class="p-4 bg-gray-100 rounded-lg shadow"
            >
            {{ project }}
                <h3 class="text-xl font-semibold">{{ project.name }}</h3>
                <p class="text-gray-600">{{ project.description }}</p>
                <a
                    :href="route('projects.show', project.api_name)"
                    class="inline-block mt-2 text-blue-500 hover:underline"
                >
                    View Project
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>