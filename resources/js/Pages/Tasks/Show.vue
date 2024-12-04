<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
//import Multiselect from '@vueform/multiselect';
//import '@vueform/multiselect/themes/default.css';

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
  users: {
    type: Array,
    required: true,
  },
  logs: {
    type: Array,
    required: true,
  }
});

const page = usePage();
const user = page.props.auth.user;

const canEdit = ref(false);// TODO: DIPL ref(user.role.abilities.includes('task.update'));
const canDelete = ref(false);//TODO: DIPL //ref(user.role.abilities.includes('task.delete'));
const canComment = ref(false);//TODO: DIPL //ref(user.role.abilities.includes('task.comment'));
const canLogTime = ref(false);//TODO: DIPL

const newComment = ref('');
const newLog = ref({
  time_start: '',
  time_end: '',
  time_spent: '',
  description: '',
})
const dialog = ref(false);
const errors = ref({});

const submitComment = async () => {
  try {
    await axios.post(route('tasks.comments.store', { project: props.project, task: props.task }), {
      comment: newComment.value,
    });
    newComment.value = '';
    location.reload();
  } catch (error) {
    if (error.response) {
      errors.value = error.response.data.errors;
    }
  }
};

const submitLog = async () => {
  try {
    const resp = await axios.post(route('tasks.logs.store', { project: props.project, task: props.task }), {
      user_id: user.id,
      ...newLog.value,
    });
    console.log(resp);
    newLog.value = {
      time_start: '',
      time_end: '',
      time_spent: '',
      description: '',
    };
    //location.reload();
  } catch (error) {
    console.error(error);
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

const form = ref({
  name: props.task.name,
  description: props.task.description,
  status: props.task.status,
  due_date: props.task.due_date,
  assignees: props.task.users.map(user => user.id),
});

const updateTask = async () => {
  console.log(form.value);
  const sendForm = form.value;
  sendForm['assignees'] = ''+sendForm['assignees'];
  await axios.put(route('tasks.update', { project: props.project, task: props.task }), {
      ...form.value,
    }).catch((error) => {
      console.error(error);
      if (error.response) {
        errors.value = error.response.data.errors;
      }
    });
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
        <div class="flex items-center justify-between overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <p class="flex items-center gap-2 px-2">
              <strong>Status</strong>
              <select v-if="canEdit" @change="updateTask" v-model="form.status" class="block w-full p-1 px-8 my-1">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="paused">Paused</option>
                <option value="client_test">Client Test</option>
                <option value="completed">Completed</option>
              </select>
              <span v-else>{{ task.status }}</span>
            </p>
          <Link :href="route('projects.show', props.project)" class="items-center block p-4 py-2 mx-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
            Go back to all tasks
          </Link>
        </div>

        <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between">
              <h3 class="text-2xl font-bold">
                <input 
                  v-if="canEdit"
                  @blur="updateTask"
                  v-model="form.name"
                  type="text"
                  class="block w-full mt-1"
                />
                <span v-else>{{ task.name }}</span>
              </h3>
              <button v-if="canDelete" @click="deleteTask" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25">
                Delete
              </button>
            </div>
            <p class="mt-2">
              <textarea v-if="canEdit" @blur="updateTask" v-model="form.description" class="block w-full mt-1" rows="3"></textarea>
              <span v-else>{{ task.description }}</span>
            </p>
            <p class="mt-2">
              <strong>Due Date</strong>
              <input v-if="canEdit" @change="updateTask" v-model="form.due_date" type="date" class="block w-full mt-1" />
              <span v-else>{{ new Date(task.due_date).toLocaleDateString() }}</span>
            </p>
            <p class="mt-2">
              <strong>Assignee</strong>
              <select v-if="canEdit" @change="updateTask" v-model="form.assignees" class="block w-full mt-1">
                <option v-for="user in users" :key="user.id" :value="[user.id]" :selected="user.id==form.assignees">{{ user.name }}</option>
              </select>
            </p>
          </div>
        </div>

        <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-xl font-bold">Time Logs</h3>
            <ul class="mt-4">
              <li v-for="log in logs" :key="log.id" class="mb-4">
                <p><strong>{{ log.user.name }}:</strong> {{ log.description }}</p>
                <p class="text-sm text-gray-500">{{ new Date(log.time_start).toLocaleString() }} - {{ new Date(log.time_end).toLocaleString() }} ({{ log.time_spent }} hours)</p>
              </li>
            </ul>

            <div v-if="canLogTime" class="mt-4">
              <button @click="dialog = true" class="inline-flex items-center px-4 py-2 mt-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25">
                Add Time Log
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

  <dialog v-show="dialog" class="fixed inset-0 z-50 flex flex-col items-center justify-center w-full h-full bg-transparent backdrop-blur-sm">
    <div class="w-1/2">
      <div class="flex justify-between pl-2 rounded-t-lg dark:bg-gray-700">
        <h1 class="self-center text-lg font-semibold dark:text-white">Add Time Log</h1>
        <button class="w-8 h-8 text-2xl font-black text-center text-white bg-red-500 rounded-tr-lg rounded-bl-lg hover:bg-red-400" @click="dialog = false">X</button>
      </div>
      <div class="flex flex-col px-2 pb-2 bg-gray-100 rounded-b-lg dark:bg-gray-700">
        <label class="flex flex-col">
          <span class="text-white">Start Time</span>
          <input v-model="newLog.time_start" type="datetime-local" class="text-white bg-gray-600 rounded-md shadow-md" />
        </label>
        <label class="flex flex-col mt-4">
          <span class="text-white">End Time</span>
          <input v-model="newLog.time_end" type="datetime-local" class="text-white bg-gray-600 rounded-md shadow-md" />
        </label>
        <label class="flex flex-col mt-4">
          <span class="text-white">Time Spent (hours)<span class="font-bold text-red-500">*</span></span>
          <input v-model="newLog.time_spent" type="number" class="text-white bg-gray-600 rounded-md shadow-md" />
        </label>
        <label class="flex flex-col mt-4">
          <span class="text-white">Description</span>
          <textarea v-model="newLog.description" class="text-white bg-gray-600 rounded-md shadow-md" rows="3"></textarea>
        </label>
        <span class="text-red-500">{{ errors.log }}</span>
        <button @click="submitLog" class="w-full h-10 p-1 mt-2 font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-400">
          Submit
        </button>
      </div>
    </div>
  </dialog>
</template>

<style scoped>
.inputs {
  @apply border-gray-200 rounded-md;
}

input[type="text"], input[type="date"], textarea, select {
  @apply inputs;
}
</style>