<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-2xl">
            <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Select a Store</h1>
                    <p class="text-gray-600 mt-2">Choose which store you want to access</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        v-for="store in stores"
                        :key="store.id"
                        @click="selectStore(store.id)"
                        class="border-2 border-gray-300 rounded-lg p-6 hover:border-blue-500 hover:bg-blue-50 transition-colors text-left"
                        :disabled="form.processing"
                    >
                        <h3 class="text-lg font-semibold text-gray-800">{{ store.name }}</h3>
                        <p v-if="store.description" class="text-gray-600 text-sm mt-2">
                            {{ store.description }}
                        </p>
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <button
                        @click="logout"
                        class="text-gray-600 hover:text-gray-800 text-sm"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    stores: Array,
});

const form = useForm({
    store_id: null,
});

const selectStore = (storeId) => {
    form.store_id = storeId;
    form.post(route('store-selection'));
};

const logout = () => {
    router.post(route('logout'));
};
</script>
