# DropX Admin - Free Tailwind CSS & Next.js Admin Dashboard Website Template

**A free admin dashboard template by [DataDropX](https://datadropx.net).**

DropX Admin is a production-ready dashboard built with Next.js 16, React 19, TypeScript and Tailwind CSS v4. It ships with 8 dashboards, 9 complete apps, 18 authentication screens and 100+ UI components, all with light and dark mode.

**Live demo:** [themewagon.github.io/dropx-admin](https://preview.themewagon.com/dropx-admin/)

## Download

- **Download:** [Get it on ThemeWagon](https://themewagon.com/themes/dropx-admin/)
- **Git:** `git clone https://github.com/themewagon/dropx-admin.git`

```bash
yarn install
yarn dev
```

![DropX Admin screenshot](public/screenshot.png)
_(Screenshot placeholder: add `public/screenshot.png`.)_

---

## Features

### Dashboards (8)

Default, Analytics, CRM, E-commerce, LMS, Project Management, SaaS and Support Desk.

### Apps (9)

| App          | Pages                                                                                                                                          |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| E-commerce   | Product list, product grid, product details, add product, orders, order details, customers, customer details, cart, checkout, billing, invoice |
| Email        | Inbox, email detail, compose                                                                                                                   |
| Events       | Event list, event detail, create event                                                                                                         |
| E-learning   | Course, student overview, trainer profile                                                                                                      |
| Social       | Feed, activity log, notifications, followers                                                                                                   |
| Support Desk | Table view, card view, contacts, contact detail, ticket preview, quick links, reports                                                          |
| Calendar     | Month view with event dialog                                                                                                                   |
| Chat         | Conversations with search and filters                                                                                                          |
| Kanban       | Task board                                                                                                                                     |

### Pages

- **Authentication (18):** login, register, forgot password, reset password, confirm mail and lock screen, each in Simple, Card and Split styles
- **User:** profile and account settings
- **Pricing (2), FAQ (3), Errors (404, 500)**
- **Misc:** landing, starter, associations, invite people, privacy policy

### Layout

- Collapsible vertical sidebar with icon-only mode
- Mobile and tablet drawer navigation
- Header with search (`Ctrl` / `Cmd` + `K`), theme toggle, fullscreen and user menu
- Light, dark and system themes

### Components and modules

- **UI components (30+):** accordion, alerts, avatars, badges, breadcrumbs, buttons, cards, carousel, collapse, cookie notice, count-up, dropdowns, list group, modals, navs and tabs, offcanvas, pagination, pictures and lightbox, placeholders, popovers, progress bars, scrollspy, search, sortable list, spinners, timeline, toasts, tooltips, tree view, typed text and video players
- **Forms:** basic, advanced, floating labels, validation and a multi-step wizard
- **Tables:** basic, advanced (sorting, search, pagination) and bulk select
- **Charts:** Recharts with a theme-aware color palette
- **Maps:** Leaflet with OpenStreetMap (no API key)
- **Icons:** icon galleries built on Lucide
- **Utilities:** background, borders, colors, display, flex, float, grid, sizing, spacing, typography and more

### Developer experience

- TypeScript strict mode and typed mock data in `data/`
- One navigation config (`config/navigation.ts`) drives the sidebar, breadcrumbs and sitemap
- Shared design tokens for color, radius, shadow and type in `app/globals.css`
- Accessible by default: visible focus rings, labelled controls and reduced-motion support
- SEO ready: per-page titles, Open Graph image, `robots.txt` and `sitemap.xml`

## Tech stack

| Layer     | Library                                                   | Version |
| --------- | --------------------------------------------------------- | ------- |
| Framework | [Next.js](https://nextjs.org) (App Router)                | 16.4.5  |
| UI        | [React](https://react.dev)                                | 19.2.8  |
| Language  | [TypeScript](https://www.typescriptlang.org)              | 5.9     |
| Styling   | [Tailwind CSS](https://tailwindcss.com)                   | 4.3     |
| State     | [Redux Toolkit](https://redux-toolkit.js.org)             | 2.12    |
| Charts    | [Recharts](https://recharts.org)                          | 3.10    |
| Maps      | [Leaflet](https://leafletjs.com)                          | 1.9.4   |
| Icons     | [Lucide](https://lucide.dev)                              | 1.28    |
| Theming   | [next-themes](https://github.com/pacocoursey/next-themes) | 0.4.6   |
| Toasts    | [Sonner](https://sonner.emilkowal.ski)                    | 2.0     |

## Requirements

- Node.js 20 or newer
- Yarn 1.x (or npm / pnpm)

## Getting started

```bash
git clone https://github.com/themewagon/dropx-admin.git
cd dropx-admin
yarn install
yarn dev
```

Open [http://localhost:3000](http://localhost:3000). The root route redirects to `/dashboard`.

### Scripts

| Command          | What it does                       |
| ---------------- | ---------------------------------- |
| `yarn dev`       | Start the development server       |
| `yarn build`     | Create a production build          |
| `yarn start`     | Serve the production build         |
| `yarn lint`      | Run ESLint                         |
| `yarn typecheck` | Run the TypeScript compiler checks |
| `yarn format`    | Format the code with Prettier      |

## Build and deploy

```bash
yarn build
yarn start
```

### Deploy to Vercel

1. Push the project to a GitHub repository.
2. Import it at [vercel.com/new](https://vercel.com/new). Vercel detects Next.js automatically.
3. Update `url` in `config/site.ts` to your live domain so Open Graph links and the sitemap point to it.

The template has no environment variables or backend, so no extra setup is needed. See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for other hosts.

## Folder structure

```
app/
  (admin)/          Admin area: dashboards, apps, pages, components, modules, utilities
  (auth)/           Authentication screens
  globals.css       Theme tokens (colors, radius, shadows, chart palette)
  layout.tsx        Root layout and site metadata
components/
  layout/           Sidebar, header, user menu, breadcrumb, footer
  ui/               Reusable primitives (Button, Card, Input, Select, Table styles...)
config/
  navigation.ts     Sidebar menu, single source of truth for routes
  site.ts           Site name, URL, author and demo user
data/               Typed mock data (CRM, e-commerce, LMS)
lib/                Helpers (cn, formatters, chart theme)
public/brand/       DataDropX logo files
store/              Redux store and slices
types/              Shared TypeScript types
```

## Customization

### Theme and colors

All colors are CSS variables in `app/globals.css`. Edit `:root` for light mode and `.dark` for dark mode. The primary color is set in the `@theme` block (`--color-primary`), and chart colors use `--chart-1` to `--chart-7`.

### Logo

Replace the files in `public/brand/` and update `components/ui/logo.tsx`. The favicon, Apple icon and Open Graph image are `app/favicon.ico`, `app/icon.png`, `app/apple-icon.png` and `app/opengraph-image.png`.

### Navigation

Add, remove or reorder menu items in `config/navigation.ts`. Each item takes a `title`, `href`, optional Lucide `icon` and optional `children`.

### Site details

Set the name, description, live URL and demo user in `config/site.ts`.

More detail in [docs/CUSTOMIZATION.md](docs/CUSTOMIZATION.md).

## Browser support

Latest two versions of Chrome, Edge, Firefox and Safari (desktop and mobile).

## Credits

DropX Admin is built on these open-source projects:

- [Next.js](https://github.com/vercel/next.js), [React](https://github.com/facebook/react), [Tailwind CSS](https://github.com/tailwindlabs/tailwindcss), [Redux Toolkit](https://github.com/reduxjs/redux-toolkit), [React Redux](https://github.com/reduxjs/react-redux), [Recharts](https://github.com/recharts/recharts), [next-themes](https://github.com/pacocoursey/next-themes), [Sonner](https://github.com/emilkowalski/sonner), [clsx](https://github.com/lukeed/clsx) and [tailwind-merge](https://github.com/dcastil/tailwind-merge) (MIT)
- [Lucide](https://github.com/lucide-icons/lucide) icons (ISC)
- [Leaflet](https://github.com/Leaflet/Leaflet) (BSD-2-Clause); map tiles © [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors
- [class-variance-authority](https://github.com/joe-bell/cva) (Apache-2.0)
- [Geist](https://vercel.com/font) font by Vercel (SIL Open Font License 1.1)
- Sample video "Big Buck Bunny" © Blender Foundation (CC BY 3.0); sample audio "SoundHelix Song 1" by T. Schürger
- Demo avatars and brand assets are original work by DataDropX

## License

Free for personal and commercial use under the [DropX Admin License](LICENSE) (based on MIT):

1. Keep the "Designed & developed by DataDropX" footer credit and its link to [datadropx.net](https://datadropx.net) visible.
2. You may redistribute DropX Admin, original or modified, free of charge, including on template marketplaces, as long as the credit stays and DataDropX is named as the original author.
3. Do not sell DropX Admin itself as a paid template, theme or UI kit. Selling the websites and apps you build with it is fine.

## Author

**DataDropX** · Distributed by <a href="https://themewagon.com" target="_blank" rel="noopener noreferrer">ThemeWagon</a>

- Website: [datadropx.net](https://datadropx.net)
- Email: [hello@datadropx.net](mailto:hello@datadropx.net)

If DropX Admin helps you, a star on GitHub is appreciated.
