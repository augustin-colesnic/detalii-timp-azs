<?php
/**
 * Detalii Timp & Verset de memorat - Versiune PHP pentru SEO Dinamic
 * Această pagină preia versetul direct de pe server pentru a permite 
 * previzualizări corecte pe WhatsApp, Facebook și alte rețele sociale.
 */

$apiUrl = 'https://www.azstulcea.ro/timp-tulcea/api/api.php?action=get';
$defaultTitle = "Detalii Timp & Verset de memorat";
$defaultDesc = "Află ora exactă, timpul de răsărit și apus în Tulcea, împreună cu versetul biblic de memorat al zilei din Școala de Sabat.";

// Setăm un timeout scurt pentru a nu bloca încărcarea paginii dacă API-ul este lent
$context = stream_context_create([
  "http" => [
    "method" => "GET",
    "header" => "User-Agent: PHP-SEO-Fetcher/1.0\r\n",
    "timeout" => 2
  ]
]);

$verseText = "";
$referenceText = "";
$data = @file_get_contents($apiUrl, false, $context);

if ($data) {
  $json = json_decode($data, true);
  if (isset($json['mesaj']) && !empty(trim($json['mesaj']))) {
    $verseText = trim($json['mesaj']);
    $referenceText = isset($json['referinta']) ? trim($json['referinta']) : "";
  }
}

$displayMessage = $verseText;
if (!empty($referenceText)) {
  $displayMessage .= " " . $referenceText;
}

if (empty($displayMessage)) {
  $displayMessage = $defaultDesc;
}

$safeVerse = htmlspecialchars($displayMessage, ENT_QUOTES, 'UTF-8');
$shortVerse = (mb_strlen($safeVerse) > 150) ? mb_substr($safeVerse, 0, 147) . "..." : $safeVerse;
$versionStr = isset($json['version']) ? $json['version'] : time();
?>
<!doctype html>
<html lang="ro">

