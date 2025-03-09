<script setup>
import { ref, computed } from 'vue';
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

const canEdit = ref(user.role.abilities.includes('task.update'));
const canDelete = ref(user.role.abilities.includes('task.delete'));
const canComment = ref(user.role.abilities.includes('task.comment'));
const canLogTime = ref(user.role.abilities.includes('task.log'));

// Form states
const form = ref({
  name: props.task.name,
  description: props.task.description,
  status: props.task.status,
  due_date: props.task.due_date,
  assignees: props.task.users.map(user => user.id),
});

// Editing states
const editing = ref({
  name: false,
  description: false,
  due_date: false,
  status: false,
  assignees: false
});

const newComment = ref('');
const newLog = ref({
  time_start: '',
  time_end: '',
  time_spent: '',
  description: '',
});

const dialog = ref(false);
const confirmDialogVisible = ref(false);
const confirmAction = ref(() => {});
const confirmMessage = ref('');
const errors = ref({});
const statusOptions = [
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'paused', label: 'Paused' },
  { value: 'client_test', label: 'Client Test' },
  { value: 'completed', label: 'Completed' }
];

// Format date for display
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Get status badge class based on status
const getStatusBadgeClass = (status) => {
  const classes = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    client_test: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  };
  return `${classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'} px-2 py-1 text-xs font-medium rounded-full`;
};

const statusLabel = computed(() => {
  const option = statusOptions.find(opt => opt.value === form.value.status);
  return option ? option.label : form.value.status;
});

// Begin editing a field
const startEditing = (field) => {
  if (!canEdit.value) return;
  editing.value[field] = true;
  // Set focus on the field after the DOM has updated
  setTimeout(() => {
    const el = document.getElementById(`edit-${field}`);
    if (el) el.focus();
  }, 50);
};

// Save changes when done editing
const saveField = async (field) => {
  if (editing.value[field]) {
    editing.value[field] = false;
    await updateTask();
  }
};

