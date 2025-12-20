<?php
/**
 * Gestion des langues et traductions
 */

// Définir les langues disponibles
define('AVAILABLE_LANGUAGES', ['fr', 'en']);
define('DEFAULT_LANGUAGE', 'fr');

// Initialiser la langue - session_start() doit être appelé avant d'inclure ce fichier
// Vérifier que la session est active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer la langue actuelle
$current_language = DEFAULT_LANGUAGE;

// 1. Vérifier si l'utilisateur a changé la langue (via URL)
if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGUAGES)) {
    $_SESSION['language'] = $_GET['lang'];
    $current_language = $_GET['lang'];
} 
// 2. Vérifier la session
elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], AVAILABLE_LANGUAGES)) {
    $current_language = $_SESSION['language'];
}
// 3. Vérifier la préférence du navigateur
elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($langs as $lang) {
        $lang = strtolower(substr($lang, 0, 2));
        if (in_array($lang, AVAILABLE_LANGUAGES)) {
            $current_language = $lang;
            break;
        }
    }
}

$_SESSION['language'] = $current_language;

// Charger les traductions
$translations = [];
$translation_file = dirname(__FILE__) . '/translations/' . $current_language . '.php';

if (file_exists($translation_file)) {
    include $translation_file;
} else {
    // Fallback sur le français
    include dirname(__FILE__) . '/translations/fr.php';
}

/**
 * Fonction pour obtenir une traduction
 * @param string $key Clé de traduction (ex: 'home.welcome')
 * @param array $params Paramètres de remplacement optionnels
 * @return string Traduction ou la clé si non trouvée
 */
function t($key, $params = []) {
    global $translations;
    
    // Parcourir les clés imbriquées
    $keys = explode('.', $key);
    $value = $translations;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $key; // Retourner la clé si non trouvée
        }
    }
    
    // Remplacer les paramètres
    if (!empty($params) && is_string($value)) {
        foreach ($params as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, $replacement, $value);
        }
    }
    
    return $value;
}

/**
 * Obtenir la langue actuelle
 * @return string Code de langue (ex: 'fr', 'en')
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? DEFAULT_LANGUAGE;
}

/**
 * Obtenir l'URL pour changer de langue
 * @param string $language Code de langue
 * @return string URL avec paramètre de langue
 */
function getLanguageUrl($language) {
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Supprimer le paramètre lang existant
    $url = preg_replace('/[?&]lang=[^&]*/', '', $current_url);
    
    // Ajouter le nouveau paramètre lang
    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . 'lang=' . $language;
}
?>
