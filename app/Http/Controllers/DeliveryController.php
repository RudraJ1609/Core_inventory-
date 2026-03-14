<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['warehouse', 'items.product']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('customer')) {
            $query->where('customer', 'like', '%' . $request->input('customer') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->input('date_to'));
        }

        $deliveries = $query->orderByDesc('created_at')->get();

        return view('operations.deliveries', [
            'deliveries' => $deliveries,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => [
                'status' => $request->input('status', 'all'),
                'warehouse_id' => $request->input('warehouse_id', 'all'),
                'customer' => $request->input('customer', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => ['nullable', 'string', 'max:255'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $delivery = Delivery::create([
            'number' => 'DO-' . Str::upper(Str::random(6)),
            'customer' => $validated['customer'] ?? null,
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'waiting',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        foreach ($validated['product_id'] as $index => $productId) {
            $delivery->items()->create([
                'product_id' => $productId,
                'quantity' => $validated['quantity'][$index] ?? 1,
            ]);
        }

        return redirect()->route('deliveries.index');
    }

    public function validateDelivery(Delivery $delivery, StockService $stockService)
    {
        if ($delivery->status === 'done') {
            return redirect()->route('deliveries.index');
        }

        foreach ($delivery->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)
                ->where('warehouse_id', $delivery->warehouse_id)
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                return redirect()->route('deliveries.index')
                    ->withErrors('Insufficient stock for delivery ' . $delivery->number);
            }
        }

        foreach ($delivery->items as $item) {
            $stockService->removeStock(
                $item->product_id,
                $delivery->warehouse_id,
                $item->quantity,
                'delivery',
                Delivery::class,
                $delivery->id,
                'Delivery validated'
            );
        }

        $delivery->status = 'done';
        $delivery->validated_at = now();
        $delivery->save();

        return redirect()->route('deliveries.index');
    }
}