// Submit comment
const submitComment = async () => {
  if (!newComment.value.trim()) return;
  
  try {
    await axios.post(route('tasks.comments.store', { project: props.project.id, task: props.task.id }), {
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

// Delete comment
const deleteComment = async (commentId) => {
  confirmMessage.value = 'Are you sure you want to delete this comment?';
  confirmAction.value = async () => {
    try {
      await axios.delete(route('tasks.comments.destroy', { 
        project: props.project.id, 
        task: props.task.id,
        comment: commentId 
      }));
      location.reload();
    } catch (error) {
      console.error('Error deleting comment:', error);
    }
    confirmDialogVisible.value = false;
  };
  confirmDialogVisible.value = true;
};

// Submit time log
const submitLog = async () => {
  try {
    await axios.post(route('tasks.logs.store', { project: props.project.id, task: props.task.id }), {
      user_id: user.id,
      ...newLog.value,
    });
    newLog.value = {
      time_start: '',
      time_end: '',
      time_spent: '',
      description: '',
    };
    dialog.value = false;
    location.reload();
  } catch (error) {
    if (error.response) {
      errors.value = error.response.data.errors;
    }
  }
};

// Delete time log
const deleteLog = async (logId) => {
  confirmMessage.value = 'Are you sure you want to delete this time log?';
  confirmAction.value = async () => {
    try {
      await axios.delete(route('tasks.logs.destroy', { 
        project: props.project.id, 
        task: props.task.id,
        log: logId 
      }));
      location.reload();
    } catch (error) {
      console.error('Error deleting log:', error);
    }
    confirmDialogVisible.value = false;
  };
  confirmDialogVisible.value = true;
};

// Delete task
const deleteTask = () => {
  confirmMessage.value = 'Are you sure you want to delete this task? This action cannot be undone.';
  confirmAction.value = async () => {
    try {
      await axios.delete(route('tasks.destroy', { 
        project: props.project.id, 
        task: props.task.id 
      }));
      location.href = route('projects.show', props.project.id);
    } catch (error) {
      console.error('Error deleting task:', error);
    }
    confirmDialogVisible.value = false;
  };
  confirmDialogVisible.value = true;
};

// Update task
const updateTask = async () => {
  const sendForm = {...form.value};
  sendForm.assignees = '' + sendForm.assignees;
  
  try {
    await axios.put(route('tasks.update', { 
      project: props.project.id, 
      task: props.task.id 
    }), sendForm);
  } catch (error) {
    if (error.response) {
      errors.value = error.response.data.errors;
    }
    console.error('Error updating task:', error);
  }
};
</script>

<template>
  <Head title="Task Details" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Task Details
        </h2>
        <Link :href="route('projects.show', props.project.id)" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
          Back to Project
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Task Header Card -->
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <!-- Task Title (click to edit) -->
              <div class="flex-grow">
                <div v-if="editing.name && canEdit" class="flex items-center">
                  <input
                    id="edit-name"
                    v-model="form.name"
                    type="text"
                    class="block w-full text-2xl font-bold border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    @blur="saveField('name')"
                    @keyup.enter="saveField('name')"
                  />
                </div>
                <h1 
                  v-else 
                  @click="startEditing('name')" 
                  class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                  :class="{'cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400': canEdit}"
                >
                  {{ form.name }}
                  <span v-if="canEdit" class="ml-2 text-xs text-gray-400 dark:text-gray-500">(click to edit)</span>
                </h1>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                <div v-if="editing.status && canEdit" class="min-w-[120px]">
                  <select
                    id="edit-status"
                    v-model="form.status"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    @blur="saveField('status')"
                    @change="saveField('status')"
                  >
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </div>
                <span 
                  v-else 
                  @click="startEditing('status')" 
                  :class="[getStatusBadgeClass(form.status), canEdit ? 'cursor-pointer' : '']"
                >
                  {{ statusLabel }}
                </span>
              </div>

              <!-- Delete Task Button -->
              <button
                v-if="canDelete"
                @click="deleteTask"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-red-600 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 dark:hover:bg-red-700 dark:focus:ring-red-900"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Task
              </button>
            </div>
          </div>
        </div>
        
        <!-- Task Details Card -->
        <div class="mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <!-- Description -->
              <div class="md:col-span-2">
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">Description</h3>
                <div v-if="editing.description && canEdit" class="mt-1">
                  <textarea
                    id="edit-description"
                    v-model="form.description"
                    rows="6"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    @blur="saveField('description')"
                  ></textarea>
                </div>
                <div 
                  v-else 
                  @click="startEditing('description')" 
                  class="p-3 mt-1 prose-sm prose rounded-md bg-gray-50 dark:bg-gray-700 dark:text-gray-300 max-w-none"
                  :class="{'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600': canEdit}"
                >
                  <p v-if="form.description">{{ form.description }}</p>
                  <p v-else class="italic text-gray-500 dark:text-gray-400">No description provided</p>
                  <div v-if="canEdit" class="mt-2 text-xs text-gray-400 dark:text-gray-500">(click to edit)</div>
                </div>
              </div>

              <!-- Task Properties -->
              <div class="space-y-4">
                <!-- Due Date -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</h4>
                  <div v-if="editing.due_date && canEdit" class="mt-1">
                    <input
                      id="edit-due_date"
                      v-model="form.due_date"
                      type="date"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      @blur="saveField('due_date')"
                    />
                  </div>
                  <div 
                    v-else 
                    @click="startEditing('due_date')" 
                    class="mt-1 text-gray-900 dark:text-gray-100"
                    :class="{'cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400': canEdit}"
                  >
                    {{ formatDate(form.due_date) }}
                    <span v-if="canEdit" class="ml-1 text-xs text-gray-400 dark:text-gray-500">(click to edit)</span>
                  </div>
                </div>

                <!-- Assignees -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned to</h4>
                  <div v-if="editing.assignees && canEdit" class="mt-1">
                    <select
                      id="edit-assignees"
                      v-model="form.assignees"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      @blur="saveField('assignees')"
                      @change="saveField('assignees')"
                    >
                      <option v-for="user in users" :key="user.id" :value="[user.id]">
                        {{ user.name }}
                      </option>
                    </select>
                  </div>
                  <div 
                    v-else 
                    @click="startEditing('assignees')" 
                    class="mt-1 text-gray-900 dark:text-gray-100"
                    :class="{'cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400': canEdit}"
                  >
                    <div class="flex flex-wrap gap-1">
                      <span 
                        v-for="taskUser in props.task.users" 
                        :key="taskUser.id"
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300"
                      >
                        {{ taskUser.name }}
                      </span>
                    </div>
                    <span v-if="canEdit" class="block mt-1 text-xs text-gray-400 dark:text-gray-500">(click to edit)</span>
                  </div>
                </div>
                
                <!-- Project -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</h4>
                  <div class="mt-1 text-gray-900 dark:text-gray-100">
                    <Link :href="route('projects.show', props.project.id)" class="text-blue-600 hover:underline dark:text-blue-400">
                      {{ props.project.name }}
                    </Link>
                  </div>
                </div>
                
                <!-- Created At -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</h4>
                  <div class="mt-1 text-gray-900 dark:text-gray-100">
                    {{ formatDate(props.task.created_at) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Time Logs Card -->
        <div class="mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Time Logs</h3>
              <button 
                v-if="canLogTime" 
                @click="dialog = true" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Add Time Log
              </button>
            </div>
            <div v-if="logs.length === 0" class="px-4 py-8 text-center text-gray-500 rounded-md dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
              No time logs recorded for this task yet.
            </div>
            <div v-else class="overflow-hidden bg-white border border-gray-200 dark:bg-gray-700 dark:border-gray-600 sm:rounded-lg">
              <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                <li v-for="log in logs" :key="log.id" class="p-4 transition hover:bg-gray-50 dark:hover:bg-gray-650">
                  <div class="flex justify-between">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ log.user.name }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                          {{ log.time_spent }} hours
                        </span>
                      </div>
                      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ new Date(log.time_start).toLocaleString() }} - {{ new Date(log.time_end).toLocaleString() }}
                      </p>
                      <p v-if="log.description" class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ log.description }}
                      </p>
                    </div>
                    <button 
                      v-if="log.user.id === user.id || user.role.abilities.includes('task.log_delete')" 
                      @click="deleteLog(log.id)" 
                      class="p-1 text-gray-400 rounded-full hover:bg-gray-200 hover:text-gray-500 dark:hover:bg-gray-600 dark:hover:text-gray-300"
                      title="Delete log"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Comments Card -->
        <div class="mt-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Comments</h3>
            
            <div v-if="comments.length === 0" class="px-4 py-8 text-center text-gray-500 rounded-md dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
              No comments yet. Be the first to comment on this task!
            </div>
            <ul v-else class="space-y-4">
              <li v-for="comment in comments" :key="comment.id" class="border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ comment.user.name }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                      {{ new Date(comment.created_at).toLocaleString() }}
                    </span>
                  </div>
                  <button 
                    v-if="comment.user.id === user.id || user.role.abilities.includes('task.comment_delete')" 
                    @click="deleteComment(comment.id)" 
                    class="text-gray-400 rounded-full hover:bg-gray-200 hover:text-gray-500 dark:hover:bg-gray-600 dark:hover:text-gray-300"
                    title="Delete comment"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
                <p class="mt-2 text-gray-700 dark:text-gray-300">{{ comment.comment }}</p>
              </li>
            </ul>

            <div v-if="canComment" class="mt-6">
              <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Add a comment</h4>
              <textarea 
                v-model="newComment" 
                placeholder="Write your comment here..." 
                rows="3" 
                class="block w-full border-gray-300 rounded-md shadow-sm resize-y focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
              ></textarea>
              <div v-if="errors.comment" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.comment }}</div>
              <button 
                @click="submitComment" 
                class="inline-flex items-center px-4 py-2 mt-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-800"
                :disabled="!newComment.trim()"
              >
                Post Comment
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Time Log Dialog -->
    <div v-if="dialog" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75">
      <div class="relative px-4 pt-5 pb-4 mx-auto text-left bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg">
        <div class="absolute top-0 right-0 pt-4 pr-4">
          <button @click="dialog = false" class="text-gray-400 bg-white rounded-md hover:text-gray-500 dark:bg-transparent dark:text-gray-400 dark:hover:text-gray-300">
            <span class="sr-only">Close</span>
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:text-left sm:w-full">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Add Time Log</h3>
            <div class="mt-4 space-y-4">
              <!-- Time fields -->
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label for="time_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                  <input
                    type="datetime-local"
                    id="time_start"
                    v-model="newLog.time_start"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  />
                  <div v-if="errors.time_start" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.time_start }}</div>
                </div>
                <div>
                  <label for="time_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                  <input
                    type="datetime-local"
                    id="time_end"
                    v-model="newLog.time_end"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  />
                  <div v-if="errors.time_end" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.time_end }}</div>
                </div>
              </div>
              <!-- Duration (hours) -->
              <div>
                <label for="time_spent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (minutes)</label>
                <input
                  type="number"
                  id="time_spent"
                  v-model="newLog.time_spent"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  min="1"
                  placeholder="Enter time in minutes"
                />
                <div v-if="errors.time_spent" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.time_spent }}</div>
              </div>
              <!-- Description -->
              <div>
                <label for="log_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea
                  id="log_description"
                  v-model="newLog.description"
                  rows="3"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                  placeholder="What did you work on?"
                ></textarea>
                <div v-if="errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.description }}</div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
              <button
                @click="submitLog"
                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm dark:focus:ring-offset-gray-800"
              >
                Save Log
              </button>
              <button
                @click="dialog = false"
                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Dialog -->
    <div v-if="confirmDialogVisible" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75">
      <div class="relative px-4 pt-5 pb-4 mx-auto text-left bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg">
        <div class="sm:flex sm:items-start">
          <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Confirm Action</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ confirmMessage }}</p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            @click="confirmAction"
            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm dark:focus:ring-offset-gray-800"
          >
            Confirm
          </button>
          <button
            @click="confirmDialogVisible = false"
            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>