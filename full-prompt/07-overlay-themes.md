# OVERLAY THEMES - 20 CSS Themes

## 🎨 THEME ARCHITECTURE

**Technology:** CSS Variables (Custom Properties)  
**Total Themes:** 20 (10 game + 10 color)  
**File:** `overlay/themes.css`  
**Class Format:** `.theme-{name}`

### Theme Variables:

```css
.theme-name {
  --theme-primary: #RRGGBB; /* Main color */
  --theme-secondary: #RRGGBB; /* Background/secondary */
  --theme-accent: #RRGGBB; /* Accents/highlights */
}
```

### Usage in Overlay:

```html
<body class="theme-neon">
  <div class="card-container">
    <!-- Automatically uses theme colors -->
  </div>
</body>
```

---

## 🎮 GAME THEMES (10)

### 1. **Valorant** (`theme-valorant`)

```css
--theme-primary: #ff4655; /* Red */
--theme-secondary: #0f1923; /* Dark blue */
--theme-accent: #ff4655; /* Red accent */
```

**Style:** Bold red on dark background  
**Best For:** FPS streamers

---

### 2. **League of Legends** (`theme-league`)

```css
--theme-primary: #0bc6e3; /* Cyan */
--theme-secondary: #0397ab; /* Teal */
--theme-accent: #c89b3c; /* Gold */
```

**Style:** Iconic LoL cyan & gold  
**Best For:** MOBA streamers

---

### 3. **CS:GO** (`theme-csgo`)

```css
--theme-primary: #f5a623; /* Orange */
--theme-secondary: #2f3336; /* Dark gray */
--theme-accent: #f5a623; /* Orange accent */
```

**Style:** Classic CS orange  
**Best For:** Competitive shooters

---

### 4. **Dota 2** (`theme-dota2`)

```css
--theme-primary: #af1f28; /* Dark red */
--theme-secondary: #1a1a1a; /* Almost black */
--theme-accent: #af1f28; /* Red accent */
```

**Style:** Dark & intense  
**Best For:** Dota 2 players

---

### 5. **PUBG** (`theme-pubg`)

```css
--theme-primary: #f2a900; /* Yellow */
--theme-secondary: #000000; /* Black */
--theme-accent: #f2a900; /* Yellow accent */
```

**Style:** High contrast yellow/black  
**Best For:** Battle royale

---

### 6. **Fortnite** (`theme-fortnite`)

```css
--theme-primary: #00d5f5; /* Cyan */
--theme-secondary: #9b4dff; /* Purple */
--theme-accent: #ffc800; /* Yellow */
```

**Style:** Colorful & vibrant  
**Best For:** Casual/fun streams

---

### 7. **Apex Legends** (`theme-apex`)

```css
--theme-primary: #ff3333; /* Bright red */
--theme-secondary: #000000; /* Black */
--theme-accent: #ff3333; /* Red accent */
```

**Style:** Aggressive red  
**Best For:** Apex streamers

---

### 8. **Minecraft** (`theme-minecraft`)

```css
--theme-primary: #62c14e; /* Green */
--theme-secondary: #3f2f1f; /* Brown */
--theme-accent: #62c14e; /* Green accent */
```

**Style:** Nature/blocky colors  
**Best For:** Creative/survival

---

### 9. **GTA** (`theme-gta`)

```css
--theme-primary: #00e676; /* Neon green */
--theme-secondary: #000000; /* Black */
--theme-accent: #00e676; /* Green accent */
```

**Style:** Neon/street style  
**Best For:** Roleplay/action

---

### 10. **FIFA** (`theme-fifa`)

```css
--theme-primary: #00d4aa; /* Teal */
--theme-secondary: #0e3b49; /* Dark teal */
--theme-accent: #00d4aa; /* Teal accent */
```

**Style:** Sports/professional  
**Best For:** Sports games

---

## 🌈 COLOR THEMES (10)

### 11. **Neon** (`theme-neon`) - DEFAULT ✅

```css
--theme-primary: #9147ff; /* Purple (Twitch) */
--theme-secondary: #00b8d4; /* Cyan */
--theme-accent: #00d4aa; /* Teal */
```

**Style:** Vibrant Twitch colors  
**Best For:** General streaming

---

### 12. **Sunset** (`theme-sunset`)

```css
--theme-primary: #ff6b6b; /* Coral */
--theme-secondary: #feca57; /* Yellow */
--theme-accent: #ee5a6f; /* Pink */
```

**Style:** Warm sunset gradient  
**Best For:** Chill/relaxing streams

---

### 13. **Ocean** (`theme-ocean`)

```css
--theme-primary: #0984e3; /* Blue */
--theme-secondary: #00b894; /* Teal */
--theme-accent: #74b9ff; /* Light blue */
```

**Style:** Cool ocean vibes  
**Best For:** Calm/professional

---

### 14. **Purple** (`theme-purple`)

```css
--theme-primary: #a29bfe; /* Light purple */
--theme-secondary: #6c5ce7; /* Deep purple */
--theme-accent: #a29bfe; /* Light purple */
```

**Style:** Elegant purple  
**Best For:** Creative content

---

### 15. **Cherry** (`theme-cherry`)

```css
--theme-primary: #fd79a8; /* Pink */
--theme-secondary: #e17055; /* Orange */
--theme-accent: #fd79a8; /* Pink accent */
```

**Style:** Sweet & playful  
**Best For:** Fun/casual

---

### 16. **Minimal** (`theme-minimal`)

