<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Finance\Currency;
use App\Models\Finance\ExchangeRate;
use App\Models\Finance\ExchangeRateHistory;
use App\Exports\MultiCurrencyExport;
use Maatwebsite\Excel\Facades\Excel;

class MultiCurrencyController extends Controller
{
    /**
     * Get multi-currency dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $overview = [
                'activeCurrencies' => Currency::where('is_active', true)->count(),
                'totalCurrencies' => Currency::count(),
                'baseCurrency' => Currency::where('is_base', true)->value('code') ?? 'USD',
                'baseCurrencyName' => Currency::where('is_base', true)->value('name') ?? 'US Dollar',
                'lastUpdate' => ExchangeRate::latest('updated_at')->value('updated_at') ?? 'Never',
                'nextUpdate' => now()->addDay()->format('Y-m-d H:i'),
                'foreignTransactions' => $this->getForeignTransactionCount()
            ];

            $currencies = Currency::select('id', 'code', 'name', 'symbol', 'is_base', 'is_active')
                ->orderBy('is_base', 'desc')
                ->orderBy('code')
                ->get();

            $activeCurrencies = Currency::where('is_active', true)
                ->select('id', 'code', 'name', 'symbol', 'decimal_places', 'is_base')
                ->orderBy('is_base', 'desc')
                ->orderBy('code')
                ->get();

            $exchangeRates = $this->getCurrentExchangeRates();

            $recentTransactions = $this->getRecentForeignTransactions();

            return response()->json([
                'overview' => $overview,
                'currencies' => $currencies,
                'activeCurrencies' => $activeCurrencies,
                'exchangeRates' => $exchangeRates,
                'recentTransactions' => $recentTransactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all currencies
     */
    public function getCurrencies(): JsonResponse
    {
        try {
            $currencies = Currency::orderBy('is_base', 'desc')
                ->orderBy('code')
                ->get();

            return response()->json([
                'currencies' => $currencies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading currencies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new currency
     */
    public function createCurrency(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:3|unique:currencies,code',
                'symbol' => 'required|string|max:5',
                'name' => 'required|string|max:100',
                'exchange_rate' => 'required|numeric|min:0.0001',
                'decimal_places' => 'required|integer|in:0,2,3,4',
                'description' => 'nullable|string|max:500',
                'auto_update' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create currency
            $currency = Currency::create([
                'code' => strtoupper($request->code),
                'symbol' => $request->symbol,
                'name' => $request->name,
                'description' => $request->description,
                'decimal_places' => $request->decimal_places,
                'is_base' => false,
                'is_active' => true,
                'auto_update' => $request->auto_update ?? false
            ]);

            // Create initial exchange rate
            $baseCurrency = Currency::where('is_base', true)->first();
            if ($baseCurrency) {
                ExchangeRate::create([
                    'base_currency_id' => $baseCurrency->id,
                    'target_currency_id' => $currency->id,
                    'rate' => $request->exchange_rate,
                    'effective_date' => now(),
                    'source' => 'manual',
                    'notes' => 'Initial exchange rate'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Currency created successfully',
                'currency' => $currency
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update currency
     */
    public function updateCurrency(Request $request, int $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);

            if ($currency->is_base) {
                return response()->json([
                    'message' => 'Cannot modify base currency'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'symbol' => 'sometimes|string|max:5',
                'name' => 'sometimes|string|max:100',
                'description' => 'nullable|string|max:500',
                'decimal_places' => 'sometimes|integer|in:0,2,3,4',
                'auto_update' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $currency->update($request->only([
                'symbol', 'name', 'description', 'decimal_places', 'auto_update'
            ]));

            return response()->json([
                'message' => 'Currency updated successfully',
                'currency' => $currency
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle currency status
     */
    public function toggleCurrencyStatus(int $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);

            if ($currency->is_base) {
                return response()->json([
                    'message' => 'Cannot deactivate base currency'
                ], 400);
            }

            $currency->update([
                'is_active' => !$currency->is_active
            ]);

            return response()->json([
                'message' => 'Currency status updated successfully',
                'currency' => $currency
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating currency status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current exchange rates
     */
    public function getExchangeRates(): JsonResponse
    {
        try {
            $rates = $this->getCurrentExchangeRates();

            return response()->json([
                'exchangeRates' => $rates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading exchange rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate(Request $request, int $id): JsonResponse
    {
        try {
            $rate = ExchangeRate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'rate' => 'required|numeric|min:0.0001',
                'effective_date' => 'required|date',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create new rate record
            ExchangeRate::create([
                'base_currency_id' => $rate->base_currency_id,
                'target_currency_id' => $rate->target_currency_id,
                'rate' => $request->rate,
                'effective_date' => $request->effective_date,
                'source' => 'manual',
                'notes' => $request->notes
            ]);

            // Update current rate
            $rate->update([
                'rate' => $request->rate,
                'notes' => $request->notes
            ]);

            return response()->json([
                'message' => 'Exchange rate updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating exchange rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh exchange rates from external API
     */
    public function refreshExchangeRates(): JsonResponse
    {
        try {
            $currencies = Currency::where('auto_update', true)
                ->where('is_active', true)
                ->get();

            $baseCurrency = Currency::where('is_base', true)->first();
            if (!$baseCurrency) {
                return response()->json([
                    'message' => 'No base currency found'
                ], 400);
            }

            $updatedCount = 0;
            foreach ($currencies as $currency) {
                if ($currency->id === $baseCurrency->id) {
                    continue;
                }

                $rate = $this->fetchExternalExchangeRate($baseCurrency->code, $currency->code);
                if ($rate) {
                    ExchangeRate::updateOrCreate(
                        [
                            'base_currency_id' => $baseCurrency->id,
                            'target_currency_id' => $currency->id
                        ],
                        [
                            'rate' => $rate,
                            'effective_date' => now(),
                            'source' => 'api',
                            'notes' => 'Auto-updated from external API'
                        ]
                    );
                    $updatedCount++;
                }
            }

            return response()->json([
                'message' => "Updated {$updatedCount} exchange rates",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error refreshing exchange rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rate history
     */
    public function getExchangeRateHistory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'base_currency' => 'required|string|exists:currencies,code',
                'target_currency' => 'required|string|exists:currencies,code',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'search' => 'nullable|string|max:100',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $baseCurrency = Currency::where('code', $request->base_currency)->first();
            $targetCurrency = Currency::where('code', $request->target_currency)->first();

            $query = ExchangeRateHistory::where('base_currency_id', $baseCurrency->id)
                ->where('target_currency_id', $targetCurrency->id)
                ->with(['baseCurrency', 'targetCurrency']);

            if ($request->date_from) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->where('date', '<=', $request->date_to);
            }

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('notes', 'like', "%{$request->search}%")
                      ->orWhere('source', 'like', "%{$request->search}%");
                });
            }

            $history = $query->orderBy('date', 'desc')
                ->paginate($request->per_page ?? 25);

            $chartData = $this->getChartData($baseCurrency->id, $targetCurrency->id, $request->date_from, $request->date_to);
            $statistics = $this->calculateStatistics($baseCurrency->id, $targetCurrency->id, $request->date_from, $request->date_to);

            return response()->json([
                'history' => $history,
                'chartData' => $chartData,
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading exchange rate history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export exchange rate history
     */
    public function exportHistory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'base_currency' => 'required|string|exists:currencies,code',
                'target_currency' => 'required|string|exists:currencies,code',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'format' => 'required|in:excel,pdf'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $baseCurrency = Currency::where('code', $request->base_currency)->first();
            $targetCurrency = Currency::where('code', $request->target_currency)->first();

            $query = ExchangeRateHistory::where('base_currency_id', $baseCurrency->id)
                ->where('target_currency_id', $targetCurrency->id)
                ->with(['baseCurrency', 'targetCurrency']);

            if ($request->date_from) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->where('date', '<=', $request->date_to);
            }

            $data = $query->orderBy('date', 'desc')->get();

            if ($request->format === 'excel') {
                return Excel::download(
                    new MultiCurrencyExport($data, $baseCurrency, $targetCurrency),
                    "exchange-rate-history-{$request->base_currency}-{$request->target_currency}.xlsx"
                );
            }

            return response()->json([
                'message' => 'Export completed',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error exporting data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get foreign transaction count for current month
     */
    private function getForeignTransactionCount(): int
    {
        // This would integrate with your transaction system
        // For now, return a placeholder
        return DB::table('journal_entries')
            ->where('currency_id', '!=', Currency::where('is_base', true)->value('id'))
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();
    }

    /**
     * Get current exchange rates with change calculations
     */
    private function getCurrentExchangeRates(): array
    {
        $baseCurrency = Currency::where('is_base', true)->first();
        if (!$baseCurrency) {
            return [];
        }

        $rates = ExchangeRate::where('base_currency_id', $baseCurrency->id)
            ->with(['targetCurrency'])
            ->get();

        return $rates->map(function ($rate) {
            // Calculate change from previous rate
            $previousRate = ExchangeRateHistory::where('base_currency_id', $rate->base_currency_id)
                ->where('target_currency_id', $rate->target_currency_id)
                ->where('date', '<', $rate->effective_date)
                ->orderBy('date', 'desc')
                ->first();

            $change = 0;
            $changePercentage = 0;

            if ($previousRate) {
                $change = $rate->rate - $previousRate->rate;
                $changePercentage = ($change / $previousRate->rate) * 100;
            }

            return [
                'id' => $rate->id,
                'currency_code' => $rate->targetCurrency->code,
                'currency_name' => $rate->targetCurrency->name,
                'symbol' => $rate->targetCurrency->symbol,
                'rate' => $rate->rate,
                'change' => $change,
                'change_percentage' => $changePercentage,
                'updated_at' => $rate->updated_at,
                'effective_date' => $rate->effective_date
            ];
        })->toArray();
    }

    /**
     * Get recent foreign transactions
     */
    private function getRecentForeignTransactions(): array
    {
        // This would integrate with your transaction system
        // For now, return placeholder data
        return [];
    }

    /**
     * Fetch exchange rate from external API
     */
    private function fetchExternalExchangeRate(string $from, string $to): ?float
    {
        try {
            // Example using a free exchange rate API
            // You would replace this with your preferred provider
            $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$from}");
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['rates'][$to] ?? null;
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching exchange rate: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Get chart data for exchange rate history
     */
    private function getChartData(int $baseCurrencyId, int $targetCurrencyId, ?string $dateFrom, ?string $dateTo): array
    {
        $query = ExchangeRateHistory::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId);

        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        return $query->orderBy('date')
            ->get(['date', 'rate'])
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'rate' => $item->rate
                ];
            })
            ->toArray();
    }

    /**
     * Calculate statistics for exchange rate history
     */
    private function calculateStatistics(int $baseCurrencyId, int $targetCurrencyId, ?string $dateFrom, ?string $dateTo): array
    {
        $query = ExchangeRateHistory::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId);

        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $rates = $query->pluck('rate')->toArray();

        if (empty($rates)) {
            return [
                'currentRate' => '0.0000',
                'highestRate' => '0.0000',
                'highestDate' => '',
                'lowestRate' => '0.0000',
                'lowestDate' => '',
                'averageRate' => '0.0000',
                'volatility' => '0.0000',
                'stability' => '0.00%'
            ];
        }

        $currentRate = $rates[0];
        $highestRate = max($rates);
        $lowestRate = min($rates);
        $averageRate = array_sum($rates) / count($rates);

        // Calculate volatility (standard deviation)
        $variance = array_sum(array_map(function ($rate) use ($averageRate) {
            return pow($rate - $averageRate, 2);
        }, $rates)) / count($rates);
        $volatility = sqrt($variance);

        // Calculate stability (percentage of rates with < 1% change)
        $stableCount = 0;
        for ($i = 1; $i < count($rates); $i++) {
            $change = abs(($rates[$i] - $rates[$i-1]) / $rates[$i-1]) * 100;
            if ($change < 1) {
                $stableCount++;
            }
        }
        $stability = count($rates) > 1 ? ($stableCount / (count($rates) - 1)) * 100 : 0;

        // Get dates for highest and lowest rates
        $highestDate = ExchangeRateHistory::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->where('rate', $highestRate)
            ->value('date');

        $lowestDate = ExchangeRateHistory::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->where('rate', $lowestRate)
            ->value('date');

        return [
            'currentRate' => number_format($currentRate, 4),
            'highestRate' => number_format($highestRate, 4),
            'highestDate' => $highestDate,
            'lowestRate' => number_format($lowestRate, 4),
            'lowestDate' => $lowestDate,
            'averageRate' => number_format($averageRate, 4),
            'volatility' => number_format($volatility, 4),
            'stability' => number_format($stability, 2) . '%'
        ];
    }
}
