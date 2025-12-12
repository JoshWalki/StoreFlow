<template>
    <!-- Toast Notifications -->
    <ToastContainer />

    <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Staff Management</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your team members and their roles</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Staff Member
                    </button>
                </div>

                <!-- Staff Table -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stores</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="member in staff" :key="member.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ member.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ member.username }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getRoleBadgeClass(member.role)"
                                    >
                                        {{ member.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <div v-if="member.stores.length > 0" class="space-y-1">
                                            <div v-for="store in member.stores" :key="store.id" class="flex items-center gap-2">
                                                <span>{{ store.name }}</span>
                                                <span class="px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">{{ store.role }}</span>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400 dark:text-gray-500">No stores assigned</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        @click="openEditModal(member)"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(member)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="staff.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No staff members found. Click "Add Staff Member" to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitForm">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                                {{ editingStaff ? 'Edit Staff Member' : 'Add Staff Member' }}
                            </h3>

                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Username -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Password {{ editingStaff ? '(leave blank to keep current)' : '*' }}
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    :required="!editingStaff"
                                    minlength="8"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Role -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant Role *</label>
                                <select
                                    v-model="form.role"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="staff">Staff</option>
                                    <option value="manager">Manager</option>
                                    <option value="owner">Owner</option>
                                </select>
                            </div>

                            <!-- Store Assignments -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Store Access</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                                    <div v-for="store in stores" :key="store.id" class="flex items-center justify-between">
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                :value="store.id"
                                                v-model="form.store_ids"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">{{ store.name }}</span>
                                        </label>
                                        <select
                                            v-if="form.store_ids.includes(store.id)"
                                            v-model="form.store_roles[form.store_ids.indexOf(store.id)]"
                                            class="text-sm border-gray-300 rounded-md focus:ring-blue-500"
                                        >
                                            <option value="staff">Staff</option>
                                            <option value="manager">Manager</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                {{ processing ? 'Saving...' : (editingStaff ? 'Update' : 'Create') }}
                            </button>
                            <button
                                type="button"
                                @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Delete Staff Member</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete <strong>{{ deletingStaff?.name }}</strong>? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="deleteStaff"
                            :disabled="processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            {{ processing ? 'Deleting...' : 'Delete' }}
                        </button>
                        <button
                            @click="showDeleteModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ToastContainer from '@/Components/Notifications/ToastContainer.vue';
import { useNotifications } from '@/Composables/useNotifications';

const props = defineProps({
    staff: {
        type: Array,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
});

const notifications = useNotifications();
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        notifications.success(flash.success);
    }
    if (flash?.error) {
        notifications.error(flash.error);
    }
}, { deep: true, immediate: true });

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingStaff = ref(null);
const deletingStaff = ref(null);
const processing = ref(false);

const form = ref({
    name: '',
    email: '',
    username: '',
    password: '',
    role: 'staff',
    store_ids: [],
    store_roles: [],
});

const openCreateModal = () => {
    editingStaff.value = null;
    form.value = {
        name: '',
        email: '',
        username: '',
        password: '',
        role: 'staff',
        store_ids: [],
        store_roles: [],
    };
    showModal.value = true;
};

const openEditModal = (staff) => {
    editingStaff.value = staff;
    form.value = {
        name: staff.name,
        email: staff.email,
        username: staff.username,
        password: '',
        role: staff.role,
        store_ids: staff.stores.map(s => s.id),
        store_roles: staff.stores.map(s => s.role),
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingStaff.value = null;
};

const submitForm = () => {
    processing.value = true;

    if (editingStaff.value) {
        router.put(`/staff/${editingStaff.value.id}`, form.value, {
            onFinish: () => {
                processing.value = false;
                closeModal();
            },
        });
    } else {
        router.post('/staff', form.value, {
            onFinish: () => {
                processing.value = false;
                closeModal();
            },
        });
    }
};

const confirmDelete = (staff) => {
    deletingStaff.value = staff;
    showDeleteModal.value = true;
};

const deleteStaff = () => {
    processing.value = true;

    router.delete(`/staff/${deletingStaff.value.id}`, {
        onFinish: () => {
            processing.value = false;
            showDeleteModal.value = false;
            deletingStaff.value = null;
        },
    });
};

const getRoleBadgeClass = (role) => {
    const classes = {
        owner: 'bg-purple-100 text-purple-800',
        manager: 'bg-blue-100 text-blue-800',
        staff: 'bg-green-100 text-green-800',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
};
</script>