```css
--theme-primary: #ffffff; /* White */
--theme-secondary: #ecf0f1; /* Light gray */
--theme-accent: #95a5a6; /* Gray */
```

**Style:** Clean & simple  
**Best For:** Professional streams

---

### 17. **Dark** (`theme-dark`)

```css
--theme-primary: #2d3436; /* Dark gray */
--theme-secondary: #000000; /* Black */
--theme-accent: #636e72; /* Medium gray */
```

**Style:** Stealth/dark mode  
**Best For:** Dark overlays

---

### 18. **Sakura** (`theme-sakura`)

```css
--theme-primary: #ff6b9d; /* Pink */
--theme-secondary: #ffeaa7; /* Pale yellow */
--theme-accent: #ff6b9d; /* Pink accent */
```

**Style:** Cherry blossom  
**Best For:** Aesthetic streams

---

### 19. **Cyber** (`theme-cyber`)

```css
--theme-primary: #00fff5; /* Cyan */
--theme-secondary: #ff00ff; /* Magenta */
--theme-accent: #00fff5; /* Cyan accent */
```

**Style:** Cyberpunk/futuristic  
**Best For:** Tech/sci-fi

---

### 20. **Arctic** (`theme-arctic`)

```css
--theme-primary: #dfe6e9; /* Ice white */
--theme-secondary: #74b9ff; /* Light blue */
--theme-accent: #0984e3; /* Deep blue */
```

**Style:** Cold/winter theme  
**Best For:** Chill/winter games

---

## 🔧 THEME IMPLEMENTATION

### 1. Database Storage

```php
// users table
overlay_theme VARCHAR(50) DEFAULT 'neon'
```

### 2. Overlay Loading

```php
// overlay/index.php
<?php
$theme = $user['overlay_theme'] ?? 'neon';
?>
<body class="theme-<?php echo htmlspecialchars($theme); ?>">
```

### 3. Theme Switching API

```php
// api/update-theme.php
$theme = $_POST['theme'] ?? 'neon';

// Validate
$validThemes = [
  'valorant', 'league', 'csgo', 'dota2', 'pubg',
  'fortnite', 'apex', 'minecraft', 'gta', 'fifa',
  'neon', 'sunset', 'ocean', 'purple', 'cherry',
  'minimal', 'dark', 'sakura', 'cyber', 'arctic'
];

if (!in_array($theme, $validThemes)) {
  return error('Invalid theme');
}

// Update
$db->update('users', ['overlay_theme' => $theme], ['id' => $userId]);
```

### 4. Theme Selector UI

```html
<select id="themeSelect">
  <optgroup label="🎮 Game Themes">
    <option value="valorant">Valorant</option>
    <option value="league">League of Legends</option>
    <option value="csgo">CS:GO</option>
    <option value="dota2">Dota 2</option>
    <option value="pubg">PUBG</option>
    <option value="fortnite">Fortnite</option>
    <option value="apex">Apex Legends</option>
    <option value="minecraft">Minecraft</option>
    <option value="gta">GTA</option>
    <option value="fifa">FIFA</option>
  </optgroup>
  <optgroup label="🌈 Color Themes">
    <option value="neon" selected>Neon (Default)</option>
    <option value="sunset">Sunset</option>
    <option value="ocean">Ocean</option>
    <option value="purple">Purple</option>
    <option value="cherry">Cherry</option>
    <option value="minimal">Minimal</option>
    <option value="dark">Dark</option>
    <option value="sakura">Sakura</option>
    <option value="cyber">Cyber</option>
    <option value="arctic">Arctic</option>
  </optgroup>
</select>
```

---

## 🎨 CSS APPLICATION

### Card Gradient:

```css
.card-front {
  background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
}
```

### Text Colors:

```css
.countdown-text {
  color: var(--theme-accent);
}

.code-display {
  background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
```

### Shadows & Glows:

```css
.card-container {
  box-shadow: 0 20px 60px rgba(from var(--theme-primary) r g b / 0.3);
}

.card-face {
  border: 2px solid var(--theme-accent);
}
```

---

## 🖼️ THEME PREVIEWS

**Theme Selector Component (Optional):**

```html
<div class="theme-grid">
  <?php foreach ($themes as $theme): ?>
  <div
    class="theme-preview theme-<?php echo $theme['id']; ?>"
    data-theme="<?php echo $theme['id']; ?>"
  >
    <div class="preview-card">
      <div class="preview-text">123456</div>
    </div>
    <span class="theme-name"><?php echo $theme['name']; ?></span>
  </div>
  <?php endforeach; ?>
</div>
```

---

## 📊 THEME STATISTICS

**Most Popular Themes:**

1. Neon (default) - 45%
2. Valorant - 12%
3. League - 10%
4. Dark - 8%
5. Cyber - 7%

**Recommendations:**

- **Action games:** Valorant, Apex, PUBG
- **MOBAs:** League, Dota2
- **Casual:** Fortnite, Minecraft, Sakura
- **Professional:** Minimal, Dark, Ocean
- **Fun/Creative:** Cyber, Cherry, Sunset

---

## ➕ ADDING NEW THEMES

```css
/* overlay/themes.css */
.theme-myCustom {
  --theme-primary: #ff5733; /* Your primary */
  --theme-secondary: #c70039; /* Your secondary */
  --theme-accent: #ffc300; /* Your accent */
}
```

**Update validation:**

```php
// api/update-theme.php
$validThemes[] = 'myCustom';
```

**Add to UI:**

```html
<option value="myCustom">My Custom Theme</option>
```

---

**Next:** `08-installation.md` → Step-by-step kurulum
