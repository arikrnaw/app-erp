<?php

namespace App\Services;

use App\Models\Finance\BankReconciliation;
use Barryvdh\DomPDF\Facade\Pdf;

class BankReconciliationPdfService
{
    public function generatePdf(BankReconciliation $reconciliation, $includeTransactions = true, $includeAdjustments = true, $includeNotes = false)
    {
        $data = [
            'reconciliation' => $reconciliation->load([
                'bankAccount',
                'transactionMatches.bankTransaction',
                'transactionMatches.bookTransaction',
                'adjustments'
            ]),
            'includeTransactions' => $includeTransactions,
            'includeAdjustments' => $includeAdjustments,
            'includeNotes' => $includeNotes,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ];

        $pdf = Pdf::loadView('exports.bank-reconciliation-pdf', $data);
        
        return $pdf->download("reconciliation-report-{$reconciliation->id}.pdf");
    }
}
