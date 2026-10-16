# TimePass MCP Server

This project ships an **MCP (Model Context Protocol) server** so AI clients like
**ChatGPT** and **Claude** can manage the storefront content directly —
create / update / delete **products, categories, reviews**, and edit the site
**settings** (contact details + social links).

- **Endpoint (web apps, token in URL):** `https://timepass-al0s.onrender.com/mcp/<MCP_TOKEN>`  ← use this for claude.ai & ChatGPT with "No sign-in"
- **Endpoint (header auth):** `https://timepass-al0s.onrender.com/mcp` with header `Authorization: Bearer <MCP_TOKEN>`  ← use this for Claude Code / Desktop
- **Built with:** the official [`laravel/mcp`](https://laravel.com/docs/12.x/mcp) package (runs inside the app, shares the live database)

## Available tools

| Module | Tools |
|--------|-------|
| Products | `list_products`, `create_product`, `update_product`, `delete_product` |
| Categories | `list_categories`, `create_category`, `update_category`, `delete_category` |
| Reviews | `list_reviews`, `create_review`, `update_review`, `delete_review` |
| Settings | `get_settings`, `update_settings` |

Changes write to the database and appear on the website immediately. Deletes are
permanent. (The admin panel itself is not exposed — only the public content.)

---

## 1. One-time setup — set the secret token

Pick a strong token (example generator):
```bash
php -r "echo 'mcp_'.bin2hex(random_bytes(24));"
```

Add it in **two** places (they must match):

1. **Render** → your service → **Environment** → add
   `MCP_TOKEN = mcp_xxxxxxxxxxxxxxxxxxxx` → Save (redeploys).
2. **Local `.env`** (optional, only if you test locally) → `MCP_TOKEN=mcp_xxxx...`

> Keep this token secret — anyone with it can edit your store content.
> To rotate it, change `MCP_TOKEN` on Render and update it in your AI client.

---

## 2. Connect from Claude

**Claude Desktop / Claude Code** (supports custom headers):

Add to your MCP client config (e.g. `claude_desktop_config.json`):
```json
{
  "mcpServers": {
    "timepass": {
      "type": "http",
      "url": "https://timepass-al0s.onrender.com/mcp",
      "headers": { "Authorization": "Bearer mcp_xxxxxxxxxxxx" }
    }
  }
}
```

**claude.ai (web) custom connector** — use the **token-in-URL** method:
- **URL:** `https://timepass-al0s.onrender.com/mcp/mcp_xxxxxxxxxxxx` (the token is the last path segment)
- **Authentication:** choose **"No sign-in"**
- Expand **Advanced** → Transport: **Streamable HTTP** → **Add**

---

## 3. Connect from ChatGPT

ChatGPT → **Settings → Connectors / Developer mode → Add custom connector (MCP)**:
- **URL:** `https://timepass-al0s.onrender.com/mcp/mcp_xxxxxxxxxxxx` (token in the URL)
- **Authentication:** **No authentication**

---

## 4. Test it before connecting (recommended)

Use the built-in MCP Inspector:
```bash
php artisan mcp:inspector
```
Point it at the `/mcp` URL with the `Authorization: Bearer` header and confirm the
14 tools list. You can also smoke-test with curl:
```bash
curl -s https://timepass-al0s.onrender.com/mcp \
  -H "Authorization: Bearer mcp_xxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json, text/event-stream" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

---

## Auth note (bearer token vs OAuth)

This server uses a **secret bearer token** — simple and works with Claude
Desktop/Code, the MCP Inspector, ChatGPT's custom connector, and any script that
can send an `Authorization` header.

Some **web connector UIs (claude.ai, ChatGPT) expect OAuth 2.1** instead of a
static header. If your client won't accept a bearer token, the server can be
upgraded to OAuth with **Laravel Passport** (a larger change — adds Passport,
signing keys, and extra Render config). Ask and it can be added.

---

## Example prompts once connected

- "List all products in the `pens` category."
- "Create a product: name 'Blue Gel Pen', category 'pens', price 25, mrp 40, tags best-seller."
- "Mark product id 12 as inactive."
- "Delete review id 5."
- "Update the contact settings: set phone to +91 90000 00000."
