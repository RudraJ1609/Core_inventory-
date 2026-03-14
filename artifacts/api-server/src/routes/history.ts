import { Router, type IRouter } from "express";
import { db } from "@workspace/db";
import { stockHistory } from "@workspace/db/schema";
import { eq, desc } from "drizzle-orm";
import { ListHistoryQueryParams } from "@workspace/api-zod";
import { requireAuth } from "../middlewares/auth";

const router: IRouter = Router();

router.get("/history", requireAuth, async (req, res) => {
  const query = ListHistoryQueryParams.parse(req.query);
  let events;
  if (query.itemId) {
    events = await db
      .select()
      .from(stockHistory)
      .where(eq(stockHistory.itemId, query.itemId))
      .orderBy(desc(stockHistory.createdAt));
  } else {
    events = await db
      .select()
      .from(stockHistory)
      .orderBy(desc(stockHistory.createdAt));
  }
  const formatted = events.map((e) => ({
    ...e,
    createdAt: e.createdAt.toISOString(),
  }));
  res.json(formatted);
});

export default router;
