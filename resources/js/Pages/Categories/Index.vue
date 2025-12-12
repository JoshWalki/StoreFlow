<template>
    <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Categories</h1>
                <Link
                    :href="route('categories.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                >
                    Add Category
                </Link>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="flex gap-4">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search categories..."
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        @input="debouncedSearch"
                    />
                </div>
            </div>

            <!-- Categories Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-10"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Products
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="(category, index) in localCategories"
                            :key="category.id"
                            draggable="true"
                            @dragstart="handleDragStart(index, $event)"
                            @dragover.prevent="handleDragOver(index, $event)"
                            @drop="handleDrop(index)"
                            @dragend="handleDragEnd"
                            @dragleave="handleDragLeave"
                            :class="{
                                'opacity-40': draggedIndex === index,
                                'border-t-4 border-t-blue-500': dropPosition === 'before' && dragOverIndex === index,
                                'border-b-4 border-b-blue-500': dropPosition === 'after' && dragOverIndex === index
                            }"
                            class="transition-all cursor-move hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="px-4 py-4 text-center">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
                                </svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ category.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400 max-w-md truncate">{{ category.description || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ category.products_count }} products</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="category.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'"
                                >
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <Link
                                    :href="route('categories.edit', category.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteCategory(category)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <tr v-if="categories.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No categories found. Create your first category to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="categories.links.length > 3" class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="categories.prev_page_url"
                            :href="categories.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="categories.next_page_url"
                            :href="categories.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ categories.from }}</span>
                                to
                                <span class="font-medium">{{ categories.to }}</span>
                                of
                                <span class="font-medium">{{ categories.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in categories.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                        link.active
                                            ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-600 text-blue-600 dark:text-blue-200'
                                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === categories.links.length - 1 ? 'rounded-r-md' : '',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    categories: Object,
    filters: Object,
    store: Object,
    user: Object,
});

const searchForm = reactive({
    search: props.filters.search || '',
});

// Local copy of categories for drag and drop
const localCategories = ref([...props.categories.data]);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);
const dropPosition = ref(null); // 'before' or 'after'

// Update local categories when props change
watch(() => props.categories.data, (newData) => {
    localCategories.value = [...newData];
}, { deep: true });

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('categories.index'), { search: searchForm.search }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

const deleteCategory = (category) => {
    if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
        router.delete(route('categories.destroy', category.id));
    }
};

// Drag and drop handlers
const handleDragStart = (index, event) => {
    draggedIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
};

const handleDragOver = (index, event) => {
    if (draggedIndex.value === index) {
        return;
    }

    // Calculate if mouse is in top or bottom half of the row
    const rect = event.currentTarget.getBoundingClientRect();
    const mouseY = event.clientY;
    const rowMiddle = rect.top + rect.height / 2;

    dragOverIndex.value = index;

    // Determine if we should insert before or after this row
    if (mouseY < rowMiddle) {
        dropPosition.value = 'before';
    } else {
        dropPosition.value = 'after';
    }
};

const handleDragLeave = () => {
    // Keep the indicator visible, don't clear on leave
};

const handleDrop = (dropIndex) => {
    if (draggedIndex.value === null) {
        return;
    }

    let targetIndex = dropIndex;

    // Adjust target index based on drop position
    if (dropPosition.value === 'after') {
        targetIndex = dropIndex + 1;
    }

    // Adjust if dragging from before the target
    if (draggedIndex.value < targetIndex) {
        targetIndex--;
    }

    // Don't do anything if dropping in same position
    if (draggedIndex.value === targetIndex) {
        draggedIndex.value = null;
        dragOverIndex.value = null;
        dropPosition.value = null;
        return;
    }

    // Reorder the array
    const items = [...localCategories.value];
    const draggedItem = items[draggedIndex.value];
    items.splice(draggedIndex.value, 1);
    items.splice(targetIndex, 0, draggedItem);

    localCategories.value = items;

    // Update sort order on backend
    updateSortOrder(items);

    draggedIndex.value = null;
    dragOverIndex.value = null;
    dropPosition.value = null;
};

const handleDragEnd = () => {
    draggedIndex.value = null;
    dragOverIndex.value = null;
    dropPosition.value = null;
};

const updateSortOrder = (categories) => {
    // Create array of category IDs in new order
    const sortedIds = categories.map(cat => cat.id);

    router.put(route('categories.reorder'), {
        category_ids: sortedIds,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
