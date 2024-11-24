<script setup lang="ts">
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
  task: {
    type: Object,
    required: true,
  },
  comments: {
    type: Array,
    required: true,
  },
});

const page = usePage();
const user = page.props.auth.user;

const canEdit = true;//ref(user.role.abilities.includes('task.update'));
const canDelete = true//ref(user.role.abilities.includes('task.delete'));
const canComment = true//ref(user.role.abilities.includes('task.comment'));

const newComment = ref('');
const errors = ref({});

const submitComment = async () => {
  console.log('submitting comment');
  try {
    const res = await axios.post(route('tasks.comments.store', {project: props.project, task: props.task}), {
      comment: newComment.value,
    });
    console.log('cmnt res', res);
    newComment.value = '';
    location.reload();
  } catch (error) {
    console.log('cmnt error', error);
    if (error.response) {
      errors.value = error.response.data.errors;
    }
  }
};

const deleteTask = async () => {
  if (confirm('Are you sure you want to delete this task?')) {
    await axios.delete(route('tasks.destroy', props.task));
    location.href = route('projects.show', props.task.project_id);
  }
};
</script>

<template>
  <Head title="Task Details" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Task Details
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-2xl font-bold">{{ task.name }}</h3>
            <p class="mt-2">{{ task.description }}</p>
            <p class="mt-2"><strong>Status:</strong> {{ task.status }}</p>
            <p class="mt-2"><strong>Due Date:</strong> {{ new Date(task.due_date).toLocaleDateString() }}</p>
            <p class="mt-2"><strong>Assignees:</strong></p>
            <ul>
              <li v-for="user in task.users" :key="user.id">{{ user.name }}</li>
            </ul>

            <div class="mt-4">
              <!-- <Link v-if="canEdit" :href="route('tasks.edit', task)" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
                Edit
              </Link> -->
              <button v-if="canDelete" @click="deleteTask" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25">
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-xl font-bold">Comments</h3>
            <ul class="mt-4">
              <li v-for="comment in comments" :key="comment.id" class="mb-4">
                <p><strong>{{ comment.user.name }}:</strong> {{ comment.comment }}</p>
                <p class="text-sm text-gray-500">{{ new Date(comment.created_at).toLocaleString() }}</p>
              </li>
            </ul>

            <div v-if="canComment" class="mt-4">
              <textarea v-model="newComment" class="block w-full mt-1" rows="3" placeholder="Add a comment"></textarea>
              <span class="text-sm text-red-500">{{ errors.comment }}</span>
              <button @click="submitComment" class="inline-flex items-center px-4 py-2 mt-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
                Submit
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>