# Load balancing TimePass

How to run the store as **several copies (instances)** with a **load balancer** in
front, on a small budget. The code is already prepared for it; you only choose a
plan and set environment variables.

```
                          ┌──► Instance A ──┐
 Visitors ──► Load        ├──► Instance B ──┼──► Neon Postgres (shared: data + images)
              balancer ───┘                 └──► Shared sessions (Redis or the database)
              checks /up, skips a broken instance, caches images/CSS
```

## What the app already does for this

| Need | How it is handled |
|---|---|
| Images must not live on one instance's disk | Admin uploads, MCP uploads and AI images are stored in the database (`media` table) and served at `/media/{id}`. Each instance keeps a local copy only as a speed-up. |
| Edits must show on every instance | `ShopData` caches the catalogue per instance and checks a shared **data version** every 15 s. An edit publishes a new version, so every instance refreshes within 15 s (the instance that made the edit refreshes immediately). |
| Only one instance migrates the shared database | `RUN_MIGRATIONS=false` on extra instances; `migrate --isolated` as a safety net. |
| Links and redirects use the public address | `TrustProxies` reads `X-Forwarded-*`; `APP_FORCE_URL=true` forces every link to `APP_URL`. |
| Health check | `GET /up` (used by the load balancer). |
| Logins must work on any instance | Use a shared session store: `SESSION_DRIVER=redis` or `database` (not `file`). |

---

## Plan A: free (₹0): Cloudflare Worker + two free hosts

Good when you have no budget. The Worker in [deploy/cloudflare-lb](deploy/cloudflare-lb) is the
load balancer (Cloudflare free plan: 100,000 requests/day).

```
Visitors ──► Cloudflare Worker "timepass-lb" ──► Origin A: Render (free)  ┐
             random spread + failover          ──► Origin B: Koyeb/other  ┘──► Neon
             cron pings /up every 5 min (keeps free instances awake)
```

1. **Second origin.** Deploy the same GitHub repo (it has a `Dockerfile`) on a second
   free host, e.g. Koyeb or another provider with a free Docker web service. Render's
   free hours only cover one always-on service, so use a different provider for B.
2. **Environment on both origins** (copy from Render → Environment):
   - Same `APP_KEY`, `DB_*` (Neon), `MCP_TOKEN`, `GOOGLE_*`, `GEMINI_API_KEY`.
   - `SESSION_DRIVER=database` (shared through Neon, free).
     Or a free Redis (e.g. Upstash): `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `REDIS_URL=…`.
   - `APP_URL=<the Worker's address>` and `APP_FORCE_URL=true`.
   - On **origin B only**: `RUN_MIGRATIONS=false`.
3. **Deploy the Worker** (needs Node.js once, on any computer):
   ```bash
   cd deploy/cloudflare-lb
   npx wrangler login
   # edit wrangler.toml → ORIGINS = "https://timepass-al0s.onrender.com,https://<origin-b-url>"
   npx wrangler deploy
   ```
   It prints the address, e.g. `https://timepass-lb.<you>.workers.dev`.
4. **Google login:** in Google Cloud Console add
   `https://<worker-address>/admin/auth/google/callback` as an authorised redirect URI.
5. **Check:** open the Worker address and look at the `x-served-by` response header;
   reload a few times and it alternates between A and B. Stop one origin and the site
   keeps working.
6. Optional: put your own domain on Cloudflare (free) and uncomment `routes` in
   `wrangler.toml`. Then images, CSS and `/media` are also cached at Cloudflare's edge.

Limits: free hosts are small and may sleep. The Worker retries another origin and
the cron keeps them awake. With `SESSION_DRIVER=database` each request does one
small session query to Neon.

## Plan B: low cost (about $14/month): Render's built-in load balancer

Simplest when you can pay a little. Render balances between instances itself; no Worker needed.

1. Change the Render service to a paid instance type (Starter) and, ideally, recreate it
   in **Ohio**, the same region as Neon (`us-east-2`). Every database query gets much faster.
2. Create a **Render Key Value** (Redis) instance (a free tier exists) in the same region.
3. Render → Environment:
   ```
   SESSION_DRIVER=redis
   CACHE_STORE=redis
   REDIS_URL=<Key Value internal URL>
   RUN_MIGRATIONS=false
   ```
   and set **Settings → Pre-Deploy Command** to:
   `php artisan migrate --force && php artisan db:seed --force`
4. **Settings → Scaling:** set instances to **2** (autoscaling needs a higher Render plan).
5. Render routes traffic only to healthy instances. Set **Health Check Path** to `/up`.

Prices and free tiers change; check the providers' pricing pages before choosing.

## Plan C: before adding instances, get more from one

Often enough for a small store and free:
- Render free plan → paid Starter (no sleeping, no cold starts) in the same region as Neon.
- Cloudflare in front of your domain (free) to cache images and CSS.
- `SESSION_DRIVER=file`, `CACHE_STORE=file` are fine while there is **one** instance.

## Checklist when adding an instance

- [ ] Same `APP_KEY` and database on every instance
- [ ] `SESSION_DRIVER` is `redis` or `database` (never `file` with 2+ instances)
- [ ] `RUN_MIGRATIONS=false` everywhere except one instance (or a Pre-Deploy Command)
- [ ] `APP_URL` = public address, `APP_FORCE_URL=true` when behind the Worker
- [ ] Google OAuth redirect URI updated to the public address
- [ ] `/up` returns 200 on every instance
