<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Bill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== Debug Bill ID 6 ===\n";

// Check if we can find bill ID 6
$bill = Bill::find(6);
if (!$bill) {
    echo "❌ Bill ID 6 not found\n";
    exit;
}

echo "✅ Bill found:\n";
echo "   ID: {$bill->id}\n";
echo "   Bill Number: {$bill->bill_number}\n";
echo "   Status: {$bill->status}\n";
echo "   Company ID: {$bill->company_id}\n";
echo "   Created By: {$bill->created_by}\n";
echo "   Posted At: " . ($bill->posted_at ? $bill->posted_at : 'null') . "\n";

// Check if bill can be posted
if ($bill->status !== 'draft') {
    echo "❌ Bill cannot be posted. Current status: {$bill->status}\n";
    echo "   Only 'draft' bills can be posted\n";
    exit;
}

echo "✅ Bill can be posted (status is 'draft')\n";

// Check if user is authenticated
if (!Auth::check()) {
    echo "❌ User not authenticated\n";
    exit;
}

echo "✅ User authenticated: " . Auth::user()->name . " (ID: " . Auth::id() . ")\n";

// Check if user has access to this company
if (Auth::user()->company_id !== $bill->company_id) {
    echo "❌ User company ID (" . Auth::user()->company_id . ") doesn't match bill company ID ({$bill->company_id})\n";
    exit;
}

echo "✅ User has access to bill company\n";

// Try to post the bill
try {
    DB::beginTransaction();
    
    $bill->update([
        'status' => 'posted',
        'posted_at' => now()
    ]);
    
    DB::commit();
    echo "✅ Bill posted successfully!\n";
    echo "   New status: {$bill->status}\n";
    echo "   Posted at: {$bill->posted_at}\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error posting bill: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n=== End Debug ===\n";