<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-5LDRNQXXG7"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-5LDRNQXXG7');
  </script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $defaultTitle; ?></title>

  <!-- SEO & General Metadata -->
  <meta name="description" content="<?php echo $safeVerse; ?>" />
  <meta name="author" content="AZS Tulcea" />

  <!-- Open Graph / Facebook (Visual Social Sharing) -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://www.azstulcea.ro/timp-tulcea/" />
  <meta property="og:title" content="<?php echo $defaultTitle; ?>" />
  <meta property="og:description" content="&ldquo;<?php echo $shortVerse; ?>&rdquo;" />
  <meta property="og:image" content="https://www.azstulcea.ro/timp-tulcea/api/og-image.php?v=<?php echo urlencode($versionStr); ?>" />
  <meta property="og:image:alt" content="Verset de memorat Scoala de Sabat" />

  <!-- Twitter / X (Rich Preview Cards) -->
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:url" content="https://www.azstulcea.ro/timp-tulcea/" />
  <meta property="twitter:title" content="<?php echo $defaultTitle; ?>" />
  <meta property="twitter:description" content="<?php echo $safeVerse; ?>" />
  <meta property="twitter:image" content="https://www.azstulcea.ro/timp-tulcea/api/og-image.php?v=<?php echo urlencode($versionStr); ?>" />

  <!-- Performance & External Resources -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/suncalc/1.8.0/suncalc.min.js"></script>
  <script src="main.js?v=1.6.0"></script>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="container">
    <main class="center-section">
      <div id="custom-text-wrapper" class="glass-panel">
        <h2 class="card-title">Versetul de memorat</h2>
        <div class="custom-text-display"></div>
        <div class="inline-toggle-wrapper">
          <div class="info-tooltip-container">
            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span class="tooltip-text glass-panel">
              Metoda Inițialelor te ajută să memorezi textul afișând doar
              prima literă a fiecărui cuvânt.
            </span>
          </div>
          <span class="toggle-label">Memorare</span>
          <label class="switch">
            <input type="checkbox" id="first-letter-toggle" />
            <span class="slider round glass-panel"></span>
          </label>
        </div>
      </div>

      <div class="study-link-wrapper">
        <a href="https://mybible.eu/sabbath-school/adults" target="_blank" class="study-button glass-panel">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="study-icon">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
          </svg>
          <span>Vezi studiul complet</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="arrow-icon">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
          </svg>
        </a>
      </div>
    </main>

    <div class="middle-section" id="breathing-logo">
      <div class="center-image-wrapper">
        <img src="assets/logo-sc_sabat_vibranta.png" alt="Sabat Vibranta" class="center-image" />
      </div>
    </div>

    <header class="top-section glass-panel">
      <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      <div class="time-header">
        <div id="clock"></div>
        <button id="edit-button-mini" class="discreet-edit">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-settings-2">
            <path d="M20 7h-9" />
            <path d="M14 17H5" />
            <circle cx="17" cy="17" r="3" />
            <circle cx="7" cy="7" r="3" />
          </svg>
          <span>Setări</span>
        </button>
      </div>
      <div id="day-of-week"></div>
      <div class="details-grid">
        <div class="date-column">
          <div id="full-date"></div>
          <div id="week-info"></div>
        </div>
        <div class="divider"></div>
        <div class="sun-column">
          <div class="sun-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sun-icon">
              <path d="m12 2 2 4 4 2-4 2-2 4-2-4-4-2 4-2Z" />
              <path d="m3.1 10.7 1-.2" />
              <path d="m4.8 5.8 1 1" />
              <path d="m9.2 3.1.2 1" />
              <path d="m14.8 3.1-.2 1" />
              <path d="m19.2 5.8-1 1" />
              <path d="m20.9 10.7-1-.2" />
              <path d="m20.9 16.3-1 .2" />
              <path d="m19.2 21.2-1-1" />
              <path d="m14.8 23.9-.2-1" />
              <path d="m9.2 23.9.2-1" />
              <path d="m4.8 21.2 1-1" />
              <path d="m3.1 16.3 1 .2" />
            </svg>
            <span id="sunrise-time"></span>
          </div>
          <div class="sun-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="moon-icon">
              <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
            <span id="sunset-time"></span>
          </div>
        </div>
      </div>
    </header>
  </div>

  <div id="settings-modal" class="modal-overlay hidden">
    <div class="modal-content glass-panel">
      <div class="modal-header">
        <h3>Editează Mesajul Afișat</h3>
        <button id="close-modal" class="close-icon-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="setting-group">
          <label for="modal-text-input">Verset de memorat</label>
          <textarea id="modal-text-input" placeholder="Scrie versetul aici..." rows="3"></textarea>
        </div>

        <div class="setting-group">
          <label for="modal-ref-input">Referința (ex: 1Tesaloniceni 5:18)</label>
          <input type="text" id="modal-ref-input" placeholder="(Referința)" />
        </div>

        <div class="setting-option">
          <button id="fetch-server-button" class="secondary">
            Preia mesajul de pe server
          </button>
          <p class="hint">
            Nu salvează automat. Poți edita în continuare și apoi apeși
            "Salvează".
          </p>
        </div>

        <div class="setting-option">
          <h4>Aranjament</h4>
          <div class="layout-toggle">
            <input type="radio" id="layout-extended" name="layout" value="extended" />
            <label for="layout-extended">Extins</label>
            <input type="radio" id="layout-compact" name="layout" value="compact" />
            <label for="layout-compact">Compact</label>
          </div>
        </div>

        <details class="setting-option">
          <summary>INFO Detalii Locație</summary>
          <article id="location-details" class="info-box">
            <p>
              Se vor folosi detaliile locației pentru a calcula răsăritul și
              apusul soarelui (dacă este permis).
            </p>
            <p>Implicit se folosește București, România.</p>
          </article>
        </details>
      </div>

      <div class="modal-footer">
        <div class="modal-footer-info">
          <span class="app-version">Versiune: 1.5.1</span>
        </div>
        <button id="save-button" class="primary-btn">Salvează</button>
      </div>
    </div>
  </div>
</body>

</html>