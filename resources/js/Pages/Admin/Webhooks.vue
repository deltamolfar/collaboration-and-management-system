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
  header_key: '',
  header_value: '',
});

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
  form.header_key = webhook.header_key || '';
  form.header_value = webhook.header_value || '';
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
        <h1 class="text-3xl font-bold">Webhooks</h1>
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
    <div class="py-6">
      <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center h-32">
              <svg class="w-8 h-8 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>

            <!-- No Webhooks State -->
            <div v-else-if="webhooks.length === 0" class="flex flex-col items-center justify-center py-12">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No webhooks found</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating a new webhook.</p>
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
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Event</th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">URL</th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Headers</th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Created</th>
                    <th scope="col" class="relative px-6 py-3">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="webhook in webhooks" :key="webhook.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ getActionLabel(webhook.action) }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ webhook.action }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900 line-clamp-1">{{ webhook.url }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="webhook.header_key" class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                        {{ webhook.header_key }}: {{ webhook.header_value }}
                      </span>
                      <span v-else class="text-sm text-gray-500">No headers</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                      {{ new Date(webhook.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                      <div class="flex items-center justify-end space-x-3">
                        <button @click="openEditModal(webhook)" class="text-indigo-600 hover:text-indigo-900">
                          Edit
                        </button>
                        <button @click="openDeleteConfirm(webhook.id)" class="text-red-600 hover:text-red-900">
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                  {{ modalType === 'create' ? 'Create Webhook' : 'Edit Webhook' }}
                </h3>
                <div class="mt-4 space-y-4">
                  <!-- Action dropdown -->
                  <div>
                    <label for="action" class="block text-sm font-medium text-gray-700">Event</label>
                    <select 
                      id="action" 
                      v-model="form.action"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="" disabled>Select an event</option>
                      <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                    <div v-if="errors.action" class="mt-1 text-sm text-red-600">{{ errors.action }}</div>
                  </div>
                  
                  <!-- URL input -->
                  <div>
                    <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                    <input
                      type="text"
                      id="url"
                      v-model="form.url"
                      placeholder="https://example.com/webhook"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                    <div v-if="errors.url" class="mt-1 text-sm text-red-600">{{ errors.url }}</div>
                  </div>
                  
                  <!-- Header key/value -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="header_key" class="block text-sm font-medium text-gray-700">Header Key (Optional)</label>
                      <input
                        type="text"
                        id="header_key"
                        v-model="form.header_key"
                        placeholder="X-Api-Key"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                      <div v-if="errors.header_key" class="mt-1 text-sm text-red-600">{{ errors.header_key }}</div>
                    </div>
                    <div>
                      <label for="header_value" class="block text-sm font-medium text-gray-700">Header Value</label>
                      <input
                        type="text"
                        id="header_value"
                        v-model="form.header_value"
                        placeholder="your-api-key"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                      <div v-if="errors.header_value" class="mt-1 text-sm text-red-600">{{ errors.header_value }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
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
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Delete Modal -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Delete Webhook</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this webhook? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
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
  </AdminLayout>
</template>
