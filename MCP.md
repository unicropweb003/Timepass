# TimePass MCP Server

An MCP (Model Context Protocol) server built into the TimePass Laravel app. AI clients (Claude, ChatGPT) can manage the store's content with **one natural-language request**, for example:

> Add product "Blue Cotton Shirt", price 799, category Men's Wear

That single call creates the category if it is missing, creates the product, generates an AI product photo, resizes it, converts it to WebP and stores it. The product then shows on the website.

It runs inside the existing app. It uses the same Postgres (Neon) database and the same Render deploy, and needs no separate service.

## Design choices

| Spec item | Implementation | Why |
|---|---|---|
| Python/FastMCP or TypeScript SDK | **PHP, official `laravel/mcp`** | Reuses the existing tables and models directly, with one deploy. |
| Google Imagen | **Google Gemini image model** (`gemini-3.1-flash-lite-image`, configurable) | Google has retired Imagen from the Gemini API. If no key is set, it falls back to free Pollinations. |
| Image storage | **Database** (`media` table, served at `/media/{id}`) | Free, and survives Render's ephemeral disk. |
| Pydantic/Zod validation | Laravel validator | Native to the stack, with AI-readable error messages. |
| Update default image source "ai" | Update tools default to **"keep"** | An unrelated edit (for example a price change) never replaces a photo by accident. Pass `image_source: "ai"` for a new one. |
| `sale_price` / `category_id` / `status` | Mapped onto existing columns | The site stores the selling price in `price` and the struck-through price in `mrp`. Products link to categories by slug. `status` maps to `is_active`. |

## Folder structure

```
app/
  Mcp/
    Servers/StoreServer.php          # registers all tools + instructions
    Support/
      ImagePipeline.php              # ai | url | upload | local -> validate -> resize/WebP -> store
      ImageGenerator.php             # Gemini (Interactions + generateContent) -> Pollinations fallback
      SafeDownloader.php             # https-only, SSRF-safe, 5 MB cap, re-checked redirects
      ImageInput.php                 # shared image_* fields for create/update tools
      Finder.php                     # find by id | slug | exact name, unique slugs
      CategoryInput.php              # category_name -> existing or auto-created category
      Present.php                    # record shapes + "summary + JSON" replies
      SiteContent.php                # About Us + contact info (settings)
    Tools/
      Products/   Create, Update, Delete, Get, List
      Categories/ Create, Update, Delete, Get, List
      About/      GetAboutUs, UpdateAboutUs
      Contact/    GetContactInfo, UpdateContactInfo, List/Get/DeleteContactMessage
      Reviews/    List, Create, Update, Delete
  Http/
    Controllers/McpUploadController.php   # POST /upload -> upload_id (15 min)
    Middleware/McpTokenAuth.php           # bearer / path / query token
    Middleware/LogMcpRequests.php         # one log line per call, no secrets
  Models/Media.php
database/migrations/
  2026_10_16_000001_change_products_image_to_text.php
  2026_10_16_000002_create_media_table.php
  2026_10_16_000003_add_stock_and_category_hierarchy.php
routes/ai.php                        # /mcp, /mcp/{token} (HTTP) + "timepass" (stdio)
tests/Feature/Mcp/StoreServerTest.php
```

## Tools (21)

| Module | Tools |
|---|---|
| Products | `create_product`, `update_product`, `delete_product`, `get_product`, `list_products` |
| Categories | `create_category`, `update_category`, `delete_category`, `get_category`, `list_categories` |
| About Us | `get_about_us`, `update_about_us` |
| Contact | `get_contact_info`, `update_contact_info`, `list_contact_messages`, `get_contact_message`, `delete_contact_message` |
| Reviews | `list_reviews`, `create_review`, `update_review`, `delete_review` |

- `create_product` takes `category_name`. If the category doesn't exist, it is created.
- Get, update and delete accept an **id, slug or exact name** (`product` / `category`).
- Every `delete_*` needs **`confirm: true`** and also deletes the stored image. `delete_category` refuses while it still has products, unless you pass `move_products_to`.
- `list_*` tools support `search`, filters and `page` / `per_page`.
- Every tool replies with a short summary plus the saved record as JSON.

## Image handling

Create and update tools for products and categories, plus `update_about_us`, accept:

| Field | Meaning |
|---|---|
| `image_source` | `ai` · `url` · `upload` · `none` (no image / remove) · `keep`. Create defaults to **ai**, update defaults to **keep**. It is inferred automatically when you pass `image_url` or `image_upload_id`. |
| `image_prompt` | Optional. If missing, a prompt is built from the name and description. Product prompts ask for a packshot with no people. |
| `image_url` | Downloaded server-side, then re-hosted. Never hotlinked. |
| `image_upload_id` | From `POST /upload`, valid for 15 minutes. |
| `image_base64` + `image_filename` | For small images. |
| `local_path` | Only in local stdio mode. |

