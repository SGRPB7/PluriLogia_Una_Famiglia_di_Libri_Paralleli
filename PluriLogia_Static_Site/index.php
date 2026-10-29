<?php
// 1. SCANSIONE SFONDI
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

// 2. SCANSIONE ARTICOLI (SOLO nomi file, niente JSON)
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

// 3. LETTURA HTML
$html_statico = file_get_contents(__DIR__ . '/Unione_Materiali_Testuali_Weebly_e_WebNode_06.html');

// 4. ESTRAZIONE BODY
preg_match('/<body([^>]*)>(.*)<\/body>/is', $html_statico, $matches);
$attrs_body = isset($matches[1]) ? $matches[1] : '';
$contenuto_originale = isset($matches[2]) ? $matches[2] : '';

// 5. INIEZIONE TOP
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

// 6. INIEZIONE BOTTOM
$bottom_injection = '
<script>
window.sfondi = ' . json_encode($sfondi, JSON_UNESCAPED_UNICODE) . ';
window.articoli = ' . json_encode($articoli, JSON_UNESCAPED_UNICODE) . ';
</script>
<script src="/index.js" defer></script>
';

// 7. RICOSTRUZIONE
$nuovo_body = $top_injection . '<div class="contenuto-principale">' . $contenuto_originale . '</div>' . $bottom_injection;
$html_statico = preg_replace('/<body[^>]*>.*<\/body>/is', '<body' . $attrs_body . '>' . $nuovo_body . '</body>', $html_statico);

echo $html_statico;
?>