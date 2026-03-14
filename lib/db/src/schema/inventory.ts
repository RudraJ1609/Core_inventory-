import { pgTable, text, serial, integer, numeric, timestamp } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod/v4";

export const inventoryItems = pgTable("inventory_items", {
  id: serial("id").primaryKey(),
  name: text("name").notNull(),
  quantity: integer("quantity").notNull().default(0),
  price: numeric("price", { precision: 10, scale: 2 }).notNull(),
  category: text("category").notNull(),
  createdAt: timestamp("created_at").notNull().defaultNow(),
  updatedAt: timestamp("updated_at").notNull().defaultNow(),
});

export const stockHistory = pgTable("stock_history", {
  id: serial("id").primaryKey(),
  itemId: integer("item_id").notNull(),
  itemName: text("item_name").notNull(),
  action: text("action").notNull(), // created | updated | deleted | stock_added | stock_removed
  quantityBefore: integer("quantity_before"),
  quantityAfter: integer("quantity_after"),
  note: text("note"),
  createdAt: timestamp("created_at").notNull().defaultNow(),
});

export const insertItemSchema = createInsertSchema(inventoryItems).omit({ id: true, createdAt: true, updatedAt: true });
export const updateItemSchema = createInsertSchema(inventoryItems).omit({ id: true, createdAt: true, updatedAt: true }).partial();

export type InsertItem = z.infer<typeof insertItemSchema>;
export type InventoryItem = typeof inventoryItems.$inferSelect;
export type StockHistory = typeof stockHistory.$inferSelect;
