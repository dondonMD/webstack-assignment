# Deployment Runbook

Use this order:

1. Expose local WordPress publicly.
2. Verify the public GraphQL endpoint.
3. Deploy Next.js to Vercel against that public GraphQL URL.
4. Update `README.md` with the live URLs.

## 1. WordPress via ngrok

Install tooling if needed:

```powershell
npm install -g ngrok vercel
```

Authenticate ngrok:

```powershell
ngrok config add-authtoken YOUR_NGROK_AUTHTOKEN
```

Start the tunnel:

```powershell
ngrok http 8090
```

Copy the HTTPS forwarding URL, then switch WordPress to it:

```powershell
.\wordpress\scripts\set-wordpress-url.ps1 -BaseUrl https://YOUR-NGROK-URL
```

## 2. Verify public GraphQL

Open:

```text
https://YOUR-NGROK-URL/graphql
```

Or verify with:

```powershell
Invoke-WebRequest `
  -Uri "https://YOUR-NGROK-URL/graphql" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"query":"{ posts(first:1){ nodes { title slug } } }"}'
```

Expected result: HTTP `200` and a JSON payload containing at least one post node.

## 3. Deploy Next.js to Vercel

From the repo root:

```powershell
cd frontend
vercel
```

Set the production env var when prompted or afterward in the Vercel dashboard:

```text
NEXT_PUBLIC_WORDPRESS_GRAPHQL_URL=https://YOUR-NGROK-URL/graphql
```

If needed, redeploy after setting the env var:

```powershell
vercel --prod
```

## 4. Restore local WordPress URL later

```powershell
.\wordpress\scripts\set-wordpress-url.ps1 -BaseUrl http://127.0.0.1:8090
```

## Final Submission Updates

- Add the GitHub repo URL.
- Add the public WordPress URL.
- Add the Vercel URL.
- Re-check the screenshots and README.
