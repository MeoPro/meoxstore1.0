# DropX Admin — Documentation

Welcome to **DropX Admin**, a free admin dashboard template by
[DataDropX](https://datadropx.net). This guide explains what is in the project
and how to set it up.

## What's included

The repository contains the complete **source code** of the template plus
supporting files. You build and run it yourself.

```
dropx-admin/
│
├── app/                  # Next.js App Router pages & layouts
│   ├── (admin)/          #   Admin area (sidebar + header shell)
│   ├── (auth)/           #   Authentication screens
│   └── page.tsx          #   Root redirect
│
├── components/
│   ├── layout/           # Sidebar, header, page header, breadcrumb
│   ├── providers/        # Theme and store providers
│   └── ui/               # Reusable UI primitives (Button, Card, ...)
│
├── config/               # Navigation & site configuration
├── data/                 # Typed mock data (typed + centralized)
├── lib/                  # Utility helpers (cn, formatters, ...)
├── store/                # Redux store & slices
├── types/                # Global & module-level TypeScript types
│
├── docs/                 # This documentation
├── public/               # Static assets
│
├── .nvmrc                # Recommended Node version
├── package.json          # Dependencies & scripts
├── yarn.lock             # Locked dependency tree
├── LICENSE               # DropX Admin License (MIT-based)
└── README.md             # Quick-start readme
```

### Not included (by design)

- **`node_modules/`** — installed locally with `yarn install`.
- **`.git/`** — repository history is stripped from the distributed package.

> Mobile-friendly, no API keys required except for a few optional features
> (e.g. Google Fonts / external analytics). Leaflet maps use free OpenStreetMap
> tiles so they work immediately without credentials.

## Quick start

```bash
# 1. Install dependencies (recommended, reproducible lockfile)
yarn install

# 2. Development server
yarn dev            # http://localhost:3000

# 3. Production build
yarn build
yarn start
```

## Guides

- [Installation](INSTALLATION.md) — environment setup, package manager, troubleshooting
- [Customization](CUSTOMIZATION.md) — branding, colors, navigation, data, components
- [Deployment](DEPLOYMENT.md) — hosting on Vercel/Netlify/self-hosting

## Version

Current version: **1.0.1** — see [CHANGELOG](../CHANGELOG.md).

## License

Free for personal and commercial use under the [DropX Admin License](../LICENSE),
based on MIT:

1. Keep the "Designed & developed by DataDropX" footer credit and its link to
   [datadropx.net](https://datadropx.net) visible.
2. You may redistribute DropX Admin, original or modified, free of charge,
   including on template marketplaces, as long as the credit stays and DataDropX
   is named as the original author.
3. Do not sell DropX Admin itself as a paid template, theme or UI kit. Selling
   the websites and apps you build with it is fine.
