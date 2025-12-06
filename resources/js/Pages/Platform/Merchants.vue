<template>
    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">All Merchants</h1>
                    <p class="text-sm text-gray-400">Manage all merchants in the system</p>
                </div>
                <div class="flex items-center space-x-4">
                    <Link :href="route('platform.dashboard')" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Dashboard
                    </Link>
                    <Link :href="route('platform.merchants.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Create Merchant
                    </Link>
                    <form @submit.prevent="logout" class="inline">
                        <button type="submit" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded">
                {{ $page.props.flash.success }}
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search -->
            <div class="mb-6">
                <input
                    v-model="searchQuery"
                    @input="debouncedSearch"
                    type="text"
                    placeholder="Search merchants by name, slug, or owner email..."
                    class="w-full px-4 py-3 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Merchants Table -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-750">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Merchant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Owner</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stores</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="merchant in merchants.data" :key="merchant.id" class="hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-white">{{ merchant.name }}</div>
                                <div class="text-sm text-gray-400">{{ merchant.slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="merchant.owner" class="text-sm text-gray-300">
                                    <div>{{ merchant.owner.name }}</div>
                                    <div class="text-gray-400">{{ merchant.owner.email }}</div>
                                </div>
                                <div v-else class="text-sm text-gray-500">No owner</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ merchant.stores_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ merchant.users_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ formatDate(merchant.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="route('platform.merchants.show', merchant.id)" class="text-blue-400 hover:text-blue-300">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="merchants.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No merchants found.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="merchants.data.length > 0" class="bg-gray-750 px-4 py-3 flex items-center justify-between border-t border-gray-700">
                    <div class="text-sm text-gray-400">
                        Showing {{ merchants.from }} to {{ merchants.to }} of {{ merchants.total }} results
                    </div>
                    <div class="flex gap-2">
                        <template v-for="link in merchants.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 rounded text-sm',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                                ]"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-1 rounded text-sm bg-gray-700 text-gray-500 opacity-50 cursor-not-allowed"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    merchants: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('platform.merchants'), { search: searchQuery.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

const logout = () => {
    router.post(route('platform.logout'));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
