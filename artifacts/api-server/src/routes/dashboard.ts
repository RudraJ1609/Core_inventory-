import { Router, type IRouter } from "express";
import { db } from "@workspace/db";
import { inventoryItems } from "@workspace/db/schema";
import { lte, sql } from "drizzle-orm";

const router: IRouter = Router();

router.get("/dashboard", async (_req, res) => {
  const items = await db.select().from(inventoryItems);
  const totalItems = items.length;
  const totalStockValue = items.reduce(
    (sum, item) => sum + parseFloat(item.price) * item.quantity,
    0
  );
  const lowStockItems = items
    .filter((item) => item.quantity <= 5)
    .map((item) => ({
      ...item,
      price: parseFloat(item.price),
      createdAt: item.createdAt.toISOString(),
      updatedAt: item.updatedAt.toISOString(),
    }));
  const categories = new Set(items.map((item) => item.category));
  const totalCategories = categories.size;

  res.json({
    totalItems,
    totalStockValue,
    lowStockItems,
    totalCategories,
  });
});

export default router;
