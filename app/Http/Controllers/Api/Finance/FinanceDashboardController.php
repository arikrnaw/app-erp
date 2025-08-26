<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinanceDashboardController extends Controller
{
    protected FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Get comprehensive financial dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $data = $this->financeService->getFinancialDashboard();
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Financial dashboard data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial metrics for specific period
     */
    public function metrics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'month');
            $data = $this->financeService->getFinancialMetrics($period);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Financial metrics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget alerts
     */
    public function budgetAlerts(): JsonResponse
    {
        try {
            $alerts = $this->financeService->getBudgetAlerts();
            
            return response()->json([
                'success' => true,
                'data' => $alerts,
                'message' => 'Budget alerts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve budget alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending approvals
     */
    public function pendingApprovals(): JsonResponse
    {
        try {
            $approvals = $this->financeService->getPendingApprovals();
            
            return response()->json([
                'success' => true,
                'data' => $approvals,
                'message' => 'Pending approvals retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending approvals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cash position
     */
    public function cashPosition(): JsonResponse
    {
        try {
            $position = $this->financeService->getCashPosition();
            
            return response()->json([
                'success' => true,
                'data' => $position,
                'message' => 'Cash position retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cash position',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
