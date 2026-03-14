# Workspace

## Overview

pnpm workspace monorepo using TypeScript. Each package manages its own dependencies.

## Stack

- **Monorepo tool**: pnpm workspaces
- **Node.js version**: 24
- **Package manager**: pnpm
- **TypeScript version**: 5.9
- **API framework**: Express 5
- **Database**: PostgreSQL + Drizzle ORM
- **Validation**: Zod (`zod/v4`), `drizzle-zod`
- **API codegen**: Orval (from OpenAPI spec)
- **Build**: esbuild (CJS bundle)

## Structure

```text
artifacts-monorepo/
├── artifacts/              # Deployable applications
│   ├── api-server/         # Express API server
│   └── inventory/          # React + Vite Inventory Management frontend
├── lib/                    # Shared libraries
│   ├── api-spec/           # OpenAPI spec + Orval codegen config
│   ├── api-client-react/   # Generated React Query hooks
│   ├── api-zod/            # Generated Zod schemas from OpenAPI
│   └── db/                 # Drizzle ORM schema + DB connection
├── scripts/                # Utility scripts (single workspace package)
│   └── src/                # Individual .ts scripts
├── pnpm-workspace.yaml     # pnpm workspace (artifacts/*, lib/*, scripts)
├── tsconfig.base.json      # Shared TS options
├── tsconfig.json           # Root TS project references
└── package.json            # Root package with hoisted devDeps
```

## Application: Inventory Management System

A full-stack inventory management app with:
- **Dashboard** — total stock value, low stock alerts (threshold: qty ≤ 5)
- **Inventory Page** — add/edit/delete items with search (name or category)
- **History Log** — records every stock change (created, updated, deleted, stock_added, stock_removed)

### Routes
- `/` — Dashboard with stats and low-stock alerts
- `/inventory` — Full inventory table with CRUD
- `/history` — Stock change history log

### API Endpoints (all under `/api`)
- `GET /api/dashboard` → stats
- `GET /api/items?search=...` → list items
- `POST /api/items` → create item
- `PUT /api/items/:id` → update item
- `DELETE /api/items/:id` → delete item
- `GET /api/history?itemId=...` → history log

### DB Schema
- `inventory_items` — id, name, quantity, price (numeric), category, createdAt, updatedAt
- `stock_history` — id, itemId, itemName, action, quantityBefore, quantityAfter, note, createdAt

## TypeScript & Composite Projects

Every package extends `tsconfig.base.json` which sets `composite: true`. The root `tsconfig.json` lists all packages as project references.

- **Always typecheck from the root** — run `pnpm run typecheck`
- **`emitDeclarationOnly`** — we only emit `.d.ts` files during typecheck

## Root Scripts

- `pnpm run build` — runs `typecheck` first, then recursively runs `build` in all packages that define it
- `pnpm run typecheck` — runs `tsc --build --emitDeclarationOnly` using project references

## Packages

### `artifacts/inventory` (`@workspace/inventory`)

React + Vite frontend for the Inventory Management System.
- Entry: `src/main.tsx`
- App: `src/App.tsx` — wouter routing with QueryClientProvider
- Pages: `src/pages/dashboard.tsx`, `src/pages/inventory.tsx`, `src/pages/history.tsx`
- Components: `src/components/layout.tsx`, `src/components/item-dialog.tsx`, `src/components/delete-dialog.tsx`
- Hooks: `src/hooks/use-inventory.ts`, `src/hooks/use-debounce.ts`
- Deps: react-hook-form, @hookform/resolvers, date-fns, @workspace/api-client-react

### `artifacts/api-server` (`@workspace/api-server`)

Express 5 API server. Routes live in `src/routes/`.

- `src/routes/items.ts` — CRUD for inventory_items + writes to stock_history
- `src/routes/history.ts` — read-only history log
- `src/routes/dashboard.ts` — aggregate stats

### `lib/db` (`@workspace/db`)

- `src/schema/inventory.ts` — inventoryItems table, stockHistory table, insert schemas
