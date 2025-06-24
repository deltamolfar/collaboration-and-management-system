<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  roles: {
    type: Array,
    required: true
  }
});

const page = usePage();

function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    axios.delete(route('admin.users.destroy', id))
      .then(() => {
        location.reload();
      });
  }
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

async function createUser() {
  if (!userObj.value.name || !userObj.value.email || !userObj.value.password || !userObj.value.role) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.post(route('admin.users.store'), userObj.value)
    .then(() => {
      return true;
    })
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
  userObj.value = {
    name: '',
    email: '',
    password: '',
    role: ''
  };
  updatingDialog.value = false;
  dialog.value = true;
}

function openUpdateUser(user) {
  userObj.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role_id
  };
  updatingDialog.value = true;
  dialog.value = true;
}

const updatingDialog = ref(false);
const dialog = ref(false);
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="mb-4 text-3xl font-bold">Users</h1>
    </template>
    <div>
      <button @click="openCreateUser" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded hover:bg-blue-400">Create User</button>
      <div v-for="(user, index) in users" :key="index" class="p-4 mb-4 bg-white rounded shadow dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">{{ user.name }}</h2>
            <p>{{ user.email }}</p>
            <p>{{ roles.find(role => role.id === user.role_id)?.name }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="openUpdateUser(user)" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-400">Edit</button>
            <button @click="deleteUser(user.id)" class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-400">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <dialog v-show="dialog" class="fixed inset-0 z-50 flex items-center justify-center min-w-full min-h-full bg-black bg-opacity-50 dark:text-white">
      <div class="w-1/2 p-6 bg-white rounded shadow-lg dark:bg-gray-800">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">{{ updatingDialog ? 'Update User' : 'Create User' }}</h2>
          <button @click="dialog = false" class="text-xl font-bold">&times;</button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block mb-1">Name</label>
            <input v-model="userObj.name" class="w-full p-2 border rounded" />
            <span class="text-red-500">{{ errors.name }}</span>
          </div>
          <div>
            <label class="block mb-1">Email</label>
            <input v-model="userObj.email" class="w-full p-2 border rounded" :readonly="updatingDialog" />
            <span class="text-red-500">{{ errors.email }}</span>
          </div>
          <div>
            <label class="block mb-1">Password</label>
            <input v-model="userObj.password" type="password" class="w-full p-2 border rounded" />
            <span class="text-red-500">{{ errors.password }}</span>
          </div>
          <div>
            <label class="block mb-1">Role</label>
            <select v-model="userObj.role" class="w-full p-2 border rounded">
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
            <span class="text-red-500">{{ errors.role }}</span>
          </div>
          <button @click="createUser" class="w-full py-2 text-white bg-blue-500 rounded hover:bg-blue-400">
            {{ updatingDialog ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </dialog>
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