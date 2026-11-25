# Customization

Everything is data-driven and centered in a few files, so customizing the product
is fast and type-safe.

## Brand name & meta

Edit `config/site.ts`:

```ts
export const siteConfig = {
  name: "DropX Admin",          // shown in the sidebar brand & <title>
  description: "…",
  version: "1.0.0",
  url: "https://yoursite.com",
  author: { name: "Your Co", url: "https://yoursite.com" },
};
```

## Colors & theming

All theme tokens live in `app/globals.css` under the Tailwind v4 `@theme`
block. Change the base palette to rebrand:

```css
@theme inline {
  --color-primary: #6366f1;      /* brand color */
  --color-primary-foreground: #ffffff;
  --color-success: …;
  --color-warning: …;
  --color-danger: …;
  --color-info: …;
  /* dark-mode overrides in .dark … */
}
```

Both **light** and **dark** variants are defined. `next-themes` handles the
toggle; `providers/theme-provider.tsx` wires it to the header.

## Navigation / sidebar

Edit `config/navigation.ts`. Each group has a `label` and `items[]`; each item
supports an optional `icon` (Lucide icon name string), `badge`, and `children`.
Routes use the `@/` path alias defined in `tsconfig.json`.

## Typed demo data

All mock data is centralized and typed in `data/` and mirrors the models in
`types/modules.ts`:

```text
data/ecommerce.ts   data/crm.ts   data/dashboard.ts
data/finance.ts     data/lms.ts
```

Add or edit records here and every page consuming them updates automatically.
Add new rows for products, orders, customers, courses, etc. without touching page
files.

## Shared UI primitives

Reusable components live in `components/ui/` and are exported from
`components/ui/index.ts`:

- `Button`, `Badge`, `Card`, `Input`, `Avatar`, `Skeleton`
- `DataTable`, `AdvancedTable` — sort/search/select/pagination
- `GradientCard` — standard dashboard stat card
- `CreditCard` — payment card visual
- `AnimatedCounter`, `AnimatedProgress`, `CircularProgress`

Use them instead of hand-rolled markup to keep the product consistent.

## Adding a new page

1. Create `app/(admin)/<section>/<page>/page.tsx` (or a flat route).
2. Optionally add a route entry in `config/navigation.ts`.
3. Import shared UI from `@/components/ui` and data from `@/data`.
4. Add a `Metadata` export for the page title.
5. Run `yarn lint && yarn typecheck`.

## Code style

- Prettier is configured (`.prettierrc`) — run `yarn format`.
- ESLint flat config in `eslint.config.mjs` (Next + TypeScript rules).
- Keep components typed and side-effect free at the module scope.