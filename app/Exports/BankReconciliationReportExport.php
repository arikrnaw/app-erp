<?php

namespace App\Exports;

use App\Models\Finance\BankReconciliation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BankReconciliationReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $reconciliationId;
    protected $includeTransactions;
    protected $includeAdjustments;
    protected $includeNotes;

    public function __construct($reconciliationId, $includeTransactions = true, $includeAdjustments = true, $includeNotes = false)
    {
        $this->reconciliationId = $reconciliationId;
        $this->includeTransactions = $includeTransactions;
        $this->includeAdjustments = $includeAdjustments;
        $this->includeNotes = $includeNotes;
    }

    public function title(): string
    {
        return 'Bank Reconciliation Report';
    }

    public function collection()
    {
        $reconciliation = BankReconciliation::with([
            'bankAccount',
            'transactionMatches.bankTransaction',
            'transactionMatches.bookTransaction',
            'adjustments'
        ])->find($this->reconciliationId);

        if (!$reconciliation) {
            return collect([]);
        }

        $data = collect();

        // Add reconciliation summary
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Report ID',
            'Value' => $reconciliation->id
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Bank Account',
            'Value' => $reconciliation->bankAccount->name ?? 'N/A'
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Account Number',
            'Value' => $reconciliation->bankAccount->account_number ?? 'N/A'
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Period Start',
            'Value' => $reconciliation->period_start
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Period End',
            'Value' => $reconciliation->period_end
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Status',
            'Value' => ucfirst($reconciliation->status)
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Bank Statement Balance',
            'Value' => 'Rp ' . number_format($reconciliation->bank_statement_balance, 2)
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Book Balance',
            'Value' => 'Rp ' . number_format($reconciliation->book_balance, 2)
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Difference',
            'Value' => 'Rp ' . number_format($reconciliation->difference, 2)
        ]);
        $data->push([
            'Type' => 'RECONCILIATION_SUMMARY',
            'Field' => 'Generated Date',
            'Value' => $reconciliation->created_at->format('Y-m-d H:i:s')
        ]);

        // Add separator
        $data->push(['Type' => '', 'Field' => '', 'Value' => '']);

        // Add transaction matches if requested
        if ($this->includeTransactions && $reconciliation->transactionMatches && $reconciliation->transactionMatches->count() > 0) {
            $data->push([
                'Type' => 'TRANSACTION_MATCHES',
                'Field' => 'Total Matches: ' . $reconciliation->transactionMatches->count(),
                'Value' => ''
            ]);
            
            foreach ($reconciliation->transactionMatches as $match) {
                $data->push([
                    'Type' => 'TRANSACTION_MATCH',
                    'Field' => 'Match ID: ' . $match->id . ' (' . strtoupper($match->match_type) . ')',
                    'Value' => 'Score: ' . $match->match_score . '%'
                ]);
                $data->push([
                    'Type' => 'BANK_TRANSACTION',
                    'Field' => 'Description: ' . ($match->bankTransaction->description ?? 'No Description'),
                    'Value' => 'Amount: Rp ' . number_format($match->bankTransaction->amount ?? 0, 2)
                ]);
                $data->push([
                    'Type' => 'BOOK_TRANSACTION',
                    'Field' => 'Description: ' . ($match->bookTransaction->description ?? 'No Description'),
                    'Value' => 'Amount: Rp ' . number_format($match->bookTransaction->amount ?? 0, 2)
                ]);
                $data->push(['Type' => '', 'Field' => '', 'Value' => '']); // Empty row separator
            }
        }

        // Add adjustments if requested
        if ($this->includeAdjustments && $reconciliation->adjustments && $reconciliation->adjustments->count() > 0) {
            $data->push([
                'Type' => 'ADJUSTMENTS',
                'Field' => 'Total Adjustments: ' . $reconciliation->adjustments->count(),
                'Value' => ''
            ]);
            
            foreach ($reconciliation->adjustments as $adjustment) {
                $data->push([
                    'Type' => 'ADJUSTMENT',
                    'Field' => 'ID: ' . $adjustment->id . ' - ' . strtoupper($adjustment->type),
                    'Value' => 'Amount: Rp ' . number_format($adjustment->amount, 2)
                ]);
                $data->push([
                    'Type' => 'ADJUSTMENT_DETAILS',
                    'Field' => 'Description: ' . $adjustment->description,
                    'Value' => 'Date: ' . $adjustment->date
                ]);
                $data->push([
                    'Type' => 'ADJUSTMENT_STATUS',
                    'Field' => 'Reference: ' . ($adjustment->reference ?? 'N/A'),
                    'Value' => 'Approved: ' . ($adjustment->approved ? 'Yes' : 'No')
                ]);
                $data->push(['Type' => '', 'Field' => '', 'Value' => '']); // Empty row separator
            }
        }

        // Add notes if requested
        if ($this->includeNotes && $reconciliation->notes) {
            $data->push([
                'Type' => 'NOTES',
                'Field' => 'Notes',
                'Value' => $reconciliation->notes
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Type',
            'Field',
            'Value'
        ];
    }

    public function map($row): array
    {
        return [
            $row['Type'] ?? '',
            $row['Field'] ?? '',
            $row['Value'] ?? ''
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Type
            'B' => 50, // Field
            'C' => 30, // Value
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Type column styling
        $sheet->getStyle('A:A')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '2C3E50'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ECF0F1'],
            ],
        ]);

        // Border styling
        $sheet->getStyle('A1:C' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A1:C1');

        return $sheet;
    }
}
