# Webstack WordPress + Next.js Assignment

This repository contains both required deliverables for the take-home assignment, built from the same WordPress content source:

1. A WordPress + Elementor homepage implementation.
2. A WordPress + WPGraphQL + Next.js homepage implementation that uses GraphQL only.

The repo is organized as a reviewer-friendly monorepo:

- `frontend/`: Next.js 15 + React 19 + TypeScript frontend
- `wordpress/`: local Docker WordPress stack, setup automation, and seeded content
- `docs/`: WordPress notes and screenshot placeholders

## What Is Implemented

- Shared WordPress content source for both parts of the assignment
- Automated local WordPress setup with seeded pages and 6 seeded posts
- Next.js homepage rendering the latest 6 posts from WPGraphQL
- Next.js single post route at `/posts/[slug]`
- Reusable TypeScript component structure
- Responsive layout, loading states, error states, and fallback media handling
- Elementor build notes for assembling the WordPress version against the same content

## Reviewer Quick Start

### Prerequisites

- Node.js 20+
- PNPM
- Docker Desktop

### 1. Install dependencies

```bash
pnpm install
```

### 2. Configure environment variables

Copy `.env.example` to `.env.local`:

```bash
NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL=http://localhost:8090/graphql
```

### 3. Start and seed WordPress

From the repository root:

```powershell
.\wordpress\scripts\setup-wordpress.ps1
```

This script:

- starts MySQL and WordPress with Docker Compose
- installs WordPress core
- enables permalink structure `/postname/`
- installs and activates `elementor` and `wp-graphql`
- creates `Home`, `About`, and `Contact`
- seeds 6 demo posts with excerpts and featured images

Local WordPress credentials:

- URL: `http://localhost:8090`
- Admin user: `admin`
- Admin password: `admin`

### 4. Run the Next.js frontend

```bash
pnpm dev
```

Frontend URL:

- `http://localhost:3000`

### 5. Verify the production build

```bash
pnpm build
pnpm start
```

## Assignment Notes

- The written PDF brief takes precedence over any ambiguity in the design screenshot.
- For that reason, the homepage post grid is intentionally limited to the latest 6 posts.
- The Next.js implementation uses WPGraphQL only and does not use the REST API.

## Repository Structure

```text
/
  frontend/
  docs/
    screenshots/
    wordpress/
  wordpress/
    scripts/
    seed/
  README.md
```

## Frontend Notes

- Homepage data is fetched from WordPress through WPGraphQL only.
- The homepage renders the latest 6 posts.
- `/posts/[slug]` renders full post content.
- Missing media is handled with local SVG fallbacks.
- Route-level loading and error states are included.
- The app is runtime-rendered against WordPress so it can operate without a build-time CMS dependency.

## WordPress / Elementor Notes

Elementor is required for the WordPress version. This repository includes:

- seeded WordPress content
- page setup automation
- design notes for recreating the homepage in Elementor

Supporting docs:

- [docs/wordpress/setup-notes.md](/C:/Users/modis/Desktop/take_home_assignment_Modisa/docs/wordpress/setup-notes.md)
- [docs/wordpress/elementor-build-notes.md](/C:/Users/modis/Desktop/take_home_assignment_Modisa/docs/wordpress/elementor-build-notes.md)

## Verification

These commands passed in this workspace on `2026-04-13`:

- `pnpm lint`
- `pnpm typecheck`
- `pnpm build`

## Deployment

### Next.js

Recommended target: Vercel

- Deploy the `frontend` workspace
- Set `NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL` to the deployed WordPress GraphQL endpoint

### WordPress

Recommended target: any managed WordPress host or small VPS/container host

Fastest temporary review option for this repo: expose the local Docker WordPress instance through `ngrok`, then point the Vercel deployment at the public `https://.../graphql` endpoint.

Minimum requirements:

- WordPress
- Elementor
- WPGraphQL
- Post name permalinks
- the 6 seeded posts or equivalent imported content

## Deployment Sequence

Use this order so the public GraphQL endpoint exists before deploying Next.js.

### 1. Start local WordPress

```powershell
.\wordpress\scripts\setup-wordpress.ps1
```

### 2. Switch WordPress to the public base URL

After `ngrok http 8090` gives you an HTTPS URL:

```powershell
.\wordpress\scripts\set-wordpress-url.ps1 -BaseUrl https://YOUR-NGROK-URL
```

This updates both `home` and `siteurl` so assets, links, and `/graphql` resolve through the public tunnel.

### 3. Verify the public GraphQL endpoint

Open:

```text
https://YOUR-NGROK-URL/graphql
```

Or send a POST query such as:

```json
{"query":"{ posts(first:1){ nodes { title slug } } }"}
```

### 4. Deploy Next.js to Vercel

Set:

```text
NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL=https://YOUR-NGROK-URL/graphql
```

Then deploy the `frontend` app.

### 5. Restore local WordPress URLs when needed

```powershell
.\wordpress\scripts\set-wordpress-url.ps1 -BaseUrl http://127.0.0.1:8090
```

## Remaining Manual Submission Steps

- complete the final Elementor homepage assembly in WordPress admin
- capture the final submission screenshots under [docs/screenshots](/C:/Users/modis/Desktop/take_home_assignment_Modisa/docs/screenshots)
- deploy WordPress and Next.js
- replace the placeholder live URLs below before submission
- push the final repository to GitHub and submit the repo link with screenshots or a short demo video

## Live URLs

- WordPress: `Local Docker setup documented in this README`
- Next.js: `https://frontend-omega-lake-71.vercel.app`

## Suggested Screenshots

- Elementor homepage desktop
- Next.js homepage desktop
- Next.js homepage mobile
- Next.js single post page
- WordPress posts list or editor view

## Assumptions And Tradeoffs

- The screenshot guides layout fidelity, but the written brief controls scope.
- The Next.js app is intentionally kept small and componentized rather than over-engineered.
- Elementor assembly still requires a final browser-based pass in WordPress admin.

## Submission Checklist

See [docs/submission-checklist.md](/C:/Users/modis/Desktop/take_home_assignment_Modisa/docs/submission-checklist.md) for a final pre-submission checklist.
