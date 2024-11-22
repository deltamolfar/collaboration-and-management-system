<script setup>
  import { computed } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  
  const props = defineProps({
    flashName: {String, required: true},
    flashColor: {String, required: true}
  });

  const page = usePage();

  const flashMsg = computed(() => page.props.flash[props.flashName]);
</script>

<template>
  <div v-if="flashMsg"
    class="p-2 mx-2 mt-2 mb-4 border rounded-md shadow-sm"
    :class="{
      [`bg-${props.flashColor}-100`]: true,
      [`border-${props.flashColor}-400`]: true,
      [`hover:bg-${props.flashColor}-200`]: true
    }"
    @click="page.props.flash[props.flashName] = null"
  >
    <p class="font-bold text-center"> {{ props.flashName.charAt(0).toUpperCase() + props.flashName.slice(1) }} </p>
    <p class="text-center">{{ flashMsg }}</p>
  </div>
</template>