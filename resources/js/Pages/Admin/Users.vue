<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';
import {ref} from 'vue';

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

function deleteUser (id) {
  if( confirm('Are you sure you want to delete this user?') ) {
    axios.delete(route('admin.users.destroy', id))
      .then((data) => {
        console.log('delete user', data);
      });
  }
  location.reload();
}

const userObj = {
  name: '',
  email: '',
  password: '',
  role: ''
};

const errors = ref({
  name: null,
  email: null,
  password: null,
  role: null
});

async function createUser() {
  if(!userObj.name || !userObj.email || !userObj.password || !userObj.role) {
    alert('Please fill all fields');
    return;
  }

  const valid = await axios.post(route('admin.users.store'), {
    ...userObj
  }).then(data => {
    console.log('create user', data);
    return true;
  }).catch(error => {
    console.log('error', error.response.data.errors);
    errors.value.name = error.response.data.errors?.name?.[0];
    errors.value.email = error.response.data.errors?.email?.[0];
    errors.value.password = error.response.data.errors?.password?.[0];
    errors.value.role = error.response.data.errors?.role?.[0];
    return false;
  });
  if( !valid ){ return; }

  dialog.value = false;
  location.reload();
}

// function updateUser(id) {
//   axios.put(route('admin.users.update', id), {
//     ...userObj
//   }).then(data => {
//     console.log('update user', data);
//   });
// }

function openCreateUser() {
  console.log('openCreateUser');
  userObj.name = '';
  userObj.email = '';
  userObj.role = '';
  userObj.password = '';

  updatingDialog.value = false;
  dialog.value = true;
}

// function openUpdateUser(user) {
//   console.log('openUpdateUser', user);
//   userObj.name = user.name;
//   userObj.email = user.email;
//   userObj.role = user.role;
//   userObj.password = '';
//   updatingDialog.value = true;
//   dialog.value = true;
// }

const updatingDialog = ref(false);
const dialog = ref(false);
</script>

<template>
  <AdminLayout>
    <template #header><span class="w-full text-lg font-bold text-center"> Users </span></template>
    <dialog
      v-show="dialog"
      class="fixed inset-0 z-50 flex flex-col items-center justify-center w-full h-full bg-transparent backdrop-blur-sm"
    >
      <div class="w-1/2">
        <div class="flex justify-between pl-1 rounded-t-lg dark:bg-gray-700">
          <h1 class="self-center dark:text-white">{{ updatingDialog ? 'Update user' : 'Create user' }}</h1>
          <button class="w-8 h-8 text-2xl font-black text-center text-white bg-red-500 rounded-tr-lg rounded-bl-lg hover:bg-red-400" @click="dialog = false">X</button>
        </div>
        <div class="flex flex-col px-2 pb-2 bg-gray-100 rounded-b-lg dark:bg-gray-700">
          <label class="flex flex-col">
            <span class="text-white">Name<span v-if="!updatingDialog" class="font-bold text-red-500">*</span></span>
            <input
              v-model="userObj.name"
              class="text-white bg-gray-600 rounded-md shadow-md"
              placeholder="Name"
            />
          </label>
          <span class="text-red-500">{{errors.name}}</span>
          <label class="flex flex-col">
            <span class="text-white">Email<span v-if="!updatingDialog" class="font-bold text-red-500">*</span></span>
            <input
              v-model="userObj.email"
              class="text-white bg-gray-600 rounded-md shadow-md read-only:cursor-not-allowed read-only:bg-gray-400"
              placeholder="Email"
              :readonly="updatingDialog"
            />
          </label>
          <span class="text-red-500">{{errors.email}}</span>
          <label class="flex flex-col">
            <span class="text-white">Password<span v-if="!updatingDialog" class="font-bold text-red-500">*</span></span>
            <input
              v-model="userObj.password"
              class="text-white bg-gray-600 rounded-md shadow-md"
              placeholder="Password"
            />
          </label>
          <span class="text-red-500">{{errors.password}}</span>
          <label class="flex flex-col">
            <span class="text-white">Role<span v-if="!updatingDialog" class="font-bold text-red-500">*</span></span>
            <select
              v-model="userObj.role"
              class="text-white bg-gray-600 rounded-md shadow-md"
            >
              <option v-for="(role, index) in roles" :key="index" :value="role.id">{{ role.name }}</option>
            </select>
          </label>
          <span class="text-red-500">{{errors.role}}</span>
          <button
            @click="createUser"
            class="w-full h-10 p-1 mt-2 font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-400"
          >
            Create
          </button>
        </div>
      </div>
    </dialog>
    <div class="">
      <button @click="openCreateUser" class="w-full px-4 py-2 text-white bg-blue-500 rounded-t-md hover:bg-blue-400">Create</button>
      <div
        v-for="(user, index) in users" :key="index"
        class="flex items-center justify-between py-1 bg-gray-700 border-b border-gray-200"
      >
        <div class="flex flex-col h-full px-2">
          <h2>
            {{ user.name }}
          </h2>
          <p>
            {{ user.email }}
          </p>
          <p>
            {{ role }}
          </p>
        </div>
        <div class="flex gap-2 px-2">
          <!-- <button @click="openUpdateUser(user)" class="px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-400">Edit</button> -->
          <button @click="deleteUser(user.id)" class="px-4 py-2 text-white bg-red-500 rounded-md">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

