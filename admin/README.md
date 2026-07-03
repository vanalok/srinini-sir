# Srinivasulu IFS — Admin CMS

A small, self-contained PHP admin for editing the site's content. No framework,
no database (content is stored in the site's existing JSON files). Portable to
GoDaddy shared hosting; runs locally in XAMPP for testing.

## Run it locally (two options)

### Option A — PHP built-in server (quickest)
From the **project root** (the folder that has `index.html`):
```
php -S localhost:8000
```
Then open: **http://localhost:8000/admin/**

### Option B — XAMPP / Apache
1. Copy the whole project folder into `C:\xampp\htdocs\` (e.g. `htdocs\srinivas\`).
2. Start **Apache** in the XAMPP control panel.
3. Open: **http://localhost/srinivas/admin/**

## Login
- **Username:** `admin`
- **Password:** `vanalok@2026`  ← change this before going live

### Change the password
```
php -r "echo password_hash('YOUR-NEW-PASSWORD', PASSWORD_DEFAULT), \"\n\";"
```
Copy the output into `admin/config.php` → `ADMIN_PASS_HASH`.

## What it edits
- **Videos** → `videos.json`  *(built — the working example)*
- Publications, Accomplishments, Honours, Photos, Audios, Blog → coming next

Saving writes directly to the JSON files the website reads, so changes appear on
the site immediately (no rebuild).

## Notes
- The `/admin` folder is **excluded from the Cloudflare deploy** (static hosting
  can't run PHP and would leak the source). On GoDaddy it runs normally.
- Requires PHP 8+ and write permission on the JSON files / `images` / `assets`.
