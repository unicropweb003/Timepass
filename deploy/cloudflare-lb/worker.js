/**
 * TimePass load balancer — a Cloudflare Worker (free plan) in front of
 * several copies ("origins") of the site. See LOAD-BALANCING.md.
 *
 *  - Spreads requests over the origins listed in the ORIGINS variable.
 *  - Fails over: if an origin errors (5xx), times out or is unreachable, the
 *    request is retried on the next one, and that origin is skipped for a while.
 *  - Caches static files (/build, /images, /media) at Cloudflare's edge.
 *  - A cron trigger pings every origin's /up health check, which also keeps
 *    free instances from going to sleep.
 *
 * Every origin must share the same database, APP_KEY and session store, so a
 * visitor can land on any of them (the Laravel app is set up for this).
 */

const STATIC_PREFIXES = ['/build/', '/images/', '/media/', '/favicon'];
const RETRY_STATUSES = new Set([502, 503, 504]);
const MAX_BODY_BYTES = 8 * 1024 * 1024; // buffer bodies up to 8 MB so POSTs can be retried

// Per-isolate memory of origins that recently failed: origin -> skip-until (ms).
const unhealthy = new Map();

function origins(env) {
  return String(env.ORIGINS || '')
    .split(',')
    .map((o) => o.trim().replace(/\/+$/, ''))
    .filter(Boolean);
}

/** Healthy origins first (in random order, which spreads the load), then the rest as a last resort. */
function orderOrigins(list) {
  const now = Date.now();
  const shuffled = [...list].sort(() => Math.random() - 0.5);
  const healthy = shuffled.filter((o) => (unhealthy.get(o) || 0) <= now);
  const resting = shuffled.filter((o) => (unhealthy.get(o) || 0) > now);
  return [...healthy, ...resting];
}

function markUnhealthy(origin, env) {
  const seconds = Number(env.UNHEALTHY_SECONDS || 30);
  unhealthy.set(origin, Date.now() + seconds * 1000);
}

function isStatic(pathname) {
  return STATIC_PREFIXES.some((p) => pathname.startsWith(p));
}

async function forward(request, origin, body, env, publicUrl) {
  const target = new URL(publicUrl.pathname + publicUrl.search, origin);
  const headers = new Headers(request.headers);
  const clientIp = request.headers.get('cf-connecting-ip');

  // Tell Laravel the public address, so links, redirects and cookies use the
  // load balancer's host instead of the origin's (TrustProxies trusts these).
  headers.set('X-Forwarded-Host', publicUrl.host);
  headers.set('X-Forwarded-Proto', publicUrl.protocol.replace(':', ''));
  if (clientIp) headers.set('X-Forwarded-For', clientIp);
  headers.delete('host');

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), Number(env.TIMEOUT_MS || 15000));
  try {
    return await fetch(target.toString(), {
      method: request.method,
      headers,
      body,
      redirect: 'manual',
      signal: controller.signal,
    });
  } finally {
    clearTimeout(timer);
  }
}

/** Rewrite absolute redirects that point at an origin back to the public host. */
function publicResponse(response, origin, publicUrl) {
  const out = new Response(response.body, response);
  const location = out.headers.get('location');
  if (location && location.startsWith(origin)) {
    out.headers.set('location', publicUrl.origin + location.slice(origin.length));
  }
  out.headers.set('x-served-by', new URL(origin).host);
  return out;
}

async function handle(request, env, ctx) {
  const list = origins(env);
  if (list.length === 0) {
    return new Response('Load balancer has no ORIGINS configured.', { status: 500 });
  }

  const publicUrl = new URL(request.url);
  const cacheable = request.method === 'GET' && isStatic(publicUrl.pathname);
  const cache = caches.default; // works on a custom domain; a no-op on *.workers.dev

  if (cacheable) {
    const hit = await cache.match(request);
    if (hit) return hit;
  }

  // Buffer the body once so the request can be replayed on another origin.
  let body = null;
  if (!['GET', 'HEAD'].includes(request.method)) {
    const length = Number(request.headers.get('content-length') || 0);
    if (length > MAX_BODY_BYTES) {
      return new Response('Request too large.', { status: 413 });
    }
    body = await request.arrayBuffer();
  }

  // GET/HEAD can always be retried. A form POST is only retried when the origin
  // surely did not process it (503 = starting/asleep, or no connection at all),
  // never after a 502/504 or a timeout, so nothing is ever saved twice.
  const idempotent = ['GET', 'HEAD'].includes(request.method);

  let lastError = 'no origin answered';
  for (const origin of orderOrigins(list)) {
    try {
      const response = await forward(request, origin, body, env, publicUrl);
      const retry = idempotent ? RETRY_STATUSES.has(response.status) : response.status === 503;
      if (retry) {
        markUnhealthy(origin, env);
        lastError = `${origin} answered ${response.status}`;
        continue;
      }

      const out = publicResponse(response, origin, publicUrl);
      if (cacheable && response.status === 200) {
        ctx.waitUntil(cache.put(request, out.clone()));
      }
      return out;
    } catch (err) {
      markUnhealthy(origin, env);
      const timedOut = err && err.name === 'AbortError';
      lastError = `${origin}: ${timedOut ? 'timed out' : String(err)}`;
      if (timedOut && !idempotent) break; // it may have been processed; do not resend
    }
  }

  return new Response(
    '<!doctype html><title>Temporarily unavailable</title><meta name="viewport" content="width=device-width">' +
      '<body style="font-family:system-ui;text-align:center;padding:4rem 1rem"><h1>We will be right back</h1>' +
      '<p>The store is waking up or busy. Please refresh in a few seconds.</p></body>',
    { status: 503, headers: { 'content-type': 'text/html; charset=utf-8', 'retry-after': '10', 'x-lb-error': lastError } },
  );
}

/** Cron: ping /up on every origin (health check + keeps free instances awake). */
async function ping(env) {
  await Promise.all(
    origins(env).map(async (origin) => {
      try {
        const res = await fetch(origin + '/up', { headers: { 'user-agent': 'timepass-lb-healthcheck' } });
        console.log(`${origin}/up -> ${res.status}`);
      } catch (err) {
        console.log(`${origin}/up -> ${String(err)}`);
      }
    }),
  );
}

export default {
  fetch: handle,
  scheduled: (event, env, ctx) => ctx.waitUntil(ping(env)),
};
