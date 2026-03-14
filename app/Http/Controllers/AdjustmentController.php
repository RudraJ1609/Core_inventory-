<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Adjustment::with(['product', 'warehouse']);

        if ($request->filled('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('product_id') && $request->input('product_id') !== 'all') {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', 'like', '%' . $request->input('reason') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->input('date_to'));
        }

        $adjustments = $query->orderByDesc('created_at')->get();

        return view('operations.adjustments', [
            'adjustments' => $adjustments,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => [
                'warehouse_id' => $request->input('warehouse_id', 'all'),
                'product_id' => $request->input('product_id', 'all'),
                'reason' => $request->input('reason', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function store(Request $request, StockService $stockService)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'counted_quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $stockService) {
            $adjustment = Adjustment::create([
                'number' => 'AD-' . Str::upper(Str::random(6)),
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'counted_quantity' => $validated['counted_quantity'],
                'delta_quantity' => 0,
                'reason' => $validated['reason'] ?? null,
                'status' => 'done',
                'occurred_at' => now(),
            ]);

            $delta = $stockService->setStock(
                $validated['product_id'],
                $validated['warehouse_id'],
                $validated['counted_quantity'],
                Adjustment::class,
                $adjustment->id,
                $validated['reason'] ?? 'Stock adjustment'
            );

            $adjustment->delta_quantity = $delta;
            $adjustment->save();
        });

        return redirect()->route('adjustments.index');
    }
}
