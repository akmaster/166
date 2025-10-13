# INSTALLATION GUIDE - Step by Step

## 📋 PREREQUISITES

### Requirements:

- ✅ **PHP 7.4+** (shared hosting)
- ✅ **Supabase Account** (free tier OK)
- ✅ **Twitch Developer Account**
- ✅ **Domain with HTTPS** (required for OAuth)
- ✅ **cPanel or FTP access**
- ✅ **cron-job.org account** (or cPanel cron)

### Skills Needed:

- Basic file upload (FTP/cPanel)
- Copy/paste configuration
- SQL query execution (Supabase)
- No advanced coding required!

---

## 🚀 INSTALLATION STEPS

### STEP 1: SUPABASE SETUP (15 min)

#### 1.1 Create Project

1. Go to [supabase.com](https://supabase.com)
2. **Sign up / Login**
3. Click **"New Project"**
4. Fill details:
   - **Name:** `twitch-code-reward`
   - **Database Password:** Strong password (save it!)
   - **Region:** Closest to you
   - **Plan:** Free tier
5. Wait ~2 minutes for project creation

#### 1.2 Get Credentials

1. Go to **Settings** → **API**
2. Copy these values:
   ```
   Project URL: https://xxxxx.supabase.co
   anon/public key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   service_role key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```
3. **Save them** → You'll need for `.env` file

#### 1.3 Create Database Tables

1. Go to **SQL Editor** (left sidebar)
2. Click **"New Query"**
3. Open `database/schema.sql` from project files
4. **Copy all content** (181 lines)
5. **Paste** into Supabase SQL Editor
6. Click **"RUN"**
7. **Success!** → 6 tables created

**Verify:**

- Go to **Table Editor**
- You should see: `users`, `codes`, `submissions`, `payout_requests`, `balance_topups`, `settings`

#### 1.4 Enable Realtime (CRITICAL!)

1. Go to **Database** → **Replication**
2. Find **Publications** section
3. Click **`supabase_realtime`** (shows "0 tables")
4. **Check** ✅ the `codes` table
5. Click **"Save"**
6. **Success!** → Overlay will now work

---

### STEP 2: TWITCH APP SETUP (10 min)

#### 2.1 Create Twitch App

1. Go to [dev.twitch.tv/console](https://dev.twitch.tv/console)
2. **Login** with Twitch account
3. Click **"Register Your Application"**
4. Fill form:
   - **Name:** `Twitch Code Reward` (unique name)
   - **OAuth Redirect URLs:** `https://yourdomain.com/callback.php`
   - **Category:** Website Integration
   - **Client Type:** Confidential
5. Click **"Create"**

#### 2.2 Get Credentials

1. Click **"Manage"** on your new app
2. Copy **Client ID**
3. Click **"New Secret"** → Copy **Client Secret**
4. **Save both** → Needed for `.env`

**⚠️ CRITICAL:** Redirect URL MUST match exactly!

```
✅ Good: https://yourdomain.com/callback.php
❌ Bad:  http://yourdomain.com/callback.php (no HTTPS)
❌ Bad:  https://www.yourdomain.com/callback.php (www)
```

---

### STEP 3: FILE UPLOAD (5 min)

#### 3.1 Upload Files

**Method 1: cPanel File Manager**

1. Login to cPanel
2. Go to **File Manager**
3. Navigate to `public_html/`
4. Upload **all project files** (ZIP and extract)

**Method 2: FTP**

1. Use FileZilla/WinSCP
2. Connect to your server
3. Upload to `public_html/` or `www/`

**Folder Structure After Upload:**

```
public_html/
├── index.php
├── callback.php
├── cron.php
├── .env (create next step!)
├── config/
├── database/
├── api/
├── admin/
├── components/
├── overlay/
├── assets/
└── cache/ (create if not exists, 755 permissions)
```

#### 3.2 Set Permissions

```bash
# Cache folder must be writable
chmod 755 cache/

# Or via cPanel File Manager:
# Right-click cache/ → "Change Permissions" → 755
```

---

### STEP 4: CONFIGURATION (.env) (5 min)

#### 4.1 Create .env File

1. In `public_html/`, create file named `.env`
2. Paste this template:

```env
# Supabase Configuration
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Twitch OAuth Configuration
TWITCH_CLIENT_ID=your_client_id_here
TWITCH_CLIENT_SECRET=your_client_secret_here
TWITCH_REDIRECT_URI=https://yourdomain.com/callback.php

# Admin Configuration
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

# Application Settings
APP_URL=https://yourdomain.com
SESSION_LIFETIME=3600
DEBUG_MODE=false
TIMEZONE=Europe/Istanbul

# Cron Security
CRON_SECRET_KEY=your_random_secret_here_min_32_chars
```

#### 4.2 Fill in YOUR Values

**Replace:**

- `SUPABASE_URL` → From Supabase Step 1.2
- `SUPABASE_ANON_KEY` → From Supabase Step 1.2
- `SUPABASE_SERVICE_KEY` → From Supabase Step 1.2
- `TWITCH_CLIENT_ID` → From Twitch Step 2.2
- `TWITCH_CLIENT_SECRET` → From Twitch Step 2.2
- `TWITCH_REDIRECT_URI` → Your domain + `/callback.php`
- `APP_URL` → Your domain (no trailing slash)
- `TIMEZONE` → Your timezone ([list](https://www.php.net/manual/en/timezones.php))
- `CRON_SECRET_KEY` → Random 32+ char string

#### 4.3 Generate Admin Password

**Option 1: Online (quick)**

1. Go to [bcrypt-generator.com](https://bcrypt-generator.com/)
2. Enter your desired password
3. Copy the hash
4. Replace `ADMIN_PASSWORD_HASH` value

**Option 2: PHP (secure)**

```php
<?php
echo password_hash('YourPasswordHere', PASSWORD_DEFAULT);
?>
```

Run this PHP script, copy output

**Default password:** `password` (hash provided in template)  
**⚠️ CHANGE THIS IN PRODUCTION!**

---

### STEP 5: TEST INSTALLATION (5 min)

#### 5.1 Test Homepage

1. Go to `https://yourdomain.com`
2. **Expected:** Landing page with login button
3. **If error:** Check `.env` file, PHP version

#### 5.2 Test Twitch Login

1. Click **"Twitch ile Giriş"**
2. **Expected:** Redirect to Twitch
3. Login with Twitch
4. **Expected:** Redirect back to dashboard
5. **Success!** → You're now in the system

#### 5.3 Test Admin Panel

1. Go to `https://yourdomain.com/admin/`
2. Login:
   - Username: `admin`
   - Password: `password` (or your custom)
3. **Expected:** Admin dashboard
4. **Success!** → You can manage the system

#### 5.4 Test Overlay

1. In dashboard (as streamer), copy **Overlay URL**
2. Paste in browser
3. **Expected:** Blank screen (no code yet)
4. Go to Admin → Codes → "Manuel Kod Gönder"
5. Select your user, send code
6. **Expected:** Code appears on overlay!
7. **Success!** → Realtime working

---

### STEP 6: CRON JOB SETUP (10 min)

#### Option A: cron-job.org (Recommended)

1. Go to [cron-job.org](https://cron-job.org)
2. **Sign up / Login**
3. Click **"Create Cronjob"**
4. Fill form:
   - **Title:** Twitch Code Generator
   - **Address:** `https://yourdomain.com/cron.php?secret=YOUR_CRON_SECRET`
   - **Schedule:** Every 1 minute
   - **Enabled:** ✅
5. Click **"Create"**
6. **Success!** → Codes will auto-generate

**Test:**

- Wait 1 minute
- Go to Admin → Codes
- **Expected:** New codes every minute (if users have balance)

#### Option B: cPanel Cron (Alternative)

1. cPanel → **Cron Jobs**
2. **Add New Cron Job:**
   - **Minute:** `*/1` (every minute)
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:**
     ```bash
     curl "https://yourdomain.com/cron.php?secret=YOUR_CRON_SECRET" > /dev/null 2>&1
     ```
3. Click **"Add"**

---

### STEP 7: OBS OVERLAY SETUP (5 min)

#### 7.1 Get Overlay URL

1. Login to your streamer account
2. Dashboard → **Overlay URL** bölümünde copy link
3. **Format:** `https://yourdomain.com/overlay/?token=xxx`

#### 7.2 Add to OBS

1. Open **OBS Studio**
2. **Sources** → **+ Add** → **Browser**
3. Name: `Code Reward Overlay`
4. **URL:** Paste your overlay URL
5. **Width:** 1920
6. **Height:** 1080
7. **FPS:** 30
8. ✅ **Shutdown source when not visible**
9. ✅ **Refresh browser when scene becomes active**
10. Click **OK**

#### 7.3 Position

1. **Right-click** overlay source → **Transform** → **Edit Transform**
2. Position: **Bottom Right** (or your preference)
3. Size: Lock aspect ratio
4. **Done!**

#### 7.4 Test

1. Admin panel → Send test code
2. **Expected:** Code appears on OBS overlay
3. **Success!** → Ready to stream!

---

## 🔧 POST-INSTALLATION

### 1. Security Checklist

- [ ] Changed admin password
- [ ] `.env` file NOT accessible via browser (add `.htaccess` rule)
- [ ] `DEBUG_MODE=false` in production
- [ ] HTTPS enabled
- [ ] Strong `CRON_SECRET_KEY` (32+ chars)

### 2. Balance Top-up

**As Streamer:**

1. Go to Admin → Balance Top-ups
2. Request balance (e.g., 100 TL)
3. Upload payment proof
4. Admin approves → Balance added

### 3. Configure Settings

**Admin Panel → Settings:**

- Payout Threshold (default: 5.00 TL)
- Default Reward Amount (default: 0.10 TL)
- Default Code Duration (default: 30s)
- Default Code Interval (default: 600s)

**Streamer Panel → My Settings:**

- Custom Reward Amount
- Code Timing (countdown, duration, interval)
- Random Reward (min/max)
- Sound Settings
- Overlay Theme

### 4. Test Full Flow

1. **Streamer:** Add balance
2. **Streamer:** Configure settings
3. **Cron:** Auto-generate code
4. **Overlay:** Code appears
5. **Viewer:** Submit code
6. **Viewer:** Balance updated
7. **Viewer:** Request payout
8. **Admin:** Approve payout

---

## ❓ TROUBLESHOOTING

### Problem: "Twitch OAuth Failed"

**Solution:**

- Check `TWITCH_CLIENT_ID` and `TWITCH_CLIENT_SECRET`
- Verify `TWITCH_REDIRECT_URI` matches exactly
- Ensure HTTPS is working

### Problem: "Database Connection Failed"

**Solution:**

- Check Supabase credentials in `.env`
- Verify Supabase project is active
- Test with: `https://yourdomain.com/api/get-public-stats.php`

### Problem: "Overlay Not Showing Codes"

**Solution:**

- Enable Realtime in Supabase (Step 1.4)
- Check overlay URL has correct token
- Clear browser cache
- Check console for errors (F12)

### Problem: "Cron Not Working"

**Solution:**

- Verify cron URL includes `?secret=YOUR_SECRET`
- Check cron is running every 1 minute
- Test manually: Visit cron URL in browser
- Check `DEBUG_MODE=true` to see detailed logs

### Problem: "Codes Expire Immediately"

**Solution:**

- Timezone issue! Check `TIMEZONE` in `.env`
- Must match your server timezone
- Verify UTC handling in code (see 01-overview.md Critical Warnings)

---

## 📚 NEXT STEPS

1. **Read:** `01-overview.md` → Understand critical warnings
2. **Customize:** Overlay theme, sounds, settings
3. **Test:** Full viewer/streamer/admin flow
4. **Go Live:** Start streaming with codes!
5. **Monitor:** Admin panel for stats and issues

---

## 🆘 SUPPORT

**Documentation:**

- `full-prompt/MASTER.md` → Complete reference
- `full-prompt/01-overview.md` → Critical warnings

**Common Issues:**

- See "KRİTİK UYARILAR" in `01-overview.md`
- Check browser console (F12) for errors
- Enable `DEBUG_MODE=true` for detailed logs

**File Structure:**

- See `02-file-structure.md` for all files

---

**Next:** `09-helpers.md` → Helper functions reference
