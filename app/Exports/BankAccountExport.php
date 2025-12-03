<?php

namespace App\Exports;

use App\Models\Finance\BankAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BankAccountExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BankAccount::query();

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['account_type']) && $this->filters['account_type'] !== 'all') {
            $query->where('account_type', $this->filters['account_type']);
        }

        if (isset($this->filters['search']) && $this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('bank_name')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Account Name',
            'Account Number',
            'Bank Name',
            'Bank Branch',
            'Account Type',
            'Currency',
            'Opening Balance',
            'Current Balance',
            'Status',
            'Auto Reconcile',
            'Allow Overdraft',
            'Include in Cash Flow',
            'Swift Code',
            'IBAN',
            'Opening Date',
            'Description',
            'Notes',
            'Last Reconciled Date'
        ];
    }

    public function map($bankAccount): array
    {
        return [
            $bankAccount->name,
            $bankAccount->account_number,
            $bankAccount->bank_name,
            $bankAccount->bank_branch,
            $bankAccount->account_type_label,
            $bankAccount->currency,
            $bankAccount->opening_balance,
            $bankAccount->balance,
            $bankAccount->status_label,
            $bankAccount->reconcile_automatically ? 'Yes' : 'No',
            $bankAccount->allow_overdraft ? 'Yes' : 'No',
            $bankAccount->include_in_cash_flow ? 'Yes' : 'No',
            $bankAccount->swift_code,
            $bankAccount->iban,
            $bankAccount->opening_date ? $bankAccount->opening_date->format('Y-m-d') : '',
            $bankAccount->description,
            $bankAccount->notes,
            $bankAccount->last_reconciled_date ? $bankAccount->last_reconciled_date->format('Y-m-d') : ''
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Account Name
            'B' => 18, // Account Number
            'C' => 20, // Bank Name
            'D' => 18, // Bank Branch
            'E' => 15, // Account Type
            'F' => 10, // Currency
            'G' => 15, // Opening Balance
            'H' => 15, // Current Balance
            'I' => 10, // Status
            'J' => 15, // Auto Reconcile
            'K' => 15, // Allow Overdraft
            'L' => 20, // Include in Cash Flow
            'M' => 12, // Swift Code
            'N' => 20, // IBAN
            'O' => 15, // Opening Date
            'P' => 30, // Description
            'Q' => 30, // Notes
            'R' => 20, // Last Reconciled Date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Apply borders to all cells with data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Auto-fit row height
                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
