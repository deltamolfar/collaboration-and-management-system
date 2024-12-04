<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const form = useForm({
  api_name: '',
  name: '',
  //statuses: { open: '#008000', closed: '#FF0000' },
  status: 'open',
  user_id: '',
});

const props = defineProps({
  users: {
    type: Array,
    required: true,
  },
});

const submit = () => {
  form.post(route('projects.store'), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <Head title="Create Project" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Create Project
      </h2>
    </template>

    <div class="p-4 overflow-hidden shadow-sm sm:rounded-lg">
      <form @submit.prevent="submit">
        <div class="mt-4">
          <label for="name" class="block text-sm font-medium dark:text-white">Name</label>
          <input v-model="form.name" type="text" name="name" id="name" class="block w-full mt-1" required />
          <span class="text-sm text-red-500">{{ form.errors.name }}</span>
        </div>

        <!-- <div class="mt-4">
        <label for="statuses" class="block text-sm font-medium text-white">Statuses</label>
        <span class="flex">
          Open
          <input v-model="form.statuses.open" type="color" name="statuses_open" id="statuses_open" class="block w-full mt-1" />
        </span>
        </div>-->

        <div class="mt-4">
          <label for="status" class="block text-sm font-medium dark:text-white">Status</label>
          <select v-model="form.status" name="status" id="status" class="block w-full mt-1" required>
              <option value="open">Open</option>
              <option value="closed">Closed</option>
          </select>
          <span class="text-sm text-red-500">{{ form.errors.status }}</span>
        </div>

        <div class="mt-4">
          <label for="user_id" class="block text-sm font-medium dark:text-white">Owner</label>
          <select v-model="form.user_id" name="user_id" id="user_id" class="block w-full mt-1" required>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
          <span class="text-sm text-red-500">{{ form.errors.user_id }}</span>
        </div>

        <div class="mt-4">
        <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
            Create
        </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>