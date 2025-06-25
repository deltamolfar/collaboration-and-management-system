<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
  users: Array,
  roles: Array
});

const page = usePage();

const confirmDialogVisible = ref(false);
const confirmAction = ref(() => {});
const confirmMessage = ref('');
const userToDelete = ref(null);

function askDeleteUser(user) {
  userToDelete.value = user;
  confirmMessage.value = `Are you sure you want to delete user "${user.name}"? This action cannot be undone.`;
  confirmAction.value = async () => {
    try {
      await axios.delete(route('admin.users.destroy', user.id));
      confirmDialogVisible.value = false;
      location.reload();
    } catch (error) {
      confirmDialogVisible.value = false;
    }
  };
  confirmDialogVisible.value = true;
}

const userObj = ref({
  name: '',
  email: '',
  password: '',
  role: ''
});

const errors = ref({
  name: null,
  email: null,
  password: null,
  role: null
});

const updatingDialog = ref(false);
const dialog = ref(false);

async function createUser() {
  if (!userObj.value.name || !userObj.value.email || (!updatingDialog.value && !userObj.value.password) || !userObj.value.role) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.post(route('admin.users.store'), userObj.value)
    .then(() => true)
    .catch(error => {
      const responseErrors = error.response.data.errors;
      errors.value.name = responseErrors?.name?.[0];
      errors.value.email = responseErrors?.email?.[0];
      errors.value.password = responseErrors?.password?.[0];
      errors.value.role = responseErrors?.role?.[0];
      return false;
    });

  if (!valid) return;

  dialog.value = false;
  location.reload();
}

async function updateUser() {
  if (!userObj.value.name || !userObj.value.email || (!updatingDialog.value && !userObj.value.password) || !userObj.value.role) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.put(route('admin.users.update', userObj.value.id), userObj.value)
    .then(() => true)
    .catch(error => {
      const responseErrors = error.response.data.errors;
      errors.value.name = responseErrors?.name?.[0];
      errors.value.email = responseErrors?.email?.[0];
      errors.value.password = responseErrors?.password?.[0];
      errors.value.role = responseErrors?.role?.[0];
      return false;
    });

  if (!valid) return;

  dialog.value = false;
  location.reload();
}

function openCreateUser() {
  userObj.value = { name: '', email: '', password: '', role: '' };
  updatingDialog.value = false;
  errors.value = { name: null, email: null, password: null, role: null };
  dialog.value = true;
}

function openUpdateUser(user) {
  userObj.value = { name: user.name, email: user.email, password: '', role: user.role_id, id: user.id };
  updatingDialog.value = true;
  errors.value = { name: null, email: null, password: null, role: null };
  dialog.value = true;
}
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Users</h1>
    </template>
    <div class="max-w-5xl mx-auto">
      <div class="flex justify-end mb-6">
        <button @click="openCreateUser"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
          <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          Create User
        </button>
      </div>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="user in users" :key="user.id"
          class="relative p-6 transition bg-white shadow rounded-xl dark:bg-gray-800 group hover:shadow-lg">
          <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center text-2xl font-bold text-indigo-700 bg-indigo-100 rounded-full w-14 h-14 dark:bg-indigo-900 dark:text-indigo-300">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
              <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200">
                {{ roles.find(role => role.id === user.role_id)?.name || 'Unknown' }}
              </span>
            </div>
          </div>
          <div class="absolute flex gap-2 transition opacity-0 top-4 right-4 group-hover:opacity-100">
            <button @click="openUpdateUser(user)"
              class="p-2 text-blue-700 bg-blue-100 rounded-full hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 dark:text-blue-200"
              title="Edit">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h4l11-11a2.828 2.828 0 10-4-4L4 17v4z"/>
              </svg>
            </button>
            <button @click="askDeleteUser(user)"
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

    <!-- Modal -->
    <transition name="fade">
      <div v-if="dialog"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="relative w-full max-w-md p-8 bg-white shadow-lg rounded-xl dark:bg-gray-900">
          <button @click="dialog = false"
            class="absolute text-2xl font-bold text-gray-400 top-4 right-4 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
            &times;
          </button>
          <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
            {{ updatingDialog ? 'Update User' : 'Create User' }}
          </h2>
          <form @submit.prevent="updatingDialog ? updateUser() : createUser()" class="space-y-5">
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <input v-model="userObj.name"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.name }" />
              <span v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <input v-model="userObj.email" :readonly="updatingDialog"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.email }" />
              <span v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
              <input v-model="userObj.password" type="password"
                :placeholder="updatingDialog ? 'Leave blank to keep current password' : ''"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.password }" />
              <span v-if="errors.password" class="text-xs text-red-500">{{ errors.password }}</span>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
              <select v-model="userObj.role"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.role }">
                <option value="" disabled>Select role</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
              </select>
              <span v-if="errors.role" class="text-xs text-red-500">{{ errors.role }}</span>
            </div>
            <button type="submit"
              class="w-full py-2 mt-4 font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-700 dark:hover:bg-indigo-600">
              {{ updatingDialog ? 'Update' : 'Create' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

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