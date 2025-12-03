<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BankAccountTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function array(): array
    {
        return [
            [
                'Sample Account',
                '1234567890',
                'Bank Example',
                'Main Branch',
                'checking',
                'IDR',
                '1000000',
                'active',
                'Yes',
                'No',
                'Yes',
                'EXAMIDJA',
                'ID12345678901234567890',
                '2024-01-01',
                'Sample bank account',
                'Sample notes'
            ]
        ];
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
            'Status',
            'Auto Reconcile',
            'Allow Overdraft',
            'Include in Cash Flow',
            'Swift Code',
            'IBAN',
            'Opening Date',
            'Description',
            'Notes'
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
            'H' => 10, // Status
            'I' => 15, // Auto Reconcile
            'J' => 15, // Allow Overdraft
            'K' => 20, // Include in Cash Flow
            'L' => 12, // Swift Code
            'M' => 20, // IBAN
            'N' => 15, // Opening Date
            'O' => 30, // Description
            'P' => 30, // Notes
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