Rules:
- URLs must be **https**. Hosts that resolve to localhost or private/reserved IPs are blocked, including after redirects (max 3).
- Images are capped at **5 MB**.
- Only **JPEG, PNG and WebP** are accepted, checked by **magic bytes** rather than the extension.
- Every image is resized to **max 1200px wide** and converted to **WebP** (quality 82).
- Replacing an image **deletes the old stored file**.
- If generation or download fails, **the record is still saved** without an image, and the reply says `Image NOT saved: <reason>`.

### Upload endpoint

```bash
curl -X POST https://timepass-al0s.onrender.com/upload \
  -H "Authorization: Bearer $MCP_TOKEN" \
  -F "file=@shirt.jpg"
# -> {"upload_id":"9f2c…","expires_at":"…"}   then pass image_upload_id to a tool
```

It also accepts JSON `{"image_base64": "..."}`, and the token can be sent as `?token=` or as `/upload/<token>`.

## Setup

Add these to your `.env` locally, and to **Render → Environment** in production:

| Variable | Required | Notes |
|---|---|---|
| `MCP_TOKEN` | **yes** | Long random secret: `php -r "echo 'mcp_'.bin2hex(random_bytes(24));"` |
| `GEMINI_API_KEY` | recommended | [aistudio.google.com/apikey](https://aistudio.google.com/apikey). Billing must be enabled for image models. |
| `GEMINI_IMAGE_MODEL` | no | Default `gemini-3.1-flash-lite-image` (cheapest). Alternative: `gemini-nano-banana-2.1`. |
| `POLLINATIONS_TOKEN` | no | Only used by the free fallback. Removes its watermark and 45-second limit. |

Then deploy:

1. `git push` to `main`. GitHub Actions runs the tests, then triggers the Render deploy.
2. The container runs `php artisan migrate --force`, which creates the new columns and the `media` table, then starts the app.
3. If you added variables on Render, Render redeploys automatically.

The Docker image includes GD with WebP support for resizing and conversion.

## Connecting AI clients

**claude.ai (web)**: Settings → Connectors → Add custom connector
- URL: `https://timepass-al0s.onrender.com/mcp/<MCP_TOKEN>`
- Authentication: **No sign-in**
- After adding or changing tools, remove and re-add the connector, then start a **new chat** so Claude reloads the tool list.

**ChatGPT**: Settings → Connectors (Developer mode) → Add MCP connector. Use the same URL with **No authentication**.

**Claude Desktop / Claude Code**, using the header token:
```bash
claude mcp add --transport http timepass https://timepass-al0s.onrender.com/mcp \
  --header "Authorization: Bearer <MCP_TOKEN>"
```

**Local (stdio)**, from this project folder:
```json
{ "mcpServers": { "timepass-local": {
    "command": "php", "args": ["artisan", "mcp:start", "timepass"],
    "cwd": "C:/Users/unicr/TimePass"
} } }
```
Note: locally `.env` points at the **live** Neon database, so local stdio edits change the live site.

**Test with MCP Inspector**: `php artisan mcp:inspector`

## Security notes

- Every HTTP request needs the token. Comparison is timing-safe (`hash_equals`).
- Rate limits: MCP 60 requests/min, `/upload` 30 requests/min.
- Logs contain one line per call (method, tool, status, duration). They never include the token, the arguments or the image data.
- **Caveat:** in the `/mcp/<token>` URL form, the token is part of the path, so any HTTP access log that records URLs (for example Render's request log) can contain it. Prefer the header form where your client supports it, and rotate `MCP_TOKEN` if a log is ever shared.

## Tests

```bash
php artisan test --filter=StoreServerTest
```

These cover: create with AI image (WebP conversion, served from `/media`), create with URL image (re-hosted), create with upload id, upload requiring a token, update (new image replaces and deletes the old one), delete (confirm required, image removed), auto-create versus reuse of a category, image failure still saving the record, SSRF and http blocking, non-image rejection, the Gemini provider, list filters, category delete moving products, and About/Contact updates. All external HTTP is faked.

## 5 example prompts

1. `Add product "Blue Cotton Shirt", price 799, sale price 649, stock 25, category Men's Wear.`
2. `Create a product "Vitamin C Face Wash" for ₹349 in Skin Care using this photo: https://example.com/facewash.jpg`
3. `Give the Keratin Cream product a new AI image of a white cream jar with a gold lid.`
4. `Update the About Us page title to "Our Story" and write a short paragraph about our natural beauty products, with an AI image.`
5. `Change our contact phone to +91 90000 00000, add Instagram https://instagram.com/timepass, then show me the unread contact messages.`
