<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number')->unique();
            $table->text('description')->nullable();
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->string('swift_code', 11)->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('currency', 3);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_date')->nullable();
            $table->enum('account_type', ['checking', 'savings', 'time_deposit', 'investment']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('reconcile_automatically')->default(false);
            $table->boolean('allow_overdraft')->default(false);
            $table->boolean('include_in_cash_flow')->default(true);
            $table->text('notes')->nullable();
            $table->date('last_reconciled_date')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
