# TinyCB V2 (PHP Whitelabel Chaturbate Frontend)

A minimal PHP-powered Chaturbate aggregator for your own whitelabel or promo site.  
Grid view, model profiles, and live filters. All data is local/cached. No database required.

If you want to thank me, please don't forget to sign up via my [affiliate link](https://chaturbate.com/in/?tour=9O7D&campaign=2DLMP&track=default)

---

## ⚡️ Setup

### 1. **Clone this project**
```bash
git clone https://github.com/YOURUSERNAME/YOURREPO.git
cd YOURREPO
```

### 2. **Permissions**
Make sure the following are **writable by your web server**:
- `config.php`
- `cache/` directory
- `assets/` directory

### 3. **Fetch/Cron setup**
Run the script to cache live model data:
```bash
php fetch-and-cache.php
```
For live updating, set up a cron to call this script every few minutes (adjust as needed).

### 4. **.htaccess**
The repo includes a working `.htaccess` for clean URLs out of the box.  
**No changes needed** (unless you put this in a subdirectory).

### 5. **Open in your browser**  
Go to your site root (e.g., `https://yourdomain.com/`) – you’ll see the live cam grid!

---

## 🔑 Admin/Config

### **Access the Admin Page**
- Go to `/admin.php` (e.g., `https://yourdomain.com/admin.php`).
- **Default admin password:**  
  ```
  changeme
  ```

### **Changing the Admin Password**
- Log in with `changeme`.
- Use the “Change Admin Password” fields at the bottom of the admin page.
- After saving, your new password is active.

### **What You Can Configure**
- Site name, primary color, logo
- Filters per page
- SEO/meta for homepage and gender pages
- Nav bar links for "Login" and "Broadcast Yourself" (URLs set in the admin panel)
- Affiliate ID, whitelabel domain

_All settings are saved instantly to `config.php`._

---

## ℹ️ Notes

- **Mobile design:** Not fully mobile responsive yet; best viewed on desktop for now.
- **No database:** All data/settings are stored in flat files.
- **Cache:** Only the models listed in your cache are shown. Update with the fetch script.
- **All URLs are pretty (`/girls/page/2`), no query strings for filters or pages._

---

## 🤔 Troubleshooting

- If you get blank model grids: make sure `cache/` is writable and `fetch-and-cache.php` has run.
- If AJAX/filters are blank: check browser dev tools and ensure `/api-proxy.php` is reachable.
- To reset admin: manually delete or edit `admin_password_hash` in `config.php`.

---

## 💬 Feedback & Contributions

*Not mobile yet.*  
**Issues & PRs are welcome!**

---

MIT License.
```
MIT License

Copyright (c) [YEAR] [YOUR NAME OR ORGANIZATION]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
---
