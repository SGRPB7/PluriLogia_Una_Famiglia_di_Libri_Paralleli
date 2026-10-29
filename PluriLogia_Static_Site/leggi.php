<?php
// ============================================================
// leggi.php - Serve un articolo dalla cartella documenti_free
// Riceve il parametro ?file=nome_file (senza estensione)
// Applica le stesse iniezioni di index.php (layer sfondi, topmost, wrap)
// ============================================================

// 1. VALIDAZIONE input
$nome_file = isset($_GET['file']) ? $_GET['file'] : '';

// Sicurezza: solo caratteri alfanumerici, underscore, trattini, punti
if (strpos($nome_file, '/') !== false || strpos($nome_file, '\\') !== false || strpos($nome_file, '..') !== false) {
    http_response_code(400);
    echo 'Nome file non valido';
    exit;
}

// 2. COSTRUZIONE percorso file HTML
$percorso_html = __DIR__ . '/documenti_free/' . $nome_file . '.html';

// 3. VERIFICA esistenza file
if (!file_exists($percorso_html)) {
    http_response_code(404);
    echo 'Articolo non trovato: ' . htmlspecialchars($nome_file);
    exit;
}

// 4. LETTURA HTML articolo
$html_articolo = file_get_contents($percorso_html);
// Inietta il tag base per correggere tutti i percorsi relativi (immagini, css, ecc.)

/*
$html_articolo = preg_replace(
    '/<head([^>]*)>/i',
    '<head$1><base href="/documenti_free/">',
    $html_articolo
);
*/

// 5. SCANSIONE SFONDI (stessa logica di index.php)
$sfondi_dir = __DIR__ . '/grafica_sfondi/';
$sfondi = array();
if (is_dir($sfondi_dir)) {
    $files = scandir($sfondi_dir);
    for ($i = 0; $i < count($files); $i++) {
        if (preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $files[$i])) {
            $sfondi[] = $files[$i];
        }
    }
}

// 6. SCANSIONE ARTICOLI per il menu dinamico (stessa logica di index.php)
$articoli_dir = __DIR__ . '/documenti_free/';
$articoli = array();
if (is_dir($articoli_dir)) {
    $files = scandir($articoli_dir);
    for ($i = 0; $i < count($files); $i++) {
        if (preg_match('/\.html$/i', $files[$i])) {
            $articoli[] = pathinfo($files[$i], PATHINFO_FILENAME);
        }
    }
}

// 7. ESTRAZIONE contenuto del <body>
preg_match('/<body([^>]*)>(.*)<\/body>/is', $html_articolo, $matches);
$attrs_body = isset($matches[1]) ? $matches[1] : '';
$contenuto_originale = isset($matches[2]) ? $matches[2] : '';

// 8. BLOCCO DA INIETTARE SUBITO DOPO <body>
$top_injection = '
<div id="sfondo-layer"></div>
<div id="sfondo-layer-next"></div>

<button id="btn-toggle-topmost" title="Mostra/Nascondi lista articoli">&#9776; Articoli</button>

<div id="layer-topmost">
  <div class="topmost-controlli">
    <button id="btn-az" title="Ordina A-Z">&#128220; A-Z</button>
    <button id="btn-inverti" title="Inverti ordine">&#8693; Inverti</button>
  </div>
  <ul id="lista-articoli"></ul>
</div>
';

// 9. BLOCCO DA INIETTARE SUBITO PRIMA DI </body>

$bottom_injection = '
<script>
window.sfondi = ' . json_encode($sfondi, JSON_UNESCAPED_UNICODE) . ';
window.articoli = ' . json_encode($articoli, JSON_UNESCAPED_UNICODE) . ';

// Corregge i path relativi delle immagini
document.querySelectorAll("img").forEach(function(img) {
    var src = img.getAttribute("src");
    if (src && !src.startsWith("/") && !src.startsWith("http") && !src.startsWith("data:")) {
        img.setAttribute("src", "/documenti_free/" + src);
    }
});
</script>
<script src="/index.js" defer></script>
';


// 10. RICOSTRUZIONE del <body>
$nuovo_body = $top_injection
    . '<div class="contenuto-principale">'
    . $contenuto_originale
    . '</div>'
    . $bottom_injection;

$html_articolo = preg_replace(
    '/<body([^>]*)>.*<\/body>/is',
    '<body' . $attrs_body . '>' . $nuovo_body . '</body>',
    $html_articolo
);

// 11. OUTPUT
echo $html_articolo;