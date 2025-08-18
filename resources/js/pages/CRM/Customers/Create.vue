<template>

    <Head title="Create Customer" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight">
                Create Customer
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
                                    <Select v-model="form.customer_type">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue placeholder="Select customer type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="individual">Individual</SelectItem>
                                            <SelectItem value="company">Company</SelectItem>
                                            <SelectItem value="government">Government</SelectItem>
                                        </SelectContent>
                                    </Select>
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
                                    Create Customer
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
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import type { CustomerForm } from '@/types/erp'

const form = useForm<CustomerForm>({
    name: '',
    customer_code: '',
    email: '',
    phone: '',
    company_name: '',
    customer_type: 'individual',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    tax_id: '',
    status: 'active'
})

const submit = (): void => {
    form.post(route('customers.store'))
}
</script>
