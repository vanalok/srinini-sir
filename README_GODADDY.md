# Deploying to GoDaddy (Deluxe hosting) — with live Views, Likes, Comments & Visitor analytics

Your site is a **static HTML site** plus a small **PHP + MySQL layer** that adds:

- **Views / Likes / Comments** on each blog post
- **Visitor tracking** and an **Analytics** dashboard in the admin panel
- A **comment moderation** screen

GoDaddy Deluxe (cPanel + PHP + MySQL) runs all of this. Cloudflare could not (it has no database) — the engagement widget simply stays hidden there.

---

## 0. Before you start — 2 security must-dos

1. **Change the admin password.** The current one is `vanalok@2026`. Generate a new hash:
   - Locally: `php -r "echo password_hash('YOUR-NEW-PASSWORD', PASSWORD_DEFAULT);"`
   - Or use https://bcrypt-generator.com/ (cost 10).
   - Paste it into `admin/config.php` → `ADMIN_PASS_HASH`.
2. **Revoke the old Wix API key** at https://manage.wix.com/account/api-keys (the backup is done; the key is no longer needed).

---

## 1. Create the MySQL database (cPanel)

1. Log in to GoDaddy → **cPanel**.
2. Open **MySQL® Databases**.
3. **Create New Database** → name it e.g. `srinivas_cms` (cPanel prefixes it, so the real name is like `cpuser_srinivas_cms` — note the full name).
4. Under **Add New User**, create a user + strong password (note the full username, e.g. `cpuser_cmsadmin`).
5. Under **Add User To Database**, add that user to the database with **ALL PRIVILEGES**.

## 2. Import the tables

1. In cPanel open **phpMyAdmin**.
2. Select your new database on the left.
3. Click the **Import** tab → choose **`api/schema.sql`** from this project → **Go**.
   - Creates 4 tables: `engagement`, `likes_by_visitor`, `comments`, `visits`.

## 3. Point the app at your database

Edit **`api/db.php`** and set the four constants to what you created in step 1:

```php
const DB_HOST = 'localhost';              // GoDaddy usually 'localhost'
const DB_NAME = 'cpuser_srinivas_cms';    // full name incl. prefix
const DB_USER = 'cpuser_cmsadmin';
const DB_PASS = 'the-strong-password';
```

> If `localhost` fails, check cPanel → MySQL Databases for the exact host; some plans use `127.0.0.1`.

## 4. Upload the site

Upload the **whole project folder** to `public_html` (via cPanel **File Manager** → Upload + Extract a zip, or FTP/FileZilla).

**Do NOT upload these** (they are dev-only / already excluded from Cloudflare too):
- `.git/`, `.wrangler/`, `node_modules/`
- `wrangler.toml`, `.assetsignore`
- The big `895-page` PDF if you don't need it online

Keep `admin/`, `api/`, `posts/`, `assets/`, and all the HTML/CSS/JS/images.

## 5. Import your historical Wix numbers (one click)

So views/likes don't restart from zero:

1. Go to `https://yourdomain.com/admin/` and log in.
2. Open **Analytics → “⇪ Import Wix stats”** (or visit `/admin/seed.php`).
3. Click **Import now**. This loads **4,624 views** and **29 likes** across your 117 posts as the baseline.
   - Safe to run once; re-running never lowers a live count.

## 6. Point your domain

In GoDaddy, set your domain's document root to `public_html` (default) — the site is served from `index.html`. Enable **SSL** (cPanel → SSL/TLS Status → run AutoSSL) so it's `https://`.

---

## How it works once live

| Feature | File(s) | Notes |
|---|---|---|
| Track a visit + count a view | `api/track.php` | View is de-duplicated per visitor+page per 6h |
| Like / unlike a post | `api/like.php` | One like per visitor (cookie `sr_vid`) |
| Read / post comments | `api/comments.php` | New comments start **pending** (need approval) |
| Front-end widget | `app.js` + `design-system.css` | Injects the views/likes/comments bar on post pages; silently does nothing if the API/DB is absent |
| Analytics dashboard | `admin/analytics.php` | Visitors, views, likes, 14-day chart, top posts, recent visits |
| Comment moderation | `admin/comments.php` | Approve / spam / delete |

**Spam protection on comments:** hidden honeypot field + max 5 comments/hour per visitor + manual approval.

---

## Troubleshooting

- **“Database not connected”** in admin → recheck the 4 values in `api/db.php` and that `schema.sql` was imported.
- **Views not counting** → open browser dev-tools → Network; `track.php` should return `200` with JSON. A `500` usually means wrong DB credentials.
- **Comments don't appear** → they're **pending**; approve them in **Admin → Comments**.
- **Admin login fails** → confirm `ADMIN_PASS_HASH` in `admin/config.php` is the hash of the password you're typing (not the plain password).
