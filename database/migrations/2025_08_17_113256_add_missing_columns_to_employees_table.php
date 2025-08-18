<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add missing columns as nullable first
            if (!Schema::hasColumn('employees', 'employee_number')) {
                $table->string('employee_number')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('employees', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('employees', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('employees', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('employees', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('employees', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('state');
            }
            if (!Schema::hasColumn('employees', 'country')) {
                $table->string('country')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('employees', 'national_id')) {
                $table->string('national_id')->nullable()->after('country');
            }
            if (!Schema::hasColumn('employees', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('national_id');
            }
            if (!Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('employees', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('employees', 'bank_routing_number')) {
                $table->string('bank_routing_number')->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            if (!Schema::hasColumn('employees', 'position_id')) {
                $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null')->after('department_id');
            }
            if (!Schema::hasColumn('employees', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null')->after('position_id');
            }
            if (!Schema::hasColumn('employees', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('hire_date');
            }
            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }
            if (!Schema::hasColumn('employees', 'employment_status')) {
                $table->enum('employment_status', ['active', 'inactive', 'terminated', 'resigned', 'retired'])->default('active')->after('contract_end_date');
            }
            if (!Schema::hasColumn('employees', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0)->after('employment_type');
            }
            if (!Schema::hasColumn('employees', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('base_salary');
            }
            if (!Schema::hasColumn('employees', 'pay_frequency')) {
                $table->enum('pay_frequency', ['weekly', 'bi_weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly')->after('currency');
            }
            if (!Schema::hasColumn('employees', 'start_time')) {
                $table->time('start_time')->nullable()->after('pay_frequency');
            }
            if (!Schema::hasColumn('employees', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('employees', 'working_hours_per_day')) {
                $table->decimal('working_hours_per_day', 4, 2)->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('employees', 'working_days_per_week')) {
                $table->integer('working_days_per_week')->nullable()->after('working_hours_per_day');
            }
            if (!Schema::hasColumn('employees', 'work_location')) {
                $table->string('work_location')->nullable()->after('working_days_per_week');
            }
            if (!Schema::hasColumn('employees', 'is_remote')) {
                $table->boolean('is_remote')->default(false)->after('work_location');
            }
            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_remote');
            }
            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('employees', 'position_id')) {
                $table->dropForeign(['position_id']);
            }
            if (Schema::hasColumn('employees', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
            }

            // Drop columns
            $columns = [
                'employee_number', 'birth_date', 'gender', 'city', 'state', 'postal_code', 'country',
                'national_id', 'tax_number', 'bank_name', 'bank_account_number', 'bank_routing_number',
                'emergency_contact_relationship', 'position_id', 'supervisor_id', 'contract_start_date',
                'contract_end_date', 'employment_status', 'base_salary', 'currency', 'pay_frequency',
                'start_time', 'end_time', 'working_hours_per_day', 'working_days_per_week',
                'work_location', 'is_remote', 'is_active', 'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
