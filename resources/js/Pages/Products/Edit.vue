<template>
    <DashboardLayout :store="store" :user="user">
        <div class="max-w-3xl">
            <div class="mb-6">
                <Link :href="route('products.index')" class="text-blue-600 hover:text-blue-800">
                    ← Back to Products
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Product</h1>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Category
                        </label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                    </div>

                    <!-- Store Assignment -->
                    <div>
                        <label for="store_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Available In Store
                        </label>
                        <select
                            id="store_id"
                            v-model="form.store_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option :value="null">All Stores (Merchant-Wide)</option>
                            <option v-for="store in stores" :key="store.id" :value="store.id">
                                {{ store.name }}
                            </option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Choose "All Stores" to make this product available across all your store locations, or select a specific store.</p>
                        <p v-if="form.errors.store_id" class="mt-1 text-sm text-red-600">{{ form.errors.store_id }}</p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-600">$</span>
                            <input
                                id="price"
                                v-model.number="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>

                    <!-- Existing Images -->
                    <div v-if="product.images && product.images.length > 0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Current Images
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="image in product.images" :key="image.id" class="relative">
                                <img
                                    :src="`/storage/${image.image_path}`"
                                    class="w-full h-32 object-cover rounded-lg border border-gray-300"
                                    :class="{ 'ring-2 ring-blue-500': image.is_primary }"
                                />
                                <span v-if="image.is_primary" class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                    Primary
                                </span>
                                <button
                                    type="button"
                                    @click="markImageForDeletion(image.id)"
                                    :class="{ 'bg-gray-500': imagesToDelete.includes(image.id) }"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="imagesToDelete.length > 0" class="mt-2 text-sm text-amber-600">
                            {{ imagesToDelete.length }} image(s) marked for deletion
                        </p>
                    </div>

                    <!-- Add New Images -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Add New Images
                        </label>
                        <div class="mt-2">
                            <input
                                type="file"
                                @change="handleImageChange"
                                multiple
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="mt-1 text-sm text-gray-500">Upload up to 5 images total (JPEG, PNG, GIF, WebP - Max 2MB each)</p>
                        </div>
                        <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>

                        <!-- New Image Preview -->
                        <div v-if="imagePreviews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="(preview, index) in imagePreviews" :key="`new-${index}`" class="relative">
                                <img :src="preview" class="w-full h-32 object-cover rounded-lg border border-gray-300" />
                                <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                    New
                                </span>
                                <button
                                    type="button"
                                    @click="removeNewImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Shippable Status -->
                    <div class="border-t pt-6">
                        <div class="flex items-center mb-4">
                            <input
                                id="is_shippable"
                                v-model="form.is_shippable"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="is_shippable" class="ml-2 block text-sm font-medium text-gray-700">
                                This product is shippable
                            </label>
                        </div>

                        <!-- Shipping Dimensions (shown only if shippable) -->
                        <div v-if="form.is_shippable" class="space-y-4 pl-6 border-l-2 border-blue-200">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Shipping Information</h3>

                            <!-- Weight -->
                            <div>
                                <label for="weight_grams" class="block text-sm font-medium text-gray-700 mb-2">
                                    Weight (grams)
                                </label>
                                <input
                                    id="weight_grams"
                                    v-model.number="form.weight_grams"
                                    type="number"
                                    min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                                <p v-if="form.errors.weight_grams" class="mt-1 text-sm text-red-600">{{ form.errors.weight_grams }}</p>
                            </div>

                            <!-- Dimensions -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="length_cm" class="block text-sm font-medium text-gray-700 mb-2">
                                        Length (cm)
                                    </label>
                                    <input
                                        id="length_cm"
                                        v-model.number="form.length_cm"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p v-if="form.errors.length_cm" class="mt-1 text-sm text-red-600">{{ form.errors.length_cm }}</p>
                                </div>

                                <div>
                                    <label for="width_cm" class="block text-sm font-medium text-gray-700 mb-2">
                                        Width (cm)
                                    </label>
                                    <input
                                        id="width_cm"
                                        v-model.number="form.width_cm"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p v-if="form.errors.width_cm" class="mt-1 text-sm text-red-600">{{ form.errors.width_cm }}</p>
                                </div>

                                <div>
                                    <label for="height_cm" class="block text-sm font-medium text-gray-700 mb-2">
                                        Height (cm)
                                    </label>
                                    <input
                                        id="height_cm"
                                        v-model.number="form.height_cm"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p v-if="form.errors.height_cm" class="mt-1 text-sm text-red-600">{{ form.errors.height_cm }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Active
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('products.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    product: Object,
    categories: Array,
    stores: Array,
    store: Object,
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.product.name,
    description: props.product.description,
    category_id: props.product.category_id,
    store_id: props.product.store_id,
    price: props.product.price_cents / 100,
    is_active: props.product.is_active,
    is_shippable: props.product.is_shippable,
    weight_grams: props.product.weight_grams,
    length_cm: props.product.length_cm,
    width_cm: props.product.width_cm,
    height_cm: props.product.height_cm,
    images: [],
    delete_images: [],
});

const imagePreviews = ref([]);
const imagesToDelete = ref([]);

const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    const currentImageCount = (props.product.images?.length || 0) - imagesToDelete.value.length;
    const totalImages = currentImageCount + form.images.length + files.length;

    if (totalImages > 5) {
        alert('You can only have up to 5 images total');
        return;
    }

    form.images = [...form.images, ...files];

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeNewImage = (index) => {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const markImageForDeletion = (imageId) => {
    const index = imagesToDelete.value.indexOf(imageId);
    if (index > -1) {
        imagesToDelete.value.splice(index, 1);
    } else {
        imagesToDelete.value.push(imageId);
    }
    form.delete_images = imagesToDelete.value;
};

const submitForm = () => {
    // Use POST with method spoofing for file uploads (Laravel doesn't support files with PUT)
    form.post(route('products.update', props.product.id), {
        forceFormData: true,
    });
};
</script>
