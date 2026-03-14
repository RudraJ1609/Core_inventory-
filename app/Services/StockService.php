<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function addStock(int $productId, int $warehouseId, int $quantity, string $type, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        $this->applyChange($productId, $warehouseId, $quantity, $type, $referenceType, $referenceId, $note);
    }

    public function removeStock(int $productId, int $warehouseId, int $quantity, string $type, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        $this->applyChange($productId, $warehouseId, -1 * abs($quantity), $type, $referenceType, $referenceId, $note);
    }

    public function moveStock(int $productId, int $fromWarehouseId, int $toWarehouseId, int $quantity, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        $this->removeStock($productId, $fromWarehouseId, $quantity, 'move_out', $referenceType, $referenceId, $note);
        $this->addStock($productId, $toWarehouseId, $quantity, 'move_in', $referenceType, $referenceId, $note);
    }

    public function setStock(int $productId, int $warehouseId, int $countedQuantity, ?string $referenceType, ?int $referenceId, ?string $note = null): int
    {
        return DB::transaction(function () use ($productId, $warehouseId, $countedQuantity, $referenceType, $referenceId, $note) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $delta = $countedQuantity - $inventory->quantity;
            $inventory->quantity = $countedQuantity;
            $inventory->save();

            StockLedger::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_change' => $delta,
                'balance_after' => $countedQuantity,
                'note' => $note,
                'occurred_at' => now(),
            ]);

            return $delta;
        });
    }

    private function applyChange(int $productId, int $warehouseId, int $quantityChange, string $type, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        DB::transaction(function () use ($productId, $warehouseId, $quantityChange, $type, $referenceType, $referenceId, $note) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $inventory->quantity += $quantityChange;
            $inventory->save();

            StockLedger::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_change' => $quantityChange,
                'balance_after' => $inventory->quantity,
                'note' => $note,
                'occurred_at' => now(),
            ]);
        });
    }
}
