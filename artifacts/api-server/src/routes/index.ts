import { Router, type IRouter } from "express";
import healthRouter from "./health";
import authRouter from "./auth";
import itemsRouter from "./items";
import historyRouter from "./history";
import dashboardRouter from "./dashboard";

const router: IRouter = Router();

router.use(healthRouter);
router.use(authRouter);
router.use(itemsRouter);
router.use(historyRouter);
router.use(dashboardRouter);

export default router;
