# TinyCB V2 (PHP Whitelabel Chaturbate Frontend)
A powerful PHP-powered Chaturbate aggregator with advanced analytics and discovery features for your own whitelabel or promo site.  
Grid view, model profiles, live filters, discovery hub, and comprehensive analytics dashboard. All data is local/cached. No database required.

**Demo:** https://tinycb.com/

> If you want to thank me and keep this project alive:
> - Please don't forget to sign up via my [affiliate link](https://chaturbate.com/in/?tour=9O7D&campaign=2DLMP&track=default)
> _(Seriously, It helps a lot!)_

---

## ✨ Features

### 🔍 Discovery Hub & Analytics
- **Advanced Discovery Dashboard:** Interactive analytics hub with real-time insights, popular tags analysis, and model comparison tools
- **Live Auto-Refresh Analytics:** Discovery hub auto-updates every 10 seconds with fresh data and statistics
- **Model Analytics & Tracking:** Comprehensive model performance tracking with enhanced analytics system
- **Heat Map Data Analysis:** Visual analytics for user engagement patterns and model popularity trends
- **AI Status Monitoring:** Advanced AI system status monitoring and management tools

### 🎯 Core Features
- **Lightning-fast local caching:** All cam grid and model data is fetched and stored with sophisticated multi-tier caching for speed and reliability
- **Enhanced Bio Caching System:** Advanced bio cache management with intelligent storage and retrieval
- **Live grid filtering:** Filter models instantly by gender, region, room size, age, and tag (#hashtags)
- **Pretty, clean URLs:** Navigate via `/girls/`, `/guys/`, `/trans/`, `/couples/`, or directly by `/model/username`
- **Clickable tags everywhere:** Hashtags (in grid & sidebar) instantly update filters, for super quick navigation
- **Auto-refresh mode (desktop):** Optional toggle in the header. When enabled, automatically refreshes the grid every minute—new models appear, offline models disappear, and all data stays up-to-date (subject line, viewer counts, even thumbnails!)
- **Profile fallback for offline models:** If a model goes offline, their profile page (avatar, stats, bio, tags, etc) is still preserved and accessible at `/model/username`

### 🤖 AI & Automation
- **Automated model bio writing (AI):** Generate model bios automatically with OpenAI (e.g. GPT-4/4o/3.5-turbo) or Ollama (local, e.g. Mistral, Gemma, Qwen, etc.), with creative variety and admin control
- **Advanced LLM Configuration:** Support for multiple AI providers with flexible model selection and API configuration
- **"Rewrite all bios" feature:** Regenerate all bios in one batch when you want to improve style or change LLMs/backends
- **AI Status Dashboard:** Monitor and manage AI system health and performance

### 🎨 User Experience
- **Modern tooltips & hover highlights:** All tags/controls give clear hover feedback (with colored backgrounds) for a polished feel
- **Responsive Design Elements:** Enhanced UI components with smooth animations and transitions
- **Model Comparison Tools:** Compare multiple models side-by-side with detailed analytics

### ⚙️ Administration & Management
- **Comprehensive Admin Panel:** Edit all nav links, meta tags, site colors, slugs, logo, LLM settings, analytics configuration, and more instantly in your browser
- **Advanced Analytics Management:** Control analytics collection, caching strategies, and data retention policies
- **Enhanced Configuration Options:** Extensive customization options for all system components
- **True 404 error handling:** Offline/missing model pages return a real HTTP 404 Not Found code for proper SEO
- **No database required:** Portable and easy to deploy anywhere PHP runs

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
- `cache/bio/` subdirectory (for enhanced bio caching)
- `cache/analytics/` subdirectory (for analytics data)
- `assets/` directory
- `logs/` directory (for system logging)

```bash
# Quick permission setup:
chmod 755 cache/ assets/ logs/
chmod 755 cache/bio/ cache/analytics/ 2>/dev/null || true
chmod 644 config.php
```

### 3. **Cron Jobs Setup**
Set up the following cron jobs for optimal performance:

#### **Essential Cron Jobs:**

**1. Cache live model data (Every 1-5 minutes for near real-time Discovery Hub):**
```bash
# For maximum freshness (recommended for Discovery Hub):
*/1 * * * * cd /var/www/html && php fetch-and-cache.php

# Or more conservative (standard):
*/5 * * * * cd /var/www/html && php fetch-and-cache.php
```
This fetches fresh model data and maintains `model_profiles.json` for offline profiles. **Run every minute for near real-time Discovery Hub data.**

**2. Generate/update sitemap (Daily):**
```bash
0 2 * * * cd /var/www/html && php generate-sitemap.php
```
Keeps your sitemap.xml current for SEO.

#### **Analytics Cron Jobs (for Discovery Hub features):**

**3. Update analytics data (Every 1-15 minutes for live Discovery Hub):**
```bash
# For maximum Discovery Hub responsiveness (recommended):
*/1 * * * * cd /var/www/html && php extend-model-analytics.php

# Or more conservative:
*/15 * * * * cd /var/www/html && php extend-model-analytics.php
```
Maintains analytics cache for Discovery Hub and heat map data. **Run every minute alongside data fetching for the most responsive Discovery Hub experience.**

> **📊 DISCOVERY HUB PERFORMANCE NOTE:**  
> Running both scripts every minute provides the most responsive Discovery Hub with near real-time analytics, but consider your server resources and API rate limits. You can adjust frequency based on your needs - even every 2-3 minutes will provide excellent responsiveness.

#### **Optional Cron Jobs:**

**4. Generate AI bios (Weekly or as needed):**
```bash
0 3 * * 0 cd /var/www/html && php generate-bio.php
```
Auto-generates bios for new models (only runs if AI/LLM is configured).

#### **Quick Setup Commands:**
Test each script manually first:
```bash
# Test data fetching
php fetch-and-cache.php

# Test sitemap generation  
php generate-sitemap.php

# Test analytics (if enabled)
php extend-model-analytics.php

# Test bio generation (if AI configured)
php generate-bio.php
```

> **CRON TIPS:**  
> - Replace `/var/www/html` with your actual installation path
> - Use full path to PHP binary if needed: `/usr/bin/php` instead of `php`
> - Check cron logs if jobs fail: `tail -f /var/log/cron` or `/var/log/syslog`
> - Test cron syntax with: `crontab -l` to list current jobs

### 4. **Generate bios with AI (Optional)**
To generate bios for all models, run:
```bash
php generate-bio.php
```
- This uses the LLM/backend/URL/model/API key you set in admin (see below).
- Runs in batch, saves every 5 bios. Safely resumable!
- Enable "Rewrite all bios" in admin to force all bios to update.

This feature does not generate model bio details, as those are already provided by the API; it simply composes a textual introduction for the model's bio.

### 5. **Initialize Analytics System (Optional)**
To enable advanced analytics and discovery features:
```bash
php extend-model-analytics.php
```
- Initializes the analytics cache system for enhanced model tracking
- Sets up heat map data collection and model performance analytics
- Required for Discovery Hub functionality

### 6. **.htaccess**
Repo includes a ready-to-go `.htaccess` for clean URLs out of the box.

### 7. **Open in your browser**  
Go to your site root (e.g. `https://yourdomain.com/`) – you'll see the live cam grid, profiles, and Discovery Hub!

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
- **Advanced AI Settings:** Rewrite modes (all/missing/stale/manual IDs), stale bio threshold, manual model targeting
- **Analytics Configuration:** Discovery hub settings, analytics cache management, heat map data controls
- **AI Status Monitoring:** System health monitoring and performance tracking
- Homepage/gender/page meta tags, instant logo & favicon upload
- **Enhanced Cache Management:** Bio cache settings, analytics data retention, performance optimization
- All edits are saved instantly—no manual file editing needed!

---

## ℹ️ Notes

- **AI Provider/model:** Choose from OpenAI (with your own API key) or run locally with Ollama or anything OpenAI-compatible.
- **Discovery Hub:** Click the "🔍 Discovery Hub" button to access advanced analytics, live stats, and model comparison tools.
- **Analytics System:** Enhanced multi-tier analytics with dedicated cache management for performance optimization.
- **memory_limit:** Set to at least `512M` or `1G` in `php.ini` due to expanded analytics data and model profiles cache. Enhanced caching system requires additional memory for optimal performance.  
  If you do not have php.ini access, set at the top of your script:
  ```php
  ini_set('memory_limit', '1G');
  ```
- **Mobile design:** Not fully responsive yet—use on desktop for optimal Discovery Hub and analytics experience.
- **No database:** All data/settings are sophisticated flat file structures with multi-level caching.
- **Grid/listings:** Only models in your latest cache appear in the public grid; profiles for all archived models remain accessible with enhanced bio caching.
- **Analytics Cache:** Heat map data and analytics are stored separately for better performance and data integrity.
- **Auto-refresh:** Discovery Hub features 10-second auto-refresh for real-time analytics insights.
- **SEO:** All URLs are pretty (`/girls/page/2` etc.), no query strings.
- **Feature requests, issues, and PRs are welcome!**
- **AI output:** Use safe, on-brand, non-PII prompts and review generated bios regularly if using OpenAI or other cloud providers.

---

## 🤔 Troubleshooting

- **Blank grid?** Make sure `cache/` is writable and `fetch-and-cache.php` has run.
- **Discovery Hub not loading?** Ensure analytics system is initialized (`php extend-model-analytics.php`) and `cache/analytics/` is writable.
- **AJAX/filters not working?** Use browser dev tools to check `/api-proxy.php`.
- **Analytics not updating?** Check that the analytics cache manager has proper write permissions and system memory is sufficient.
- **Can't write bios/config?** Check permissions on `cache/`, `cache/bio/`, and `config.php`.
- **Discovery Hub auto-refresh issues?** Check browser console for JavaScript errors and ensure adequate system resources.
- **Heat map data missing?** Run analytics initialization and ensure model analytics tracking is enabled.
- **To reset admin:** delete or edit `admin_password_hash` in `config.php`.

---

## 💬 Feedback & Contributions

_Not fully opmized for mobile yet._  
**Issues and PRs are very welcome!**
