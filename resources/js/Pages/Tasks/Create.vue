<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
  users: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  name: '',
  description: '',
  status: 'open',
  due_date: '',
  assignees: [],
  project_id: props.project.id,
});

const submit = () => {
  form.assignees = form.assignees instanceof Array ? form.assignees : [form.assignees];
  console.log(form);
  form.post(route('tasks.store', props.project), {
    onSuccess: () => form.reset(),
    onError: (data) => {
      console.log(data);
    },
  });
};
</script>

<template>
  <Head title="Create Task" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Create Task
      </h2>
    </template>

    <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <form @submit.prevent="submit">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
              <input v-model="form.name" type="text" name="name" id="name" class="block w-full mt-1" required />
              <span class="text-sm text-red-500">{{ form.errors.name }}</span>
            </div>

            <div class="mt-4">
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="form.description" name="description" id="description" class="block w-full mt-1" required></textarea>
              <span class="text-sm text-red-500">{{ form.errors.description }}</span>
            </div>

            <div class="mt-4">
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select v-model="form.status" name="status" id="status" class="block w-full mt-1" required>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
              </select>
              <span class="text-sm text-red-500">{{ form.errors.status }}</span>
            </div>

            <div class="mt-4">
              <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
              <input v-model="form.due_date" type="date" name="due_date" id="due_date" class="block w-full mt-1" required />
              <span class="text-sm text-red-500">{{ form.errors.due_date }}</span>
            </div>

            <div class="mt-4">
              <label for="assignees" class="block text-sm font-medium text-gray-700">Assignees</label>
              <select v-model="form.assignees" name="assignees" id="assignees" class="block w-full mt-1" multiple required>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
              <span class="text-sm text-red-500">{{ form.errors.assignees }}</span>
            </div>

            <div class="mt-4">
              <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  </AuthenticatedLayout>
</template>