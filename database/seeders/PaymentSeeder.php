<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company and user
        $company = Company::first();
        $user = User::first();

        if (!$company || !$user) {
            $this->command->error('Company or User not found. Please run CompanySeeder and UserSeeder first.');
            return;
        }

        // Get some customers and invoices
        $customers = Customer::where('company_id', $company->id)->take(5)->get();
        $invoices = Invoice::where('company_id', $company->id)->take(10)->get();

        if ($customers->isEmpty()) {
            $this->command->error('Customers not found. Please run CustomerSeeder first.');
            return;
        }

        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'check', 'other'];
        $statuses = ['pending', 'completed', 'cancelled'];

        for ($i = 1; $i <= 30; $i++) {
            $customer = $customers->random();
            $invoice = $invoices->isNotEmpty() ? $invoices->random() : null;
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $status = $statuses[array_rand($statuses)];
            $paymentDate = now()->subDays(rand(1, 90));
            
            // Generate amount based on invoice or random amount
            $amount = $invoice ? min($invoice->balance_amount, rand(100000, 5000000)) : rand(100000, 5000000);

            Payment::create([
                'company_id' => $company->id,
                'payment_number' => Payment::generatePaymentNumber($company->id),
                'customer_id' => $customer->id,
                'invoice_id' => $invoice ? $invoice->id : null,
                'payment_date' => $paymentDate->toDateString(),
                'payment_method' => $paymentMethod,
                'reference_number' => $paymentMethod === 'check' ? 'CHK-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : 
                                   ($paymentMethod === 'bank_transfer' ? 'TRX-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) : null),
                'amount' => $amount,
                'notes' => 'Sample payment for ' . $customer->name,
                'status' => $status,
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('Payments seeded successfully!');
    }
}
