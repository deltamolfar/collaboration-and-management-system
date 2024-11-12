<script setup>
const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div>
        {{ project }}
    </div>
</template>