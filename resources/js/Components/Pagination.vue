<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap justify-center gap-1 sm:hidden">
            <Link
                v-for="(link, key) in links"
                :key="key"
                class="px-4 py-2 text-sm leading-4 border rounded hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-500 dark:border-gray-600 dark:hover:bg-gray-700"
                :class="{ 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-900': link.active }"
                :href="link.url"
                v-html="link.label"
            />
        </div>
        <div class="justify-center hidden gap-1 sm:flex">
            <Link
                v-for="(link, key) in links"
                :key="key"
                class="px-4 py-2 text-sm leading-4 border rounded hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-500 dark:border-gray-600 dark:hover:bg-gray-700"
                :class="{ 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-900': link.active }"
                :href="link.url"
                v-html="link.label"
            />
        </div>
    </div>
</template>
