<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

// Webhook data
const webhooks = ref([]);
const loading = ref(true);
const showModal = ref(false);
const modalType = ref('create');
const editingId = ref(null);
const errors = ref({});
const confirmDelete = ref(false);
const deleteId = ref(null);

const actionOptions = [
  { value: 'task.create', label: 'Task Created' },
  { value: 'task.update', label: 'Task Updated' },
  { value: 'task.delete', label: 'Task Deleted' },
  { value: 'task.comment', label: 'Task Comment Added' },
  { value: 'task.log', label: 'Task Log Added' },
  { value: 'project.create', label: 'Project Created' },
  { value: 'project.update', label: 'Project Updated' },
  { value: 'project.delete', label: 'Project Deleted' },
  { value: 'role.create', label: 'Role Created' },
  { value: 'role.update', label: 'Role Updated' },
  { value: 'role.delete', label: 'Role Deleted' },
  { value: 'user.create', label: 'User Created' },
  { value: 'user.update', label: 'User Updated' },
  { value: 'user.delete', label: 'User Deleted' },
];

// Form for creating/editing webhooks
const form = useForm({
  action: '',
  url: '',
  headers: [{ key: '', value: '' }],
  hmac_secret: '',
});

const addHeader = () => form.headers.push({ key: '', value: '' });
const removeHeader = (idx) => form.headers.splice(idx, 1);

// Load all webhooks
const loadWebhooks = async () => {
  try {
    loading.value = true;
    const response = await axios.get(route('admin.webhooks.index'));
    webhooks.value = response.data;
  } catch (error) {
    console.error('Error loading webhooks', error);
  } finally {
    loading.value = false;
  }
};

// Open modal for creating a new webhook
const openCreateModal = () => {
  form.reset();
  modalType.value = 'create';
  showModal.value = true;
  errors.value = {};
};

// Open modal for editing an existing webhook
const openEditModal = (webhook) => {
  form.action = webhook.action;
  form.url = webhook.url;
  form.headers = Array.isArray(webhook.headers) && webhook.headers.length > 0
    ? webhook.headers.map(h => ({ key: h.key, value: h.value }))
    : [{ key: '', value: '' }];
  form.hmac_secret = webhook.hmac_secret || '';
  editingId.value = webhook.id;
  modalType.value = 'edit';
  showModal.value = true;
  errors.value = {};
};

// Open confirmation for deleting a webhook
const openDeleteConfirm = (id) => {
  deleteId.value = id;
  confirmDelete.value = true;
};

const toggleWebhook = async (webhook) => {
  await axios.patch(route('admin.webhooks.toggle', webhook.id));
  loadWebhooks();
};

// Submit the form (create or update)
const submitForm = () => {
  if (modalType.value === 'create') {
    form.post(route('admin.webhooks.store'), {
      onSuccess: () => {
        showModal.value = false;
        loadWebhooks();
      },
      onError: (err) => {
        errors.value = err;
      }
    });
  } else {
    form.put(route('admin.webhooks.update', editingId.value), {
      onSuccess: () => {
        showModal.value = false;
        loadWebhooks();
      },
      onError: (err) => {
        errors.value = err;
      }
    });
  }
};

const testModal = ref(false);
const testResult = ref(null);

const sendTestWebhook = async (webhook) => {
  testResult.value = null;
  testModal.value = true;
  try {
    const { data } = await axios.post(route('admin.webhooks.test', webhook.id));
    testResult.value = data;
  } catch (e) {
    testResult.value = e.response?.data || { success: false, error: e.message };
  }
};

// Delete a webhook
const deleteWebhook = () => {
  if (deleteId.value) {
    axios.delete(route('admin.webhooks.destroy', deleteId.value))
      .then(() => {
        confirmDelete.value = false;
        loadWebhooks();
      })
      .catch(error => {
        console.error('Error deleting webhook', error);
      });
  }
};

// Get action label
const getActionLabel = (actionValue) => {
  const option = actionOptions.find(o => o.value === actionValue);
  return option ? option.label : actionValue;
};

const showLogsModal = ref(false);
const logs = ref([]);
const logsLoading = ref(false);

const openLogsModal = async (id) => {
  showLogsModal.value = true;
  logsLoading.value = true;
  const { data } = await axios.get(route('admin.webhooks.logs', id));
  logs.value = data;
  logsLoading.value = false;
};

// Load webhooks on component mount
onMounted(() => {
  loadWebhooks();
});
</script>

