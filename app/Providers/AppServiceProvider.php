<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Finance\Expense;
use App\Models\Finance\AssetPurchase;
use App\Models\Finance\CashTransaction;
use App\Observers\ExpenseObserver;
use App\Observers\AssetPurchaseObserver;
use App\Observers\CashTransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Finance module observers
        Expense::observe(ExpenseObserver::class);
        AssetPurchase::observe(AssetPurchaseObserver::class);
        CashTransaction::observe(CashTransactionObserver::class);
    }
}
