# Technical Context: Rumb

## Technology Stack

### Backend

- **PHP 7.4+**
  - Native functions (no framework)
  - cURL for API requests
  - Session management (native)

### Database

- **Supabase**
  - PostgreSQL 15+
  - REST API (via PHP cURL)
  - Realtime (WebSocket)
  - Service role key (admin operations)
  - Anon key (client operations)

### Frontend

- **HTML5** (semantic markup)
- **CSS3** (CSS Variables, Grid, Flexbox)
- **Vanilla JavaScript** (ES6+, async/await)
- **Supabase JS Client** (CDN: v2)
- **Web Audio API** (prosedürel ses üretimi)

### Hosting

- **Shared Hosting** (cPanel)
- **PHP 7.4+** support required
- **cURL** enabled
- **Session** support
- **Cron job** access
- **HTTPS** required

## External APIs

### Twitch API

- **OAuth 2.0:**
  - Authorization endpoint: `/oauth2/authorize`
  - Token endpoint: `/oauth2/token`
  - Scope: `user:read:email`
- **Helix API:**
  - Users: `GET /helix/users`
  - Streams: `GET /helix/streams`
  - Auth: Client-ID + Bearer token

### Supabase REST API

- **Base URL:** `{SUPABASE_URL}/rest/v1/`
- **Headers:**

  - `apikey`: Anon or Service key
  - `Authorization`: Bearer token
  - `Content-Type`: application/json
  - `Prefer`: return=representation

- **Operations:**
  - GET: `?select=columns&filter=eq.value`
  - POST: JSON body
  - PATCH: JSON body + filter
  - DELETE: filter in query

### Supabase Realtime

- **Protocol:** WebSocket
- **Library:** @supabase/supabase-js (v2)
- **Channel:** `codes-changes`
- **Event:** `postgres_changes` (INSERT)
- **Filter:** `streamer_id=eq.{ID}`

## Development Setup

### Prerequisites

```bash
- PHP 7.4+
- Composer (not required, no dependencies)
- Supabase account
- Twitch developer account
- Text editor (VSCode recommended)
```

### Local Environment

1. Clone repository
2. Create `.env` from `.env.example`
3. Configure Supabase credentials
4. Configure Twitch OAuth
5. Set admin password hash:
   ```bash
   php -r "echo password_hash('password', PASSWORD_BCRYPT);"
   ```
6. Upload `database/schema.sql` to Supabase
7. Enable Realtime for `codes` table
8. Test locally with PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

### Production Deployment

1. Upload all files to hosting
2. Configure `.env` with production values
3. Set file permissions:
   - `cache/`: 755
   - `.env`: 600
4. Setup cron job:
   ```
   * * * * * /usr/bin/php /path/to/cron.php?key=SECRET
   ```
5. Test Twitch OAuth redirect
6. Verify Realtime connection

## File Structure

```
project/
├── config/
│   ├── config.php         # Main config + session
│   ├── database.php       # Supabase wrapper
│   └── helpers.php        # 30+ utility functions
├── api/
│   ├── auth.php           # Twitch redirect
│   ├── submit-code.php    # Code submission
│   ├── get-active-code.php
│   ├── get-activity.php
│   ├── request-payout.php
│   ├── request-topup.php
│   ├── update-*.php       # Settings APIs
│   ├── calculate-budget.php
│   ├── apply-budget-settings.php
│   ├── get-live-streamers.php
│   ├── get-public-stats.php
│   └── logout.php
├── components/
│   ├── CodeSettings/      # 4 files per component
│   ├── RewardSettings/
│   ├── RandomReward/
│   └── BudgetCalculator/
├── overlay/
│   ├── index.php          # Main overlay
│   ├── themes.css         # 20 themes
│   └── sounds.js          # Web Audio API
├── admin/
│   ├── login.php
│   ├── index.php          # Dashboard
│   ├── users.php
│   ├── codes.php
│   ├── payouts.php
│   ├── balance-topups.php
│   ├── settings.php
│   ├── logout.php
│   └── includes/
│       ├── header.php
│       └── footer.php
├── assets/
│   ├── css/
│   │   ├── style.css + .min.css
│   │   └── landing.css + .min.css
│   └── js/
│       └── main.js + .min.js
├── database/
│   └── schema.sql         # PostgreSQL DDL
├── cache/                 # Auto-created, 755
├── index.php             # Main page
├── streamers.php         # Live streamers
├── callback.php          # OAuth callback
├── cron.php              # Cron job
├── .env                  # Environment (NOT in git)
├── .gitignore
└── README.md
```

## Dependencies

### PHP Extensions

- `curl`: API requests
- `json`: JSON encode/decode
- `session`: Session management
- `openssl`: random_bytes()

### CDN Resources

```html
<!-- Supabase JS Client -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
```

### No Composer

- Pure PHP implementation
- No external PHP packages
- Lightweight and portable

## Performance Considerations

### Optimization Techniques

1. **Database Indexes:** 17+ strategic indexes
2. **File Cache:** 2-second TTL for hot data
3. **Minified Assets:** CSS/JS production versions
4. **Query Optimization:** Select specific columns
5. **Lazy Loading:** Images in streamer list

### Bottlenecks

1. Shared hosting I/O (file cache)
2. Supabase API rate limits
3. Twitch API rate limits
4. Cron frequency (1 minute minimum)

## Browser Compatibility

- **Modern browsers:** Chrome 90+, Firefox 88+, Safari 14+
- **JavaScript:** ES6+ (async/await)
- **CSS:** Grid, Flexbox, CSS Variables
- **Audio:** Web Audio API support required

## Environment Variables

```bash
# Supabase
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_ANON_KEY=eyJ...
SUPABASE_SERVICE_KEY=eyJ...

# Twitch
TWITCH_CLIENT_ID=xxx
TWITCH_CLIENT_SECRET=xxx
TWITCH_REDIRECT_URI=https://domain.com/callback.php

# Admin
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=$2y$10$...

# App
APP_URL=https://domain.com
SESSION_LIFETIME=3600
DEBUG_MODE=false
CRON_SECRET_KEY=random_string
CACHE_ENABLED=true
CACHE_TTL=2
TIMEZONE=Europe/Istanbul
```
