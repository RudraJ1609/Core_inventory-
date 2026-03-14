<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Receipt;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Receipt::with(['warehouse', 'items.product']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->input('supplier') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->input('date_to'));
        }

        $receipts = $query->orderByDesc('created_at')->get();

        return view('operations.receipts', [
            'receipts' => $receipts,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => [
                'status' => $request->input('status', 'all'),
                'warehouse_id' => $request->input('warehouse_id', 'all'),
                'supplier' => $request->input('supplier', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier' => ['nullable', 'string', 'max:255'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $receipt = Receipt::create([
            'number' => 'RC-' . Str::upper(Str::random(6)),
            'supplier' => $validated['supplier'] ?? null,
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'waiting',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        foreach ($validated['product_id'] as $index => $productId) {
            $receipt->items()->create([
                'product_id' => $productId,
                'quantity' => $validated['quantity'][$index] ?? 1,
            ]);
        }

        return redirect()->route('receipts.index');
    }

    public function validateReceipt(Receipt $receipt, StockService $stockService)
    {
        if ($receipt->status === 'done') {
            return redirect()->route('receipts.index');
        }

        foreach ($receipt->items as $item) {
            $stockService->addStock(
                $item->product_id,
                $receipt->warehouse_id,
                $item->quantity,
                'receipt',
                Receipt::class,
                $receipt->id,
                'Receipt validated'
            );
        }

        $receipt->status = 'done';
        $receipt->validated_at = now();
        $receipt->save();

        return redirect()->route('receipts.index');
    }
}
