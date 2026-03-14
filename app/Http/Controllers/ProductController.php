<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['inventories.warehouse'])
            ->withSum('inventories as total_stock', 'quantity');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->whereHas('inventories', function ($q) use ($request) {
                $q->where('warehouse_id', $request->input('warehouse_id'));
            });
        }

        $products = $query->orderBy('name')->get();

        $stockStatus = $request->input('stock_status');
        if ($stockStatus === 'low') {
            $products = $products->filter(function ($product) {
                return ($product->total_stock ?? 0) <= $product->reorder_point && ($product->total_stock ?? 0) > 0;
            })->values();
        } elseif ($stockStatus === 'out') {
            $products = $products->filter(function ($product) {
                return ($product->total_stock ?? 0) <= 0;
            })->values();
        } elseif ($stockStatus === 'healthy') {
            $products = $products->filter(function ($product) {
                return ($product->total_stock ?? 0) > $product->reorder_point;
            })->values();
        }

        $categoryCounts = $products
            ->groupBy(fn ($product) => $product->category ?? 'Uncategorized')
            ->map->count()
            ->sortDesc();

        return view('products.index', [
            'products' => $products,
            'categoryCounts' => $categoryCounts,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', 'all'),
                'warehouse_id' => $request->input('warehouse_id', 'all'),
                'stock_status' => $request->input('stock_status', 'all'),
            ],
        ]);
    }

    public function store(Request $request, StockService $stockService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit_of_measure' => ['nullable', 'string', 'max:40'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category' => $validated['category'] ?? null,
            'unit_of_measure' => $validated['unit_of_measure'] ?? null,
            'initial_stock' => $validated['initial_stock'] ?? 0,
            'reorder_point' => $validated['reorder_point'] ?? 0,
        ]);

        if (($validated['initial_stock'] ?? 0) > 0) {
            $warehouse = Warehouse::orderBy('id')->first();
            if ($warehouse) {
                $stockService->addStock(
                    $product->id,
                    $warehouse->id,
                    (int) $validated['initial_stock'],
                    'initial_stock',
                    Product::class,
                    $product->id,
                    'Initial stock on product creation'
                );
            }
        }

        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku,' . $product->id],
            'category' => ['nullable', 'string', 'max:120'],
            'unit_of_measure' => ['nullable', 'string', 'max:40'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category' => $validated['category'] ?? null,
            'unit_of_measure' => $validated['unit_of_measure'] ?? null,
            'reorder_point' => $validated['reorder_point'] ?? 0,
        ]);

        return redirect()->route('products.index');
    }
}
