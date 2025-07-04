# TinyCB V2 (PHP Whitelabel Chaturbate Frontend)
A minimal PHP-powered Chaturbate aggregator for your own whitelabel or promo site.  
Grid view, model profiles, and live filters. All data is local/cached. No database required.

**Demo:** https://tinycb.com/

> If you want to thank me and keep this project alive:
> - Please don't forget to sign up via my [affiliate link](https://chaturbate.com/in/?tour=9O7D&campaign=2DLMP&track=default)
> - And [buy me a coffee](https://coff.ee/tinycb).
>
> _(Seriously, do both. It helps a lot!)_

---

## ✨ Features
- **Lightning-fast local caching:** All cam grid and model data is fetched and stored as flat JSON files for speed and reliability.
- **Live grid filtering:** Filter models instantly by gender, region, room size, age, and tag (#hashtags).
- **Pretty, clean URLs:** Navigate via `/girls/`, `/guys/`, `/trans/`, `/couples/`, or directly by `/model/username`.
- **Clickable tags everywhere:** Hashtags (in grid & sidebar) instantly update filters, for super quick navigation.
- **Auto-refresh mode (desktop):** Optional toggle in the header. When enabled, automatically refreshes the grid every minute—new models appear, offline models disappear, and all data stays up-to-date (subject line, viewer counts, even thumbnails!).
- **Profile fallback for offline models:** If a model goes offline, their profile page (avatar, stats, bio, tags, etc) is still preserved and accessible at `/model/username`.
- **Automated model bio writing (AI):** Generate model bios automatically with OpenAI (e.g. GPT-4/4o/3.5-turbo) or Ollama (local, e.g. Mistral, Gemma, Qwen, etc.), with creative variety and admin control.
- **Modern tooltips & hover highlights:** All tags/controls give clear hover feedback (with colored backgrounds) for a polished feel.
- **Full admin panel:** Edit all nav links, meta tags, site colors, slugs, logo, LLM settings, and more instantly in your browser.
- **"Rewrite all bios" feature:** Regenerate all bios in one batch when you want to improve style or change LLMs/backends.
- **True 404 error handling:** Offline/missing model pages return a real HTTP 404 Not Found code for proper SEO.
- **No database required:** Portable and easy to deploy anywhere PHP runs.

---

## ⚡️ Setup

### 1. **Clone this project**
```bash
git clone https://github.com/YOURUSERNAME/YOURREPO.git
cd YOURREPO
```
Or download and unzip [here](https://github.com/Kudocams/TinyCB/archive/master.zip).

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

### 4. **Generate bios with AI (Optional)**
To generate bios for all models, run:
```bash
php generate-bio.php
```
- This uses the LLM/backend/URL/model/API key you set in admin (see below).
- Runs in batch, saves every 5 bios. Safely resumable!
- Enable "Rewrite all bios" in admin to force all bios to update.

### 5. **.htaccess**
Repo includes a ready-to-go `.htaccess` for clean URLs out of the box.

### 6. **Open in your browser**  
Go to your site root (e.g. `https://yourdomain.com/`) – you’ll see the live cam grid and profiles!

---

## 🔑 Admin/Config

### **Access the Admin Page**
- Go to `/admin.php` (e.g., `https://yourdomain.com/admin.php`)
- **Default admin password:** `changeme`

### **Changing the Admin Password**
- Log in with `changeme`
- Use the “Change Admin Password” fields at the bottom of the admin page.

### **What You Can Configure**
- Site name, affiliate ID, logo, colors, footer
- Nav links: login & broadcast
- Google Analytics code
- Privacy/contact email, SEO meta, verification tags
- Cams per page limit, URL slugs for all categories and models
- **AI/LLM backend:** Provider (OpenAI/ollama), API URL, model name, (and OpenAI API key if needed)
- **"Rewrite all bios"**: Checkbox to force regenerate all bios on next batch
- Homepage/gender/page meta tags, instant logo & favicon upload
- All edits are saved instantly—no manual file editing needed!

---

## ℹ️ Notes

- **AI Provider/model:** Choose from OpenAI (with your own API key) or run locally with Ollama or anything OpenAI-compatible.
- **memory_limit:** Set to at least `256M` or `512M` in `php.ini` as `model_profiles.json` (used for offline profile pages) can quickly hit 50MB+.  
  If you do not have php.ini access, set at the top of your script:
  ```php
  ini_set('memory_limit', '256M');
  ```
- **Mobile design:** Not fully responsive yet—use on desktop for now.
- **No database:** All data/settings are flat files.
- **Grid/listings:** Only models in your latest cache appear in the public grid; profiles for all archived models remain accessible.
- **SEO:** All URLs are pretty (`/girls/page/2` etc.), no query strings.
- **Feature requests, issues, and PRs are welcome!**
- **AI output:** Use safe, on-brand, non-PII prompts and review generated bios regularly if using OpenAI or other cloud providers.

---

## 🤔 Troubleshooting

- Blank grid? Make sure `cache/` is writable and `fetch-and-cache.php` has run.
- AJAX/filters not working? Use browser dev tools to check `/api-proxy.php`.
- Can't write bios/config? Check permissions on `cache/` and `config.php`.
- To reset admin: delete or edit `admin_password_hash` in `config.php`.

---

## 💬 Feedback & Contributions

_Not fully opmized for mobile yet._  
**Issues and PRs are very welcome!**
