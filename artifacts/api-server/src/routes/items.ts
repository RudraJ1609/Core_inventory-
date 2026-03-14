import { Router, type IRouter } from "express";
import { db } from "@workspace/db";
import { inventoryItems, stockHistory } from "@workspace/db/schema";
import { eq, ilike, or } from "drizzle-orm";
import {
  CreateItemBody,
  UpdateItemBody,
  ListItemsQueryParams,
  GetItemParams,
  UpdateItemParams,
  DeleteItemParams,
} from "@workspace/api-zod";
import { requireAuth, requireManager } from "../middlewares/auth";

const router: IRouter = Router();

router.get("/items", requireAuth, async (req, res) => {
  const query = ListItemsQueryParams.parse(req.query);
  let items;
  if (query.search) {
    items = await db
      .select()
      .from(inventoryItems)
      .where(
        or(
          ilike(inventoryItems.name, `%${query.search}%`),
          ilike(inventoryItems.category, `%${query.search}%`)
        )
      )
      .orderBy(inventoryItems.createdAt);
  } else {
    items = await db.select().from(inventoryItems).orderBy(inventoryItems.createdAt);
  }
  const formatted = items.map((item) => ({
    ...item,
    price: parseFloat(item.price),
    createdAt: item.createdAt.toISOString(),
    updatedAt: item.updatedAt.toISOString(),
  }));
  res.json(formatted);
});

router.post("/items", requireManager, async (req, res) => {
  const body = CreateItemBody.parse(req.body);
  const [item] = await db
    .insert(inventoryItems)
    .values({ ...body, price: String(body.price) })
    .returning();

  await db.insert(stockHistory).values({
    itemId: item.id,
    itemName: item.name,
    action: "created",
    quantityBefore: null,
    quantityAfter: item.quantity,
    note: `Item created with quantity ${item.quantity}`,
  });

  res.status(201).json({
    ...item,
    price: parseFloat(item.price),
    createdAt: item.createdAt.toISOString(),
    updatedAt: item.updatedAt.toISOString(),
  });
});

router.get("/items/:id", requireAuth, async (req, res) => {
  const params = GetItemParams.parse({ id: Number(req.params.id) });
  const [item] = await db.select().from(inventoryItems).where(eq(inventoryItems.id, params.id));
  if (!item) {
    res.status(404).json({ error: "Item not found" });
    return;
  }
  res.json({
    ...item,
    price: parseFloat(item.price),
    createdAt: item.createdAt.toISOString(),
    updatedAt: item.updatedAt.toISOString(),
  });
});

router.put("/items/:id", requireManager, async (req, res) => {
  const params = UpdateItemParams.parse({ id: Number(req.params.id) });
  const body = UpdateItemBody.parse(req.body);
  const [existing] = await db.select().from(inventoryItems).where(eq(inventoryItems.id, params.id));
  if (!existing) {
    res.status(404).json({ error: "Item not found" });
    return;
  }

  const updateData: Record<string, unknown> = { updatedAt: new Date() };
  if (body.name !== undefined) updateData.name = body.name;
  if (body.price !== undefined) updateData.price = String(body.price);
  if (body.category !== undefined) updateData.category = body.category;
  if (body.quantity !== undefined) updateData.quantity = body.quantity;

  const [updated] = await db
    .update(inventoryItems)
    .set(updateData)
    .where(eq(inventoryItems.id, params.id))
    .returning();

  const oldQty = existing.quantity;
  const newQty = updated.quantity;
  let action = "updated";
  let note = "Item details updated";
  if (body.quantity !== undefined && oldQty !== newQty) {
    if (newQty > oldQty) {
      action = "stock_added";
      note = `Stock increased by ${newQty - oldQty}`;
    } else {
      action = "stock_removed";
      note = `Stock decreased by ${oldQty - newQty}`;
    }
  }

  await db.insert(stockHistory).values({
    itemId: updated.id,
    itemName: updated.name,
    action,
    quantityBefore: oldQty,
    quantityAfter: newQty,
    note,
  });

  res.json({
    ...updated,
    price: parseFloat(updated.price),
    createdAt: updated.createdAt.toISOString(),
    updatedAt: updated.updatedAt.toISOString(),
  });
});

router.delete("/items/:id", requireManager, async (req, res) => {
  const params = DeleteItemParams.parse({ id: Number(req.params.id) });
  const [existing] = await db.select().from(inventoryItems).where(eq(inventoryItems.id, params.id));
  if (!existing) {
    res.status(404).json({ error: "Item not found" });
    return;
  }

  await db.insert(stockHistory).values({
    itemId: existing.id,
    itemName: existing.name,
    action: "deleted",
    quantityBefore: existing.quantity,
    quantityAfter: null,
    note: `Item deleted`,
  });

  await db.delete(inventoryItems).where(eq(inventoryItems.id, params.id));
  res.json({ success: true });
});

export default router;
