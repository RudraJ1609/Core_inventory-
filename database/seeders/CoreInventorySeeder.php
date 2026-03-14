<?php

namespace Database\Seeders;

use App\Models\Adjustment;
use App\Models\Delivery;
use App\Models\Move;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoreInventorySeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = collect([
            ['name' => 'Main Warehouse', 'code' => 'WH-01', 'location' => 'Mumbai'],
            ['name' => 'Production Floor', 'code' => 'PR-01', 'location' => 'Mumbai'],
            ['name' => 'Warehouse 2', 'code' => 'WH-02', 'location' => 'Pune'],
        ])->map(function ($data) {
            return Warehouse::firstOrCreate(['code' => $data['code']], $data);
        });

        $products = collect([
            ['name' => 'Steel Rods', 'sku' => 'STL-204', 'category' => 'Raw Materials', 'unit_of_measure' => 'kg', 'reorder_point' => 150],
            ['name' => 'Aluminum Sheets', 'sku' => 'ALU-118', 'category' => 'Raw Materials', 'unit_of_measure' => 'sheets', 'reorder_point' => 120],
            ['name' => 'Chair Frame', 'sku' => 'FRM-011', 'category' => 'Finished Goods', 'unit_of_measure' => 'units', 'reorder_point' => 40],
            ['name' => 'Packing Foam', 'sku' => 'PKG-011', 'category' => 'Packaging', 'unit_of_measure' => 'rolls', 'reorder_point' => 30],
        ])->map(function ($data) {
            return Product::firstOrCreate(['sku' => $data['sku']], $data);
        });

        $stockService = app(StockService::class);

        $stockService->setStock($products[0]->id, $warehouses[0]->id, 420, null, null, 'Opening balance');
        $stockService->setStock($products[1]->id, $warehouses[0]->id, 280, null, null, 'Opening balance');
        $stockService->setStock($products[2]->id, $warehouses[1]->id, 78, null, null, 'Opening balance');
        $stockService->setStock($products[3]->id, $warehouses[2]->id, 22, null, null, 'Opening balance');

        if (Receipt::count() === 0) {
            $receiptDone = Receipt::create([
                'number' => 'RC-' . Str::upper(Str::random(6)),
                'supplier' => 'Zenith Metals',
                'warehouse_id' => $warehouses[0]->id,
                'status' => 'waiting',
                'scheduled_at' => now()->subHours(6),
            ]);

            $receiptDone->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 50,
            ]);

            $stockService->addStock(
                $products[0]->id,
                $warehouses[0]->id,
                50,
                'receipt',
                Receipt::class,
                $receiptDone->id,
                'Receipt validated'
            );

            $receiptDone->status = 'done';
            $receiptDone->validated_at = now()->subHours(4);
            $receiptDone->save();

            Receipt::create([
                'number' => 'RC-' . Str::upper(Str::random(6)),
                'supplier' => 'Allied Tools',
                'warehouse_id' => $warehouses[0]->id,
                'status' => 'waiting',
                'scheduled_at' => now()->addHours(6),
            ])->items()->create([
                'product_id' => $products[1]->id,
                'quantity' => 120,
            ]);

            Receipt::create([
                'number' => 'RC-' . Str::upper(Str::random(6)),
                'supplier' => 'Bright Packaging',
                'warehouse_id' => $warehouses[2]->id,
                'status' => 'ready',
                'scheduled_at' => now()->addHours(3),
            ])->items()->create([
                'product_id' => $products[3]->id,
                'quantity' => 40,
            ]);
        }

        if (Delivery::count() === 0) {
            $deliveryDone = Delivery::create([
                'number' => 'DO-' . Str::upper(Str::random(6)),
                'customer' => 'Alto Designs',
                'warehouse_id' => $warehouses[1]->id,
                'status' => 'waiting',
                'scheduled_at' => now()->subHours(2),
            ]);

            $deliveryDone->items()->create([
                'product_id' => $products[2]->id,
                'quantity' => 10,
            ]);

            $stockService->removeStock(
                $products[2]->id,
                $warehouses[1]->id,
                10,
                'delivery',
                Delivery::class,
                $deliveryDone->id,
                'Delivery validated'
            );

            $deliveryDone->status = 'done';
            $deliveryDone->validated_at = now()->subHours(1);
            $deliveryDone->save();

            Delivery::create([
                'number' => 'DO-' . Str::upper(Str::random(6)),
                'customer' => 'Northline Retail',
                'warehouse_id' => $warehouses[0]->id,
                'status' => 'ready',
                'scheduled_at' => now()->addHours(4),
            ])->items()->create([
                'product_id' => $products[1]->id,
                'quantity' => 6,
            ]);

            Delivery::create([
                'number' => 'DO-' . Str::upper(Str::random(6)),
                'customer' => 'Urban Loft',
                'warehouse_id' => $warehouses[0]->id,
                'status' => 'waiting',
                'scheduled_at' => now()->addHours(6),
            ])->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 4,
            ]);
        }

        if (Move::count() === 0) {
            $moveDone = Move::create([
                'number' => 'IT-' . Str::upper(Str::random(6)),
                'from_warehouse_id' => $warehouses[0]->id,
                'to_warehouse_id' => $warehouses[1]->id,
                'status' => 'waiting',
                'scheduled_at' => now()->subHours(3),
            ]);

            $moveDone->items()->create([
                'product_id' => $products[0]->id,
                'quantity' => 100,
            ]);

            $stockService->moveStock(
                $products[0]->id,
                $warehouses[0]->id,
                $warehouses[1]->id,
                100,
                Move::class,
                $moveDone->id,
                'Internal transfer'
            );

            $moveDone->status = 'done';
            $moveDone->completed_at = now()->subHours(2);
            $moveDone->save();

            Move::create([
                'number' => 'IT-' . Str::upper(Str::random(6)),
                'from_warehouse_id' => $warehouses[2]->id,
                'to_warehouse_id' => $warehouses[0]->id,
                'status' => 'ready',
                'scheduled_at' => now()->addHours(2),
            ])->items()->create([
                'product_id' => $products[3]->id,
                'quantity' => 14,
            ]);
        }

        if (Adjustment::count() === 0) {
            $adjustment = Adjustment::create([
                'number' => 'AD-' . Str::upper(Str::random(6)),
                'product_id' => $products[0]->id,
                'warehouse_id' => $warehouses[1]->id,
                'counted_quantity' => 97,
                'delta_quantity' => 0,
                'reason' => 'Damaged items',
                'status' => 'done',
                'occurred_at' => now()->subHours(5),
            ]);

            $delta = $stockService->setStock(
                $products[0]->id,
                $warehouses[1]->id,
                97,
                Adjustment::class,
                $adjustment->id,
                'Damaged items'
            );

            $adjustment->delta_quantity = $delta;
            $adjustment->save();
        }
    }
}
