<template>

    <Head title="Create Supplier" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Create Supplier
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Supplier Name -->
                                <div>
                                    <Label for="name">Supplier Name</Label>
                                    <Input id="name" type="text" class="mt-1" v-model="form.name"
                                        placeholder="Enter supplier name" required autofocus />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <!-- Supplier Code -->
                                <div>
                                    <Label for="code">Supplier Code</Label>
                                    <Input id="code" type="text" class="mt-1" v-model="form.code"
                                        placeholder="Enter supplier code" required />
                                    <InputError :message="form.errors.code" class="mt-2" />
                                </div>

                                <!-- Email -->
                                <div>
                                    <Label for="email">Email</Label>
                                    <Input id="email" type="email" class="mt-1" v-model="form.email"
                                        placeholder="Enter email address" required />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>

                                <!-- Phone -->
                                <div>
                                    <Label for="phone">Phone</Label>
                                    <Input id="phone" type="tel" class="mt-1" v-model="form.phone"
                                        placeholder="Enter phone number" required />
                                    <InputError :message="form.errors.phone" class="mt-2" />
                                </div>

                                <!-- Contact Person -->
                                <div>
                                    <Label for="contact_person">Contact Person</Label>
                                    <Input id="contact_person" type="text" class="mt-1" v-model="form.contact_person"
                                        placeholder="Enter contact person name" required />
                                    <InputError :message="form.errors.contact_person" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div>
                                    <Label for="status">Status</Label>
                                    <select id="status" v-model="form.status"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <InputError :message="form.errors.status" class="mt-2" />
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <Label for="address">Address</Label>
                                <textarea id="address" v-model="form.address" rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Enter complete address" required></textarea>
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('suppliers.index')">
                                <Button variant="outline" type="button" class="mr-2">
                                    Cancel
                                </Button>
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Create Supplier
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
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const form = useForm({
    name: '',
    code: '',
    email: '',
    phone: '',
    address: '',
    contact_person: '',
    status: 'active'
})

const submit = (): void => {
    form.post(route('suppliers.store'))
}
</script>
