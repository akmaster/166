<?php
/**
 * Language Configuration
 * 
 * i18n system for multi-language support
 */

// Supported languages
define('SUPPORTED_LANGUAGES', ['tr', 'en']);
define('DEFAULT_LANGUAGE', 'tr');

/**
 * Get current language
 * Priority: URL param > Cookie > Browser > Default
 */
function getCurrentLanguage() {
    // 1. URL parameter (?lang=en)
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGUAGES)) {
        $lang = $_GET['lang'];
        setcookie('user_lang', $lang, time() + (365 * 24 * 60 * 60), '/');
        return $lang;
    }
    
    // 2. Cookie
    if (isset($_COOKIE['user_lang']) && in_array($_COOKIE['user_lang'], SUPPORTED_LANGUAGES)) {
        return $_COOKIE['user_lang'];
    }
    
    // 3. Browser language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browserLang, SUPPORTED_LANGUAGES)) {
            return $browserLang;
        }
    }
    
    // 4. Default
    return DEFAULT_LANGUAGE;
}

/**
 * Load language file
 */
function loadLanguage($lang) {
    $file = __DIR__ . "/{$lang}.json";
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        if ($data !== null) {
            return $data;
        }
    }
    
    // Fallback to default language
    if ($lang !== DEFAULT_LANGUAGE) {
        $defaultFile = __DIR__ . "/" . DEFAULT_LANGUAGE . ".json";
        if (file_exists($defaultFile)) {
            $content = file_get_contents($defaultFile);
            return json_decode($content, true);
        }
    }
    
    return [];
}

// Initialize global language variables
$GLOBALS['CURRENT_LANG'] = getCurrentLanguage();
$GLOBALS['LANG_DATA'] = loadLanguage($GLOBALS['CURRENT_LANG']);

/**
 * Translation helper function
 * 
 * @param string $key Dot notation key (e.g., 'nav.home')
 * @param array $params Optional parameters for replacement
 * @return string Translated text or key if not found
 */
function __($key, $params = []) {
    $keys = explode('.', $key);
    $value = $GLOBALS['LANG_DATA'];
    
    // Navigate through nested array
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            // Fallback: return key itself
            return $key;
        }
    }
    
    // Ensure we got a string
    if (!is_string($value)) {
        return $key;
    }
    
    // Replace parameters {param}
    foreach ($params as $param => $val) {
        $value = str_replace('{' . $param . '}', $val, $value);
    }
    
    return $value;
}

/**
 * Alias for translation helper
 */
function t($key, $params = []) {
    return __($key, $params);
}

/**
 * Get all available languages
 */
function getAvailableLanguages() {
    return [
        'tr' => [
            'name' => 'Türkçe', 
            'flag' => '<svg width="20" height="15" viewBox="0 0 20 15" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="15" fill="#E30A17"/><circle cx="10" cy="7.5" r="3.5" fill="#fff"/><circle cx="10" cy="7.5" r="2.5" fill="#E30A17"/><polygon points="10,5.5 10.8,6.8 9.2,6.8" fill="#fff"/></svg>'
        ],
        'en' => [
            'name' => 'English', 
            'flag' => '<svg width="20" height="15" viewBox="0 0 20 15" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="15" fill="#012169"/><path d="M0 0l20 15M20 0L0 15" stroke="#fff" stroke-width="2"/><path d="M0 0l20 15M20 0L0 15" stroke="#C8102E" stroke-width="1.2"/><rect x="0" y="6" width="20" height="3" fill="#fff"/><rect x="0" y="6" width="20" height="1.5" fill="#C8102E"/><rect x="8" y="0" width="4" height="15" fill="#fff"/><rect x="8" y="0" width="2" height="15" fill="#C8102E"/></svg>'
        ]
    ];
}

/**
 * Get language switcher HTML
 */
function getLanguageSwitcher() {
    $current = $GLOBALS['CURRENT_LANG'];
    $languages = getAvailableLanguages();
    
    $html = '<div class="language-switcher">';
    foreach ($languages as $code => $info) {
        $active = ($code === $current) ? 'active' : '';
        $html .= '<a href="?lang=' . $code . '" class="lang-btn ' . $active . '" title="' . $info['name'] . '">';
        $html .= $info['flag'] . ' ' . strtoupper($code);
        $html .= '</a>';
    }
    $html .= '</div>';
    
    return $html;
}

