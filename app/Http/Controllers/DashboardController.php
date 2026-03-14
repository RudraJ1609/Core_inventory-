<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\Move;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\StockLedger;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStock = Inventory::sum('quantity');

        $productsWithTotals = Product::query()
            ->where('is_active', true)
            ->withSum('inventories as total_stock', 'quantity')
            ->get();

        $lowStockItems = $productsWithTotals
            ->filter(fn ($product) => ($product->total_stock ?? 0) <= $product->reorder_point)
            ->values();

        $lowStockCount = $lowStockItems->count();

        $pendingReceipts = Receipt::whereIn('status', ['draft', 'waiting', 'ready'])->count();
        $pendingDeliveries = Delivery::whereIn('status', ['draft', 'waiting', 'ready'])->count();
        $internalTransfersScheduled = Move::whereIn('status', ['draft', 'waiting', 'ready'])->count();

        $recentOperations = StockLedger::with(['product', 'warehouse'])
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'totalStock' => $totalStock,
            'lowStockCount' => $lowStockCount,
            'pendingReceipts' => $pendingReceipts,
            'pendingDeliveries' => $pendingDeliveries,
            'internalTransfersScheduled' => $internalTransfersScheduled,
            'lowStockItems' => $lowStockItems->take(5),
            'recentOperations' => $recentOperations,
        ]);
    }
}
