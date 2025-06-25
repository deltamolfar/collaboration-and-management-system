<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  flashName: { type: String, required: true },
  flashColor: { type: String, required: true }
});

const page = usePage();

const flashMsg = computed(() => page.props.flash[props.flashName]);
const colorMap = {
  green: {
    bg: 'bg-green-100 dark:bg-green-900',
    border: 'border-green-400 dark:border-green-700',
    text: 'text-green-800 dark:text-green-200',
    icon: 'M16.707 7.293a1 1 0 00-1.414 0L10 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l6-6a1 1 0 000-1.414z'
  },
  red: {
    bg: 'bg-red-100 dark:bg-red-900',
    border: 'border-red-400 dark:border-red-700',
    text: 'text-red-800 dark:text-red-200',
    icon: 'M6 18L18 6M6 6l12 12'
  },
  yellow: {
    bg: 'bg-yellow-100 dark:bg-yellow-900',
    border: 'border-yellow-400 dark:border-yellow-700',
    text: 'text-yellow-800 dark:text-yellow-200',
    icon: 'M12 8v4m0 4h.01'
  },
  blue: {
    bg: 'bg-blue-100 dark:bg-blue-900',
    border: 'border-blue-400 dark:border-blue-700',
    text: 'text-blue-800 dark:text-blue-200',
    icon: 'M13 16h-1v-4h-1m1-4h.01'
  },
  default: {
    bg: 'bg-gray-100 dark:bg-gray-800',
    border: 'border-gray-400 dark:border-gray-700',
    text: 'text-gray-800 dark:text-gray-200',
    icon: 'M12 8v4m0 4h.01'
  }
};
const color = colorMap[props.flashColor] || colorMap.default;
</script>

<template>
  <transition name="fade">
    <div
      v-if="flashMsg"
      class="flex items-center gap-3 px-4 py-3 mx-2 mt-2 mb-4 transition-all duration-300 border rounded-lg shadow-lg cursor-pointer pointer-events-auto"
      :class="[color.bg, color.border, color.text]"
      @click="page.props.flash[props.flashName] = null"
      style="min-width: 260px; max-width: 400px;"
    >
      <svg v-if="color.icon" class="flex-shrink-0 w-6 h-6 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path :d="color.icon" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <div class="flex-1">
        <p class="mb-0 text-base font-semibold leading-tight">
          {{ props.flashName.charAt(0).toUpperCase() + props.flashName.slice(1) }}
        </p>
        <p class="mt-1 text-sm leading-snug">{{ flashMsg }}</p>
      </div>
      <button
        class="ml-2 text-xl font-bold opacity-60 hover:opacity-100 focus:outline-none"
        @click.stop="page.props.flash[props.flashName] = null"
        aria-label="Close"
      >&times;</button>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>