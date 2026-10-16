# Deployment & CI/CD Guide

This project deploys to **Render** (Docker) with a **Neon PostgreSQL** database.
A GitHub Actions pipeline (`.github/workflows/ci-cd.yml`) tests and builds every
change **before** anything reaches the live server, so a broken build can't go live.

## How the pipeline works

```
push / PR to main
      │
      ├─ Test & Build  (install deps, Pint lint, build Vite assets, run tests)   ← HARD GATE
      ├─ Security audit (composer audit + npm audit — advisory)
      │
      └─ Deploy to Render   ← runs ONLY on push to main AND ONLY if "Test & Build" passed
```

- **Pull requests** run tests + build + audit only. They never deploy.
- **Pushes to `main`** run everything and, if tests pass, trigger a Render deploy.
- If a test fails or assets don't build, **the deploy step is skipped** — the live site keeps the last working version.

---

## ⚠️ One-time setup (required)

### 1. Turn OFF Render auto-deploy
Right now Render redeploys on *every* push, even when the code is broken (this is how
the earlier `$middleware` crash went live). We want deploys to happen only through the
tested pipeline.

- Render Dashboard → your service → **Settings** → **Build & Deploy**
- Set **Auto-Deploy** to **No**.

### 2. Create a Render Deploy Hook
- Render Dashboard → your service → **Settings** → **Deploy Hook**
- Copy the URL (looks like `https://api.render.com/deploy/srv-xxxxx?key=yyyyy`).

### 3. Add the hook as a GitHub secret
- GitHub repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**
- **Name:** `RENDER_DEPLOY_HOOK_URL`
- **Value:** the deploy hook URL from step 2.

That is the **only** secret GitHub Actions needs. The pipeline tests against an
in-memory SQLite database, so it needs no real credentials.

---

## Where credentials actually live

| What | Where it goes | Why |
|------|---------------|-----|
| `RENDER_DEPLOY_HOOK_URL` | **GitHub** → Actions secrets | Lets the pipeline trigger a deploy |
| DB password, APP_KEY, Google OAuth, mail, etc. | **Render** → Environment | Render *runs* the app, so the app reads them at runtime |

> The app's runtime secrets do **not** go in GitHub. GitHub only builds/tests and
> presses the "deploy" button. Render runs the container and injects the runtime env.

### Render environment variables to set
Set these in Render → your service → **Environment**. (Your local `.env` is git-ignored
and never uploaded, so these must be configured on Render directly.)

**App**
- `APP_NAME` = TimePass
- `APP_ENV` = `production`
- `APP_KEY` = `base64:...` (generate with `php artisan key:generate --show`)
- `APP_DEBUG` = `false`
- `APP_URL` = `https://timepass-al0s.onrender.com` (your live URL)

**Database (Neon) — the earlier `password authentication failed` error was here**
- `DB_CONNECTION` = `pgsql`
- `DB_HOST` = `ep-small-surf-b4prdg8f.c-6.us-east-2.aws.neon.tech`
- `DB_PORT` = `5432`
- `DB_DATABASE` = `neondb`
- `DB_USERNAME` = `neondb_owner`
- `DB_PASSWORD` = *(exact password from Neon console — reset it there if unsure)*
- `DB_SSLMODE` = `require`

**Auth / Google OAuth**
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` = `https://timepass-al0s.onrender.com/auth/google/callback`

**Sessions / cache / queue — use `file` for speed**
- `SESSION_DRIVER` = `file`  ← **important for speed.** With `database`, every page view makes 3 queries to Neon (≈1 s, because the database is in another region).
- `CACHE_STORE` = `file`
- `QUEUE_CONNECTION` = `sync`
- `LOG_CHANNEL` = `stderr`  ← so logs show up in Render's log viewer

Side effect of `file` sessions: logged-in users sign in again after a deploy or server restart. "Remember me" logins and visitors are not affected.

---

## Performance

Normal page views use **no database queries**:
- Products, categories, settings and reviews are cached (`app/Support/ShopData.php`). The cache clears automatically when anything is saved or deleted (admin panel or MCP), and refreshes every 5 minutes for changes made outside the app.
- Images at `/media/{id}` are served from a disk copy after the first view, without sessions or cookies.
- OPcache is on, and the server runs 4 workers, so requests run in parallel.

**Check performance:** send your MCP token in an `X-Timing` header:
```bash
curl -s -D - -o /dev/null -H "X-Timing: <MCP_TOKEN>" https://timepass-al0s.onrender.com/products | grep -iE "server-timing|x-timing-info"
# server-timing: app;dur=12.3, db;dur=0.0;desc="0 queries"
# x-timing-info: opcache=on; session=file; cache=file; workers=4
```

**Optional (logged-in users, admin and MCP):** these still need the database. Each query is slow because the Render service and the Neon database (`us-east-2`, Ohio) are in different regions. Putting both in the **same region** (for example a Render service in **Ohio**) makes those queries about 100× faster.

**Mail / AWS / Redis** — only if you use them.

---

## Deploying

1. Commit and push to `main` (or merge a PR into `main`).
2. Watch **GitHub → Actions** — the `Test & Build` job runs first.
3. If it's green, the `Deploy to Render` job fires the deploy hook.
4. Watch **Render → Logs** for the build and `php artisan migrate --force`.

### Manual / emergency deploy
Just `curl` the deploy hook (bypasses CI — use only when you know the build is fine):
```bash
curl -X POST "https://api.render.com/deploy/srv-xxxxx?key=yyyyy"
```

---

## Running checks locally before pushing
```bash
composer install
npm install && npm run build      # catch frontend build breaks early
vendor/bin/pint                   # auto-fix code style
php artisan test                  # run the test suite
composer audit                    # check dependency vulnerabilities
```
