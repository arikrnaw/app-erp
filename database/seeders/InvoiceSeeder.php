<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Company;
use App\Models\User;

class InvoiceSeeder extends Seeder
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

        // Get some customers and products
        $customers = Customer::where('company_id', $company->id)->take(5)->get();
        $products = Product::where('company_id', $company->id)->take(10)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->error('Customers or Products not found. Please run CustomerSeeder and ProductSeeder first.');
            return;
        }

        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $referenceTypes = ['sales_order', 'manual', null];

        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $status = $statuses[array_rand($statuses)];
            $invoiceDate = now()->subDays(rand(1, 90));
            $dueDate = $invoiceDate->copy()->addDays(rand(15, 45));
            
            // Adjust status based on dates
            if ($dueDate < now() && $status !== 'paid') {
                $status = 'overdue';
            }

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => Invoice::generateInvoiceNumber($company->id),
                'customer_id' => $customer->id,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'reference_type' => $referenceTypes[array_rand($referenceTypes)],
                'reference_id' => rand(1, 100),
                'description' => 'Sample invoice for ' . $customer->name,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => $status === 'paid' ? 0 : rand(0, 1000),
                'balance_amount' => 0,
                'status' => $status,
                'notes' => 'Sample invoice notes',
                'created_by' => $user->id,
            ]);

            // Create invoice items
            $numItems = rand(1, 5);
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            for ($j = 1; $j <= $numItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $unitPrice = rand(100, 5000);
                $taxRate = rand(0, 11); // 0%, 5%, 10%, 11%
                $discountRate = rand(0, 20); // 0% to 20%

                $lineTotal = $quantity * $unitPrice;
                $discount = $lineTotal * $discountRate / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * $taxRate / 100;
                $total = $lineTotal - $discount + $tax;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'description' => $product->description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $tax,
                    'discount_rate' => $discountRate,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'line_number' => $j,
                ]);

                $subtotal += $lineTotal;
                $taxAmount += $tax;
                $discountAmount += $discount;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $paidAmount = $invoice->paid_amount;
            $balanceAmount = $totalAmount - $paidAmount;

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
            ]);
        }

        $this->command->info('Invoices seeded successfully!');
    }
}
