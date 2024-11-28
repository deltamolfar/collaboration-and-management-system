<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
});

const navigateToTask = (task) => {
  window.location.href = route('tasks.show', { project: props.project.id, task: task.id });
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex justify-between w-full p-2">
      <h1 class="text-2xl font-semibold dark:text-white">{{ project.name }}</h1>
      <p>{{ project.description }}</p>
      <div class="flex gap-1">
        <a :href="route('projects.edit', project)" class="self-center p-1 text-white bg-blue-500 rounded-md hover:bg-blue-400">Edit</a>
        <a :href="route('tasks.create', project)" class="self-center p-1 text-white bg-blue-500 rounded-md hover:bg-blue-400">New Task</a>
      </div>
    </div>
    <div class="p-2">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Task Name</th>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Assignees</th>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Last Update</th>
            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Due Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="project.tasks.length <= 0">
            <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">No tasks found</td>
          </tr>
          <tr
            v-for="task in project.tasks" :key="task.id"
            class="hover:cursor-pointer hover:bg-gray-100"
            @click="navigateToTask(task)"
          >
            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ task.id }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ task.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
              <ul>
                <li v-for="user in task.users" :key="user.id">{{ user.name }}</li>
              </ul>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ task.status }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ new Date(task.updated_at).toLocaleDateString() }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ new Date(task.due_date).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>
</template>