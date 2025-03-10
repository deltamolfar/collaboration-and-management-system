<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
  roles: {
    type: Array,
    required: true
  }
});

const page = usePage();

function deleteRole(id) {
  if (confirm('Are you sure you want to delete this role?')) {
    axios.delete(route('admin.roles.destroy', id))
      .then(() => {
        location.reload();
      });
  }
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

async function createRole() {
  if (!roleObj.value.name || !roleObj.value.api_name || !roleObj.value.abilities.length) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.post(route('admin.roles.store'), roleObj.value)
    .then(() => {
      return true;
    })
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
  dialog.value = true;
}

function openUpdateRole(role) {
  roleObj.value = {
    name: role.name,
    api_name: role.api_name,
    description: role.description,
    abilities: role.abilities
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
      <h1 class="mb-4 text-3xl font-bold">Roles</h1>
    </template>
    <div>
      <button @click="openCreateRole" class="px-4 py-2 mb-4 text-white bg-blue-500 rounded hover:bg-blue-400">Create Role</button>
      <div v-for="(role, index) in roles" :key="index" class="p-4 mb-4 bg-white rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">{{ role.name }}</h2>
            <p>{{ role.description }}</p>
            <p>{{ role.abilities.join(', ') }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="openUpdateRole(role)" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-400">Edit</button>
            <button @click="deleteRole(role.id)" class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-400">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <dialog v-show="dialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-1/2 p-6 bg-white rounded shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">{{ updatingDialog ? 'Update Role' : 'Create Role' }}</h2>
          <button @click="dialog = false" class="text-xl font-bold">&times;</button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block mb-1">Name</label>
            <input v-model="roleObj.name" class="w-full p-2 border rounded" />
            <span class="text-red-500">{{ errors.name }}</span>
          </div>
          <div>
            <label class="block mb-1">API Name</label>
            <input v-model="roleObj.api_name" class="w-full p-2 border rounded" />
            <span class="text-red-500">{{ errors.api_name }}</span>
          </div>
          <div>
            <label class="block mb-1">Description</label>
            <textarea v-model="roleObj.description" class="w-full p-2 border rounded"></textarea>
            <span class="text-red-500">{{ errors.description }}</span>
          </div>
          <div>
            <label class="block mb-1">Abilities</label>
            <input v-model="roleObj.abilities" class="w-full p-2 border rounded" />
            <span class="text-red-500">{{ errors.abilities }}</span>
          </div>
          <button @click="createRole" class="w-full py-2 text-white bg-blue-500 rounded hover:bg-blue-400">
            {{ updatingDialog ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </dialog>
  </AdminLayout>
</template>
