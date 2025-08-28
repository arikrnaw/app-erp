<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Reconciliation Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .info-value {
            color: #34495e;
        }
        .balances {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .balance-item {
            text-align: center;
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
        }
        .balance-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .balance-value {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
        .difference {
            color: #e74c3c;
        }
        .transactions, .adjustments {
            margin-bottom: 20px;
        }
        .item {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .item-type {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 11px;
        }
        .detail-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
            border-top: 1px solid #bdc3c7;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bank Reconciliation Report</h1>
        <p>Generated on: {{ $generatedAt }}</p>
        <p>Report ID: {{ $reconciliation->id }}</p>
    </div>

    <div class="section">
        <h2>Reconciliation Summary</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Bank Account</div>
                <div class="info-value">{{ $reconciliation->bankAccount->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Account Number</div>
                <div class="info-value">{{ $reconciliation->bankAccount->account_number ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Period Start</div>
                <div class="info-value">{{ $reconciliation->period_start }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Period End</div>
                <div class="info-value">{{ $reconciliation->period_end }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">{{ ucfirst($reconciliation->status) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Generated Date</div>
                <div class="info-value">{{ $reconciliation->created_at->format('Y-m-d H:i:s') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Balances</h2>
        <div class="balances">
            <div class="balance-item">
                <div class="balance-label">Bank Statement Balance</div>
                <div class="balance-value">Rp {{ number_format($reconciliation->bank_statement_balance, 2) }}</div>
            </div>
            <div class="balance-item">
                <div class="balance-label">Book Balance</div>
                <div class="balance-value">Rp {{ number_format($reconciliation->book_balance, 2) }}</div>
            </div>
            <div class="balance-item">
                <div class="balance-label">Difference</div>
                <div class="balance-value {{ $reconciliation->difference != 0 ? 'difference' : '' }}">
                    Rp {{ number_format($reconciliation->difference, 2) }}
                </div>
            </div>
        </div>
    </div>

    @if($includeTransactions && $reconciliation->transactionMatches && $reconciliation->transactionMatches->count() > 0)
    <div class="section">
        <h2>Transaction Matches ({{ $reconciliation->transactionMatches->count() }})</h2>
        <div class="transactions">
            @foreach($reconciliation->transactionMatches as $match)
            <div class="item">
                <div class="item-header">
                    <span class="item-type">{{ strtoupper($match->match_type) }}</span>
                    <span>Score: {{ $match->match_score }}%</span>
                </div>
                <div class="item-details">
                    <div>
                        <span class="detail-label">Bank Transaction:</span><br>
                        {{ $match->bankTransaction->description ?? 'No Description' }}<br>
                        <strong>Rp {{ number_format($match->bankTransaction->amount ?? 0, 2) }}</strong>
                    </div>
                    <div>
                        <span class="detail-label">Book Transaction:</span><br>
                        {{ $match->bookTransaction->description ?? 'No Description' }}<br>
                        <strong>Rp {{ number_format($match->bookTransaction->amount ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($includeAdjustments && $reconciliation->adjustments && $reconciliation->adjustments->count() > 0)
    <div class="section">
        <h2>Adjustments ({{ $reconciliation->adjustments->count() }})</h2>
        <div class="adjustments">
            @foreach($reconciliation->adjustments as $adjustment)
            <div class="item">
                <div class="item-header">
                    <span class="item-type">{{ strtoupper($adjustment->type) }}</span>
                    <span>{{ $adjustment->approved ? 'Approved' : 'Pending' }}</span>
                </div>
                <div class="item-details">
                    <div>
                        <span class="detail-label">Description:</span><br>
                        {{ $adjustment->description }}<br>
                        <span class="detail-label">Reference:</span> {{ $adjustment->reference ?? 'N/A' }}
                    </div>
                    <div>
                        <span class="detail-label">Amount:</span><br>
                        <strong>Rp {{ number_format($adjustment->amount, 2) }}</strong><br>
                        <span class="detail-label">Date:</span> {{ $adjustment->date }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($includeNotes && $reconciliation->notes)
    <div class="section">
        <h2>Notes</h2>
        <div class="notes">
            {{ $reconciliation->notes }}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Bank Reconciliation System</p>
        <p>For questions or support, please contact your system administrator</p>
    </div>
</body>
</html>
