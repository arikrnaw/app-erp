<?php

namespace App\Imports;

use App\Models\Finance\BankAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;

class BankAccountImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $currentRowIndex = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $this->currentRowIndex = $index + 2; // +2 because we start from row 2 (after header)
            
            try {
                // Prepare data for validation
                $data = [
                    'account_name' => (string) $row['account_name'],
                    'account_number' => (string) $row['account_number'],
                    'bank_name' => (string) $row['bank_name'],
                    'account_type' => (string) $row['account_type'],
                    'currency' => (string) $row['currency'],
                    'opening_balance' => $this->parseNumeric($row['opening_balance'] ?? 0),
                    'status' => $row['status'] ? (string) $row['status'] : 'active',
                ];

                // Validate required fields
                $validator = Validator::make($data, [
                    'account_name' => 'required|string|max:255',
                    'account_number' => 'required|string|max:100|unique:bank_accounts,account_number',
                    'bank_name' => 'required|string|max:255',
                    'account_type' => 'required|in:checking,savings,time_deposit,investment',
                    'currency' => 'required|string|max:3',
                    'opening_balance' => 'nullable|numeric|min:0',
                    'status' => 'nullable|in:active,inactive',
                ]);

                if ($validator->fails()) {
                    $this->skippedCount++;
                    $this->errors[] = "Row " . $this->currentRowIndex . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Check if account number already exists
                if (BankAccount::where('account_number', $data['account_number'])->exists()) {
                    $this->skippedCount++;
                    $this->errors[] = "Row " . $this->currentRowIndex . ": Account number already exists";
                    continue;
                }

                // Create bank account
                BankAccount::create([
                    'name' => $data['account_name'],
                    'account_number' => $data['account_number'],
                    'description' => $row['description'] ? (string) $row['description'] : null,
                    'bank_name' => $data['bank_name'],
                    'bank_branch' => $row['bank_branch'] ? (string) $row['bank_branch'] : null,
                    'swift_code' => $row['swift_code'] ? (string) $row['swift_code'] : null,
                    'iban' => $row['iban'] ? (string) $row['iban'] : null,
                    'currency' => $data['currency'],
                    'opening_balance' => $data['opening_balance'],
                    'opening_date' => $this->parseDate($row['opening_date'] ?? null),
                    'account_type' => $data['account_type'],
                    'status' => $data['status'],
                    'reconcile_automatically' => $this->parseBoolean($row['auto_reconcile'] ?? 'No'),
                    'allow_overdraft' => $this->parseBoolean($row['allow_overdraft'] ?? 'No'),
                    'include_in_cash_flow' => $this->parseBoolean($row['include_in_cash_flow'] ?? 'Yes'),
                    'notes' => $row['notes'] ? (string) $row['notes'] : null,
                    'balance' => $data['opening_balance'],
                ]);

                $this->importedCount++;

            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = "Row " . $this->currentRowIndex . ": " . $e->getMessage();
            }
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }


    protected function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Try different date formats
            if (is_numeric($date)) {
                // Excel date serial number
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($date - 2);
            }
            
            // Try common date formats
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $date);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Try Carbon's parse
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', '1', 'y', 'ya']);
    }

    protected function parseNumeric($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove any non-numeric characters except decimal point and minus sign
        $cleaned = preg_replace('/[^0-9.-]/', '', (string) $value);
        
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return 0;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
