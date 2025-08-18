<template>

    <Head title="Edit Category" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Edit Category
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Category Name -->
                            <div>
                                <Label for="name">Category Name</Label>
                                <Input id="name" type="text" class="mt-1" v-model="form.name"
                                    placeholder="Enter category name" required autofocus />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <!-- Parent Category -->
                            <div>
                                <Label for="parent_id">Parent Category (Optional)</Label>
                                <select id="parent_id" v-model="form.parent_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">No Parent Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id"
                                        :disabled="category.id === props.category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.parent_id" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('categories.index')">
                                <Button variant="outline" type="button" class="mr-2">
                                    Cancel
                                </Button>
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Update Category
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { apiService } from '@/services/api'
import type { Category } from '@/types/erp'

interface Props {
    category: Category
}

const props = defineProps<Props>()

const categories = ref<Category[]>([])

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id || ''
})

const fetchCategories = async () => {
    try {
        const response = await apiService.getCategories()
        categories.value = response
    } catch (error) {
        console.error('Error fetching categories:', error)
    }
}

const submit = (): void => {
    form.put(route('categories.update', props.category.id))
}

onMounted(() => {
    fetchCategories()
})
</script>
