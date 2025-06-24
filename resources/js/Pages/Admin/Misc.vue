<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

const settings = ref([
  { key: 'site_name', value: 'My Application' },
  { key: 'site_description', value: 'This is a description of my application.' }
]);

const newSetting = ref({ key: '', value: '' });

function addSetting() {
  settings.value.push({ ...newSetting.value });
  newSetting.value = { key: '', value: '' };
}

function updateSetting(index) {
  axios.post(route('admin.settings.update'), settings.value[index])
    .then(() => {
      alert('Setting updated successfully');
    })
    .catch(() => {
      alert('Failed to update setting');
    });
}

function deleteSetting(index) {
  settings.value.splice(index, 1);
}
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-4 text-3xl font-bold">Miscellaneous Settings</h1>
    </template>
    <div>
      <div v-for="(setting, index) in settings" :key="index" class="p-4 mb-4 bg-white rounded shadow dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">{{ setting.key }}</h2>
            <input v-model="setting.value" class="w-full p-2 border rounded" />
          </div>
          <div class="flex gap-2">
            <button @click="updateSetting(index)" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-400">Update</button>
            <button @click="deleteSetting(index)" class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-400">Delete</button>
          </div>
        </div>
      </div>
      <div class="p-4 mb-4 bg-white rounded shadow dark:bg-gray-800">
        <h2 class="mb-2 text-xl font-semibold">Add New Setting</h2>
        <input v-model="newSetting.value.key" placeholder="Key" class="w-full p-2 mb-2 border rounded" />
        <input v-model="newSetting.value.value" placeholder="Value" class="w-full p-2 mb-2 border rounded" />
        <button @click="addSetting" class="w-full py-2 text-white bg-green-500 rounded hover:bg-green-400">Add Setting</button>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
input {
  @apply text-black dark:text-white bg-white dark:bg-gray-800;
}

select {
  @apply text-black dark:text-white bg-white dark:bg-gray-800;
}
</style>