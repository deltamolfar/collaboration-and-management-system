<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps({
  roles: {
    type: Array,
    required: true
  }
});

const page = usePage();

const confirmDialogVisible = ref(false);
const confirmAction = ref(() => {});
const confirmMessage = ref('');
const roleToDelete = ref(null);

function askDeleteRole(role) {
  roleToDelete.value = role;
  confirmMessage.value = `Are you sure you want to delete role "${role.name}"? This action cannot be undone.`;
  confirmAction.value = async () => {
    try {
      await axios.delete(route('admin.roles.destroy', role.id));
      confirmDialogVisible.value = false;
      location.reload();
    } catch (error) {
      confirmDialogVisible.value = false;
    }
  };
  confirmDialogVisible.value = true;
}

const roleObj = ref({
  name: '',
  api_name: '',
  description: '',
  abilities: []
});

const errors = ref({
  name: null,
  api_name: null,
  description: null,
  abilities: null
});

const updatingDialog = ref(false);
const dialog = ref(false);

async function createRole() {
  if (!roleObj.value.name || !roleObj.value.api_name || !roleObj.value.abilities.length) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.post(route('admin.roles.store'), roleObj.value)
    .then(() => true)
    .catch(error => {
      const responseErrors = error.response.data.errors;
      errors.value.name = responseErrors?.name?.[0];
      errors.value.api_name = responseErrors?.api_name?.[0];
      errors.value.description = responseErrors?.description?.[0];
      errors.value.abilities = responseErrors?.abilities?.[0];
      return false;
    });

  if (!valid) return;

  dialog.value = false;
  location.reload();
}

async function updateRole() {
  if (!roleObj.value.name || !roleObj.value.api_name || !roleObj.value.abilities.length) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.put(`/admin/settings/roles/${roleObj.value.id}`, roleObj.value)
    .then(() => true)
    .catch(error => {
      const responseErrors = error.response.data.errors;
      errors.value.name = responseErrors?.name?.[0];
      errors.value.api_name = responseErrors?.api_name?.[0];
      errors.value.description = responseErrors?.description?.[0];
      errors.value.abilities = responseErrors?.abilities?.[0];
      return false;
    });

  if (!valid) return;

  dialog.value = false;
  location.reload();
}

function openCreateRole() {
  roleObj.value = {
    name: '',
    api_name: '',
    description: '',
    abilities: []
  };
  updatingDialog.value = false;
  errors.value = { name: null, api_name: null, description: null, abilities: null };
  dialog.value = true;
}

function openUpdateRole(role) {
  roleObj.value = {
    name: role.name,
    id: role.id,
    api_name: role.api_name,
    description: role.description,
    abilities: role.abilities
  };
  updatingDialog.value = true;
  errors.value = { name: null, api_name: null, description: null, abilities: null };
  dialog.value = true;
}

const allAbilities = [
  'task.create',
  'task.update',
  'task.delete',
  'task.log',
  'task.log.view_all',
  'task.comment',
  'project.create',
  'project.update',
  'project.delete',
  'project.assign',
  'project.manage',
  'project.view_all',
  'role.create',
  'role.update',
  'role.delete',
  'admin_dashboard.view',
];

const abilitiesValue = computed({
  get: () => roleObj.value.abilities,
  set: (val) => { roleObj.value.abilities = val; }
});
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Roles</h1>
    </template>
    <div class="max-w-4xl mx-auto">
      <div class="flex justify-end mb-6">
        <button @click="openCreateRole"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
          <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          Create Role
        </button>
      </div>
      <div class="grid gap-6 sm:grid-cols-2">
        <div v-for="role in roles" :key="role.id"
          class="relative p-6 transition bg-white shadow rounded-xl dark:bg-gray-800 group hover:shadow-lg">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ role.name }}</h2>
              <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">{{ role.description }}</p>
              <div class="flex flex-wrap gap-1 mt-2">
                <span v-for="ability in role.abilities" :key="ability"
                  class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200">
                  {{ ability }}
                </span>
              </div>
            </div>
            <div class="flex flex-col gap-2 ml-4">
              <button @click="openUpdateRole(role)"
                class="p-2 text-blue-700 bg-blue-100 rounded-full hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 dark:text-blue-200"
                title="Edit">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h4l11-11a2.828 2.828 0 10-4-4L4 17v4z"/>
                </svg>
              </button>
              <button @click="askDeleteRole(role)"
                class="p-2 text-red-700 bg-red-100 rounded-full hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 dark:text-red-200"
                title="Delete">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="dialog"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="relative w-full max-w-lg p-8 bg-white shadow-lg rounded-xl dark:bg-gray-900">
          <button @click="dialog = false"
            class="absolute text-2xl font-bold text-gray-400 top-4 right-4 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
            &times;
          </button>
          <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
            {{ updatingDialog ? 'Update Role' : 'Create Role' }}
          </h2>
          <form @submit.prevent="updatingDialog ? updateRole() : createRole()" class="space-y-5">
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <input v-model="roleObj.name"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.name }" />
              <span v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">API Name</label>
              <input v-model="roleObj.api_name"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.api_name }" />
              <span v-if="errors.api_name" class="text-xs text-red-500">{{ errors.api_name }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea v-model="roleObj.description"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.description }"></textarea>
              <span v-if="errors.description" class="text-xs text-red-500">{{ errors.description }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Abilities</label>
              <div class="grid grid-cols-2 gap-2 overflow-y-auto max-h-40">
                <label v-for="ability in allAbilities" :key="ability" class="flex items-center gap-2 text-sm">
                  <input type="checkbox" :value="ability" v-model="abilitiesValue"
                    class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" />
                  <span>{{ ability }}</span>
                </label>
              </div>
              <span v-if="errors.abilities" class="text-xs text-red-500">{{ errors.abilities }}</span>
            </div>
            <button type="submit"
              class="w-full py-2 mt-4 font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
              {{ updatingDialog ? 'Update' : 'Create' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <!-- Are you sure? Modal -->
    <transition name="fade">
      <div v-if="confirmDialogVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="relative w-full max-w-md p-8 bg-white shadow-lg rounded-xl dark:bg-gray-900">
          <div class="flex flex-col items-center">
            <div class="flex items-center justify-center w-16 h-16 mb-4 bg-red-100 rounded-full dark:bg-red-200">
              <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Confirm Deletion</h3>
            <p class="mb-6 text-sm text-center text-gray-500 dark:text-gray-400">{{ confirmMessage }}</p>
            <div class="flex w-full gap-3">
              <button
                @click="confirmAction()"
                class="inline-flex justify-center flex-1 px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800"
              >
                Delete
              </button>
              <button
                @click="confirmDialogVisible = false"
                class="inline-flex justify-center flex-1 px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>