<template>
  <Head title="Webhooks" />

  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold dark:text-white">Webhooks</h1>
        <button 
          @click="openCreateModal" 
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Webhook
        </button>
      </div>
    </template>

    <!-- Main Content -->
    <div class="mt-2 overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center h-32">
          <svg class="w-8 h-8 text-blue-600 animate-spin dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <!-- No Webhooks State -->
        <div v-else-if="webhooks.length === 0" class="flex flex-col items-center justify-center py-12">
          <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No webhooks found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new webhook.</p>
          <div class="mt-6">
            <button
              @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              New Webhook
            </button>
          </div>
        </div>

        <!-- Webhooks Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Event</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">URL</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Created</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
              <tr v-for="webhook in webhooks" :key="webhook.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ getActionLabel(webhook.action) }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ webhook.action }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-white line-clamp-1">{{ webhook.url }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-white whitespace-nowrap">
                  {{ new Date(webhook.created_at).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                  <div class="flex items-center justify-end space-x-3">
                    <button @click="openEditModal(webhook)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                      Edit
                    </button>
                    <button @click="openDeleteConfirm(webhook.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                      Delete
                    </button>
                    <button @click="openLogsModal(webhook.id)" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                      Logs
                    </button>
                    <button @click="sendTestWebhook(webhook)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                      Test
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-80"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800 dark:text-white">
          <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
            <div class="sm:flex sm:items-start">
              <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                  {{ modalType === 'create' ? 'Create Webhook' : 'Edit Webhook' }}
                </h3>
                <div class="mt-4 space-y-4">
                  <!-- Action dropdown -->
                  <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Event</label>
                    <select 
                      id="action" 
                      v-model="form.action"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                      <option value="" disabled>Select an event</option>
                      <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                    <div v-if="errors.action" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.action }}</div>
                  </div>
                  
                  <!-- URL input -->
                  <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-200">URL</label>
                    <input
                      type="text"
                      id="url"
                      v-model="form.url"
                      placeholder="https://example.com/webhook"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                    <div v-if="errors.url" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.url }}</div>
                  </div>
                  
                  <!-- Headers -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Headers</label>
                    <div v-for="(header, idx) in form.headers" :key="idx" class="flex mt-1 space-x-2">
                      <input v-model="header.key" placeholder="Header Key" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                      <input v-model="header.value" placeholder="Header Value" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                      <button type="button" @click="removeHeader(idx)" v-if="form.headers.length > 1" class="text-red-600 dark:text-red-400">Remove</button>
                    </div>
                    <button type="button" @click="addHeader" class="mt-2 text-blue-600 dark:text-blue-400">Add Header</button>
                  </div>

                  <!-- HMAC Secret -->
                  <div>
                    <label for="hmac_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-200">HMAC Secret (Optional)</label>
                    <input
                      type="text"
                      id="hmac_secret"
                      v-model="form.hmac_secret"
                      placeholder="Secret for HMAC signature"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
            <button 
              @click="submitForm" 
              :disabled="form.processing"
              class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              {{ modalType === 'create' ? 'Create' : 'Update' }}
            </button>
            <button 
              @click="showModal = false" 
              type="button" 
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="confirmDelete" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-80"></div>
        </div>

        <!-- Delete Modal -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800 dark:text-white">
          <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
            <div class="sm:flex sm:items-start">
              <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-200">
                <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Delete Webhook</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-300">
                    Are you sure you want to delete this webhook? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
            <button 
              @click="deleteWebhook" 
              class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button 
              @click="confirmDelete = false" 
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Webhook Logs Modal -->
    <div v-if="showLogsModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900 dark:text-gray-200">Webhook Logs</h3>
            <div v-if="logsLoading">Loading...</div>
            <div v-else>
              <div v-if="logs.length === 0" class="text-gray-500">No logs found.</div>
              <ul v-else class="space-y-2 overflow-y-auto max-h-96">
                <li v-for="log in logs" :key="log.id" class="p-2 border rounded dark:border-gray-600 dark:text-white">
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(log.created_at).toLocaleString() }}</div>
                  <div class="text-xs">Status: <span :class="log.status_code === 200 ? 'text-green-600' : 'text-red-600'">{{ log.status_code ?? 'Undefined Code' }}</span></div>
                  <div class="text-xs break-all">Payload: {{ log.payload }}</div>
                  <div class="text-xs break-all">Response: {{ log.response }}</div>
                </li>
              </ul>
            </div>
          </div>
          <div class="px-4 py-3 dark:bg-gray-700 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="showLogsModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white dark:bg-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Test Webhook Modal -->
    <div v-if="testModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800 dark:text-white">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900 dark:text-white">Test Webhook Result</h3>
            <div v-if="!testResult" class="dark:text-white">Sending...</div>
            <div v-else>
              <div v-if="testResult.success" class="text-green-700 dark:text-green-400">
                <div>Status: {{ testResult.status }}</div>
                <div>Response: <pre class="break-all whitespace-pre-wrap">{{ testResult.response }}</pre></div>
              </div>
              <div v-else class="text-red-700 dark:text-red-400">
                <div>Error: {{ testResult.error }}</div>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-900">
            <button @click="testModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-900 dark:text-white dark:border-gray-700 dark:hover:bg-gray-800">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
