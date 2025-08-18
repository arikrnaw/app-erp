<template>

    <Head title="Edit Customer" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Edit Customer
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Customer Name -->
                                <div>
                                    <Label for="name">Customer Name</Label>
                                    <Input id="name" type="text" class="mt-1" v-model="form.name"
                                        placeholder="Enter customer name" required autofocus />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <!-- Customer Code -->
                                <div>
                                    <Label for="customer_code">Customer Code</Label>
                                    <Input id="customer_code" type="text" class="mt-1" v-model="form.customer_code"
                                        placeholder="Enter customer code" required />
                                    <InputError :message="form.errors.customer_code" class="mt-2" />
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

                                <!-- Company Name -->
                                <div>
                                    <Label for="company_name">Company Name</Label>
                                    <Input id="company_name" type="text" class="mt-1" v-model="form.company_name"
                                        placeholder="Enter company name" />
                                    <InputError :message="form.errors.company_name" class="mt-2" />
                                </div>

                                <!-- Customer Type -->
                                <div>
                                    <Label for="customer_type">Customer Type</Label>
                                    <select id="customer_type" v-model="form.customer_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required>
                                        <option value="individual">Individual</option>
                                        <option value="company">Company</option>
                                        <option value="government">Government</option>
                                    </select>
                                    <InputError :message="form.errors.customer_type" class="mt-2" />
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <Label for="address">Address</Label>
                                <Textarea id="address" v-model="form.address" rows="3"
                                    placeholder="Enter complete address" />
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <!-- City -->
                            <div>
                                <Label for="city">City</Label>
                                <Input id="city" type="text" class="mt-1" v-model="form.city"
                                    placeholder="Enter city" />
                                <InputError :message="form.errors.city" class="mt-2" />
                            </div>

                            <!-- State/Province -->
                            <div>
                                <Label for="state">State/Province</Label>
                                <Input id="state" type="text" class="mt-1" v-model="form.state"
                                    placeholder="Enter state or province" />
                                <InputError :message="form.errors.state" class="mt-2" />
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <Label for="postal_code">Postal Code</Label>
                                <Input id="postal_code" type="text" class="mt-1" v-model="form.postal_code"
                                    placeholder="Enter postal code" />
                                <InputError :message="form.errors.postal_code" class="mt-2" />
                            </div>

                            <!-- Country -->
                            <div>
                                <Label for="country">Country</Label>
                                <Input id="country" type="text" class="mt-1" v-model="form.country"
                                    placeholder="Enter country" />
                                <InputError :message="form.errors.country" class="mt-2" />
                            </div>

                            <!-- Tax ID -->
                            <div>
                                <Label for="tax_id">Tax ID</Label>
                                <Input id="tax_id" type="text" class="mt-1" v-model="form.tax_id"
                                    placeholder="Enter tax identification number" />
                                <InputError :message="form.errors.tax_id" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="mt-1">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('customers.index')">
                                <Button variant="outline" type="button" class="mr-2">
                                    Cancel
                                </Button>
                                </Link>
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                    type="submit">
                                    Update Customer
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

import type { Customer, CustomerForm } from '@/types/erp'

interface Props {
    customer: Customer
}

const props = defineProps<Props>()

const form = useForm({
    name: props.customer.name,
    customer_code: props.customer.customer_code,
    email: props.customer.email,
    phone: props.customer.phone,
    company_name: props.customer.company_name || '',
    customer_type: props.customer.customer_type,
    address: props.customer.address || '',
    city: props.customer.city || '',
    state: props.customer.state || '',
    postal_code: props.customer.postal_code || '',
    country: props.customer.country || '',
    tax_id: props.customer.tax_id || '',
    status: props.customer.status
})

const submit = (): void => {
    form.put(route('customers.update', props.customer.id))
}
</script>
