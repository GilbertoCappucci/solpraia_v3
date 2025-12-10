<?php

namespace App\Services;

use App\Models\Stock;

class StockService
{
    /**
     * Checks if there is enough stock for the product.
     * Returns true if stock is infinite (< 0) or if quantity >= requested.
     */
    public function hasStock(int $productId, int $quantity = 1): bool
    {
        $stock = Stock::where('product_id', $productId)->first();

        if (!$stock) {
            // If no stock record exists, we assume 0 stock (unavailable)
            return false;
        }

        // Infinite stock condition
        if ($stock->quantity < 0) {
            return true;
        }

        return $stock->quantity >= $quantity;
    }

    /**
     * Decrements stock for a product.
     * Only decrements if stock is not infinite.
     * Throws exception if not enough stock? Or just silently returns false if fails? 
     * For now, we will try to decrement and return bool.
     */
    public function decrement(int $productId, int $quantity = 1): bool
    {
        $stock = Stock::where('product_id', $productId)->first();

        if (!$stock) {
            return false;
        }

        // Infinite stock
        if ($stock->quantity < 0) {
            return true;
        }

        // Use database constraint check or optimistic locking?
        // Simpler approach: update where quantity >= requested
        $updated = Stock::where('product_id', $productId)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        return $updated > 0;
    }

    /**
     * Increments stock for a product.
     * Only increments if stock is not infinite.
     */
    public function increment(int $productId, int $quantity = 1): bool
    {
        $stock = Stock::where('product_id', $productId)->first();

        if (!$stock) {
            return false;
        }

        // Infinite stock - do nothing
        if ($stock->quantity < 0) {
            return true;
        }

        $stock->increment('quantity', $quantity);
        return true;
    }
}
