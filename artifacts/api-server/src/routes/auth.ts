import { Router, type IRouter } from "express";
import bcrypt from "bcryptjs";
import { db } from "@workspace/db";
import { users } from "@workspace/db/schema";
import { eq } from "drizzle-orm";
import { LoginBody, SignupBody } from "@workspace/api-zod";

const router: IRouter = Router();

async function seedDefaultUsers() {
  const existing = await db.select().from(users);
  if (existing.length > 0) return;

  const managerHash = await bcrypt.hash("manager123", 12);
  const guestHash = await bcrypt.hash("guest123", 12);

  await db.insert(users).values([
    { username: "manager", passwordHash: managerHash, role: "manager" },
    { username: "guest", passwordHash: guestHash, role: "guest" },
  ]);
  console.log("Default users seeded: manager / guest");
}

seedDefaultUsers().catch(console.error);

router.post("/auth/signup", async (req, res) => {
  const body = SignupBody.parse(req.body);

  const [existing] = await db.select().from(users).where(eq(users.username, body.username));
  if (existing) {
    res.status(409).json({ error: "Username already taken. Please choose another." });
    return;
  }

  const passwordHash = await bcrypt.hash(body.password, 12);
  const [user] = await db
    .insert(users)
    .values({ username: body.username, passwordHash, role: "guest" })
    .returning();

  req.session.userId = user.id;
  req.session.username = user.username;
  req.session.role = user.role;

  res.status(201).json({ id: user.id, username: user.username, role: user.role });
});

router.post("/auth/login", async (req, res) => {
  const body = LoginBody.parse(req.body);
  const [user] = await db.select().from(users).where(eq(users.username, body.username));

  if (!user) {
    res.status(401).json({ error: "Invalid username or password" });
    return;
  }

  const valid = await bcrypt.compare(body.password, user.passwordHash);
  if (!valid) {
    res.status(401).json({ error: "Invalid username or password" });
    return;
  }

  req.session.userId = user.id;
  req.session.username = user.username;
  req.session.role = user.role;

  res.json({ id: user.id, username: user.username, role: user.role });
});

router.post("/auth/logout", (req, res) => {
  req.session.destroy(() => {
    res.clearCookie("inventory.sid");
    res.json({ success: true });
  });
});

router.get("/auth/me", (req, res) => {
  if (!req.session.userId) {
    res.status(401).json({ error: "Not authenticated" });
    return;
  }
  res.json({
    id: req.session.userId,
    username: req.session.username,
    role: req.session.role,
  });
});

export default router;
