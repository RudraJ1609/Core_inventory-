<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Move;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoveController extends Controller
{
    public function index(Request $request)
    {
        $query = Move::with(['fromWarehouse', 'toWarehouse', 'items.product']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_warehouse_id') && $request->input('from_warehouse_id') !== 'all') {
            $query->where('from_warehouse_id', $request->input('from_warehouse_id'));
        }

        if ($request->filled('to_warehouse_id') && $request->input('to_warehouse_id') !== 'all') {
            $query->where('to_warehouse_id', $request->input('to_warehouse_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->input('date_to'));
        }

        $moves = $query->orderByDesc('created_at')->get();

        return view('operations.moves', [
            'moves' => $moves,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => [
                'status' => $request->input('status', 'all'),
                'from_warehouse_id' => $request->input('from_warehouse_id', 'all'),
                'to_warehouse_id' => $request->input('to_warehouse_id', 'all'),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => ['required', 'different:to_warehouse_id', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($validated) {
            $move = Move::create([
                'number' => 'IT-' . Str::upper(Str::random(6)),
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'status' => 'waiting',
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $move->items()->create([
                    'product_id' => $productId,
                    'quantity' => $validated['quantity'][$index] ?? 1,
                ]);
            }
        });

        return redirect()->route('moves.index');
    }

    public function complete(Move $move, StockService $stockService)
    {
        if ($move->status === 'done') {
            return redirect()->route('moves.index');
        }

        foreach ($move->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)
                ->where('warehouse_id', $move->from_warehouse_id)
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                return redirect()->route('moves.index')
                    ->withErrors('Insufficient stock to complete transfer ' . $move->number);
            }
        }

        foreach ($move->items as $item) {
            $stockService->moveStock(
                $item->product_id,
                $move->from_warehouse_id,
                $move->to_warehouse_id,
                $item->quantity,
                Move::class,
                $move->id,
                'Internal transfer'
            );
        }

        $move->status = 'done';
        $move->completed_at = now();
        $move->save();

        return redirect()->route('moves.index');
    }
}
