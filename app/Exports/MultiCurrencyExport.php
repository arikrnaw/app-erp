<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use App\Models\Finance\Currency;
use App\Models\Finance\ExchangeRateHistory;

class MultiCurrencyExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;
    protected $baseCurrency;
    protected $targetCurrency;

    public function __construct($data, Currency $baseCurrency, Currency $targetCurrency)
    {
        $this->data = $data;
        $this->baseCurrency = $baseCurrency;
        $this->targetCurrency = $targetCurrency;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Exchange Rate',
            'Change',
            'Change %',
            'Volume',
            'Source',
            'Notes'
        ];
    }

    public function map($row): array
    {
        return [
            $row->date,
            number_format($row->rate, 4),
            $row->change ? number_format($row->change, 4) : '-',
            $row->change_percentage ? number_format($row->change_percentage, 4) . '%' : '-',
            $row->volume ? number_format($row->volume, 2) : '-',
            ucfirst($row->source),
            $row->notes ?? '-'
        ];
    }

    public function title(): string
    {
        return "Exchange Rate History - {$this->baseCurrency->code} to {$this->targetCurrency->code}";
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Date
            'B' => 15, // Exchange Rate
            'C' => 15, // Change
            'D' => 15, // Change %
            'E' => 15, // Volume
            'F' => 15, // Source
            'G' => 40, // Notes
        ];
    }

    public function styles($sheet)
    {
        // Header styles
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data row styles
        $dataRows = $sheet->getHighestRow();
        if ($dataRows > 1) {
            $sheet->getStyle("A2:G{$dataRows}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($row = 2; $row <= $dataRows; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:G{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(['rgb' => 'F9FAFB']);
                }
            }

            // Number formatting for specific columns
            $sheet->getStyle("B2:B{$dataRows}")->getNumberFormat()->setFormatCode('#,##0.0000');
            $sheet->getStyle("C2:C{$dataRows}")->getNumberFormat()->setFormatCode('#,##0.0000');
            $sheet->getStyle("D2:D{$dataRows}")->getNumberFormat()->setFormatCode('#,##0.0000%');
            $sheet->getStyle("E2:E{$dataRows}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        return $sheet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Add summary information
                $highestRow = $sheet->getHighestRow();
                $summaryRow = $highestRow + 3;
                
                // Add title
                $sheet->setCellValue("A{$summaryRow}", 'Summary Information');
                $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true)->setSize(14);
                $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
                
                // Add currency pair info
                $summaryRow += 2;
                $sheet->setCellValue("A{$summaryRow}", 'Currency Pair:');
                $sheet->setCellValue("B{$summaryRow}", "{$this->baseCurrency->code} to {$this->targetCurrency->code}");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
                
                // Add export date
                $summaryRow += 1;
                $sheet->setCellValue("A{$summaryRow}", 'Export Date:');
                $sheet->setCellValue("B{$summaryRow}", now()->format('Y-m-d H:i:s'));
                $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
                
                // Add data range
                $summaryRow += 1;
                $sheet->setCellValue("A{$summaryRow}", 'Data Range:');
                $sheet->setCellValue("B{$summaryRow}", "{$this->data->first()->date} to {$this->data->last()->date}");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
                
                // Add total records
                $summaryRow += 1;
                $sheet->setCellValue("A{$summaryRow}", 'Total Records:');
                $sheet->setCellValue("B{$summaryRow}", $this->data->count());
                $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
                
                // Style summary section
                $summaryStartRow = $highestRow + 3;
                $summaryEndRow = $summaryRow;
                $sheet->getStyle("A{$summaryStartRow}:G{$summaryEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '3B82F6'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EFF6FF'],
                    ],
                ]);
                
                // Auto-adjust column widths
                foreach (range('A', 'G') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
