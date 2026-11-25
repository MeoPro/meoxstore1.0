# Deployment

The template is a standard Next.js application and can be deployed to any host
that supports Node.js. Because all demo data is local, no environment variables
are required for a default deployment.

## Build + start (any Node host)

```bash
yarn install
yarn build
yarn start        # production server on :3000
```

Make sure the host uses **Node 20+**.

## Vercel (recommended)

1. Push the repository to Git.
2. Import the repo in Vercel.
3. Framework preset: **Next.js** (auto-detected).
4. Build command: `yarn build`. Leave the output directory as Next.js default.

## Netlify

- Build command: `yarn build`
- Publish directory: (leave default — Next.js on Netlify uses the Netlify Next plugin)

## Docker (self-hosting)

Example `Dockerfile` at the project root:

```dockerfile
FROM node:20-alpine AS build
WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install --frozen-lockfile
COPY . .
RUN yarn build

FROM node:20-alpine
WORKDIR /app
ENV NODE_ENV=production
COPY --from=build /app/.next ./.next
COPY --from=build /app/public ./public
COPY --from=build /app/package.json ./
RUN yarn install --production --frozen-lockfile
EXPOSE 3000
CMD ["yarn", "start"]
```

## Health check

After deploy, visit `/dashboard`. It returns a static-generated page (all 149
routes are statically prerendered), so p95 CDN caching is effective by default.