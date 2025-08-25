<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Finance\Currency;
use App\Models\Finance\ExchangeRate;
use Carbon\Carbon;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create base currency (USD)
        $usd = Currency::create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'description' => 'United States Dollar - Base Currency',
            'decimal_places' => 2,
            'is_base' => true,
            'is_active' => true,
            'auto_update' => true,
            'exchange_rate_source' => 'api',
            'last_update' => now(),
            'next_update' => now()->addDay()
        ]);

        // Create other major currencies
        $currencies = [
            [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => 'Euro',
                'description' => 'European Euro',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'GBP',
                'symbol' => '£',
                'name' => 'British Pound',
                'description' => 'British Pound Sterling',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'JPY',
                'symbol' => '¥',
                'name' => 'Japanese Yen',
                'description' => 'Japanese Yen',
                'decimal_places' => 0,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'AUD',
                'symbol' => 'A$',
                'name' => 'Australian Dollar',
                'description' => 'Australian Dollar',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'CAD',
                'symbol' => 'C$',
                'name' => 'Canadian Dollar',
                'description' => 'Canadian Dollar',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'CHF',
                'symbol' => 'CHF',
                'name' => 'Swiss Franc',
                'description' => 'Swiss Franc',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'CNY',
                'symbol' => '¥',
                'name' => 'Chinese Yuan',
                'description' => 'Chinese Yuan Renminbi',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'INR',
                'symbol' => '₹',
                'name' => 'Indian Rupee',
                'description' => 'Indian Rupee',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'SGD',
                'symbol' => 'S$',
                'name' => 'Singapore Dollar',
                'description' => 'Singapore Dollar',
                'decimal_places' => 2,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ],
            [
                'code' => 'IDR',
                'symbol' => 'Rp',
                'name' => 'Indonesian Rupiah',
                'description' => 'Indonesian Rupiah',
                'decimal_places' => 0,
                'auto_update' => true,
                'exchange_rate_source' => 'api'
            ]
        ];

        foreach ($currencies as $currencyData) {
            $currency = Currency::create($currencyData);

            // Create initial exchange rates (sample rates - these would normally come from API)
            $sampleRates = [
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.50,
                'AUD' => 1.35,
                'CAD' => 1.25,
                'CHF' => 0.92,
                'CNY' => 6.45,
                'INR' => 74.50,
                'SGD' => 1.35,
                'IDR' => 14250.00
            ];

            if (isset($sampleRates[$currency->code])) {
                ExchangeRate::create([
                    'base_currency_id' => $usd->id,
                    'target_currency_id' => $currency->id,
                    'rate' => $sampleRates[$currency->code],
                    'effective_date' => now(),
                    'source' => 'seeder',
                    'notes' => 'Initial exchange rate from seeder'
                ]);
            }
        }

        $this->command->info('Currencies seeded successfully!');
        $this->command->info('Created ' . (count($currencies) + 1) . ' currencies with sample exchange rates.');
    }
}
