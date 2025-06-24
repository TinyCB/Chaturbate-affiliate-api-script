# TinyCB V2 (PHP Whitelabel Chaturbate Frontend)

A minimal PHP-powered Chaturbate aggregator for your own whitelabel or promo site.  
Grid view, model profiles, and live filters. All data is local/cached. No database required.

**Demo:** https://tinycb.com/

If you want to thank me and keep this project alive, please don't forget to sign up via my [affiliate link](https://chaturbate.com/in/?tour=9O7D&campaign=2DLMP&track=default).  
In addition to signing up via my affiliate link, I would also appreciate it if you could buy me a coffee [here](https://coff.ee/tinycb).

---

## ✨ Features

- **Lightning-fast local caching:** All cam grid and model data is fetched and stored as flat JSON files for speed and reliability.
- **Live grid filtering:** Filter models instantly by gender, region, room size, age, and tag (#hashtags).
- **Pretty, clean URLs:** Navigate via `/girls/`, `/guys/`, `/trans/`, `/couples/`, or directly by `/model/username`.
- **Clickable tags everywhere:** Hashtags (in grid & sidebar) instantly update filters, for super quick navigation.
- **Auto-refresh mode (desktop):** Optional toggle in the header. When enabled, automatically refreshes the grid every minute—new models appear, offline models disappear, and all data stays up-to-date (subject line, viewer counts, even thumbnails!).
- **Profile fallback for offline models:** If a model goes offline, their profile page (avatar, stats, bio, tags, etc) is still preserved and accessible at `/model/username`.
- **Modern tooltips & hover highlights:** All tags/controls give clear hover feedback (with colored backgrounds), and tooltips use Font Awesome icons for a polished feel.
- **Full admin panel:** Edit all nav links, meta tags, site colors, slugs, logo, and more—instantly, via the browser.
- **True 404 error handling:** Offline/missing model pages return a real HTTP 404 Not Found code for proper SEO.
- **No database required:** Portable and easy to deploy anywhere PHP runs.

---

## ⚡️ Setup

### 1. **Clone this project**
```bash
git clone https://github.com/YOURUSERNAME/YOURREPO.git
cd YOURREPO
```
Or, download and unzip the archive [here](https://github.com/Kudocams/TinyCB/archive/master.zip).

After downloading, [sign up at Chaturbate](https://chaturbate.com/in/?track=default&tour=9O7D&campaign=2DLMP) to get your affiliate ID.

### 2. **Permissions**
Make sure the following are **writable by your web server**:
- `config.php`
- `cache/` directory (create manually if needed; this is where cached files and offline profiles are stored)
- `assets/` directory

### 3. **Fetch/Cron setup**
Run the script to cache live model data:
```bash
php fetch-and-cache.php
```

> **NOTE:**  
> The script also maintains `model_profiles.json` (an archive of all models ever seen), which is used to display offline profile pages.

Set up a cron job to call this script every few minutes (adjust as desired for freshness).

### 4. **.htaccess**
The repo includes a ready-to-go `.htaccess` for clean URLs out of the box.  
No changes needed unless you install in a subdirectory.

### 5. **Open in your browser**  
Browse to your site root (e.g., `https://yourdomain.com/`) – you’ll see the live cam grid!

---

## 🔑 Admin/Config

### **Access the Admin Page**
- Go to `/admin.php` (ex: `https://yourdomain.com/admin.php`)
- **Default admin password:** `changeme`

### **Changing the Admin Password**
- Log in with `changeme`
- Use the “Change Admin Password” fields at the bottom of the admin page.
- After saving, your new password is instantly active.

### **What You Can Configure**

- Site name, affiliate ID, and main logo
- Primary color and footer text
- Nav bar login & broadcast links
- Google Analytics code
- Privacy policy/contact email
- Google & Bing site verification tags
- “Cams Per Page” grid limit
- Whitelabel domain for embeds
- URL slugs for girls, guys, trans, couples, model profiles
- Homepage meta title & description
- Per-gender page meta title & description
- Change admin password (for panel access)
- Instant logo upload (PNG)

_All edits are live—no need to edit files manually!_

---

## ℹ️ Notes

- **Mobile design:** Not fully responsive yet—best viewed on desktop for now.
- **No database:** All data/settings stored as flat files.
- **Cached listings/grid:** Only models present in your most recent cache appear in the grid. Update with `fetch-and-cache.php`.
- **Offline profiles:** Only *online* models appear in the grid, but the profile page for *any* model ever seen is preserved and accessible (does **not** show cam if offline).
- All URLs are pretty (`/girls/page/2` etc.), no query strings.
- More admin customization is coming in future versions.
- Feature requests & issues are very welcome!

---

## 🤔 Troubleshooting

- Blank grid? Make sure `cache/` is writable and `fetch-and-cache.php` has run.
- AJAX/filters blank? Check browser dev tools and confirm `/api-proxy.php` is reachable.
- To reset admin: manually delete or edit `admin_password_hash` in `config.php`.

---

## 💬 Feedback & Contributions

_Not mobile yet._  
**Issues and PRs are very welcome!**
