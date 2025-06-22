/
├── index.php                # Homepage/grid, filtering, AJAX
├── model.php                # Individual cam/model profile page
├── api-proxy.php            # Handles all AJAX model/filter requests via cached .json files
├── fetch-and-cache.php      # Script to populate region/gender (etc.) JSON cache files
├── admin.php                # Admin config page for settings
├── config.php               # Contains site settings, affiliate ids, meta defaults, etc.
├── /cache/                  # Cached Chaturbate API files, e.g.
│   ├── cams_northamerica.json
│   ├── cams_europe_russia.json
│   ├── cams_southamerica.json
│   ├── cams_asia.json
│   └── cams_other.json
├── /assets/
│   ├── style.css
│   ├── admin.css
│   └── logo.png
├── /templates/
│   ├── header.php
│   └── footer.php
├── README.md
└── .htaccess