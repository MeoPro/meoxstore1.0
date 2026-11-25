# AGENTS.md

Guide for AI coding assistants (Claude Code, Cursor, Copilot, Codex and others) working in a project built on **DropX Admin by DataDropX**. Human developers can read it too: it is the short version of the project's conventions.

## Project

Admin dashboard template on Next.js 16 (App Router), React 19, TypeScript (strict) and Tailwind CSS v4. No backend: all data is typed mock data, ready to swap for real API calls.

## Commands

| Command          | Purpose                             |
| ---------------- | ----------------------------------- |
| `yarn dev`       | Dev server on http://localhost:3000 |
| `yarn build`     | Production build                    |
| `yarn lint`      | ESLint                              |
| `yarn typecheck` | `tsc --noEmit`                      |
| `yarn format`    | Prettier                            |

Run `yarn lint` and `yarn typecheck` after every change.

## Where things live

- `app/(admin)/` admin pages, all wrapped by the sidebar and header layout
- `app/(auth)/` authentication screens with their own centered layout
- `config/navigation.ts` the sidebar menu; the breadcrumb and `sitemap.xml` are generated from it
- `config/site.ts` site name, URL, author and the demo user
- `components/ui/` primitives: `Button`, `Card`, `Input`, `Select`, `Textarea`, `Checkbox`, `Badge`, `Avatar`, `Skeleton`, `EmptyState` and more
- `components/layout/` sidebar, header, user menu, breadcrumb, footer
- `lib/chart-theme.ts` chart colors, tooltip and axis styles for Recharts
- `app/globals.css` all design tokens (colors, radius, shadows, chart palette, type scale)
- `data/` typed mock data; many pages also keep small demo arrays at the top of their file

## Conventions

### Adding a page

1. Create `app/(admin)/<section>/<name>/page.tsx`.
2. Export `metadata` with a `title`; the root layout adds ` | DropX Admin`.
3. If the page needs state or effects, put the UI in `view.tsx` with `"use client"` and keep `page.tsx` as a server file that exports `metadata` and renders the view. Many existing pages follow this pattern.
4. Start the page with `<PageHeader title="..." description="..." />`.
5. Add the route to `config/navigation.ts` so it appears in the sidebar, breadcrumb and sitemap.

### Styling

- Use theme tokens, never hard-coded hex colors: `bg-card`, `text-muted-foreground`, `border-border`, `text-primary`, `bg-success/10` and so on.
- Charts use `chartColors`, `tooltipProps` and `axisTick` from `lib/chart-theme.ts`.
- Radius scale: cards `rounded-xl`, buttons and inputs `rounded-lg`, small chips `rounded-md`, pills `rounded-full`.
- Shadows: `shadow-card` (resting), `shadow-dropdown` (menus, hover), `shadow-modal` (dialogs).
- Smallest text is `text-2xs` (11px). Do not go below it.
- Dark mode is class based (`next-themes`). `dark:` variants follow the `.dark` class, not the OS setting.

### Components and accessibility

- Use the form primitives (`Input`, `Select`, `Textarea`, `Checkbox`) with a `label` so the label is linked to the control.
- Icon-only buttons and links need an `aria-label`.
- Card titles render as `h2` under the page `h1`; keep heading levels in order.
- Wrap wide tables in `overflow-x-auto` and give them the `data-table` class.
- Icons come from `lucide-react`. Import each icon by name; do not import the whole library.

### Next.js 16

This version has breaking changes from older Next.js releases. Before using a Next.js API, read the matching guide in `node_modules/next/dist/docs/`.

## License rules to respect

DropX Admin is free to use under the DropX Admin License (see `LICENSE`):

- Keep the "Designed & developed by DataDropX" footer credit and its link to https://datadropx.net. Do not remove or hide `BrandCredit` in `components/layout/footer.tsx` or the auth layout.
- Redistribution is allowed free of charge, including on template marketplaces, as long as the credit stays and DataDropX is named as the original author. The template itself may not be sold as a paid template, theme or UI kit.

Credit: DropX Admin is designed and developed by [DataDropX](https://datadropx.net).

<!-- BEGIN:nextjs-agent-rules -->

# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` (resolved from this file's directory; in monorepos the `next` package may not be visible from the repo root) before writing any code. Heed deprecation notices.

This block is written and re-added by `next dev` — verify at `node_modules/next/dist/server/lib/generate-agent-files.js`. Removing it from a diff only re-creates the uncommitted change; committing it with your work keeps the tree clean.

<!-- END:nextjs-agent-rules -->
