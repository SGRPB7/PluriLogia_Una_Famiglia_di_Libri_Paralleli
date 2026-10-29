<?php
// ============================================================
// index.php - Homepage dinamica della PluriLogia
// ============================================================

// 1. SCANSIONE SFONDI (una tantum all'avvio)
$sfondi_dir = __DIR__ . '/grafica_sfondi/';
$sfondi = [];
if (is_dir($sfondi_dir)) {
    foreach (scandir($sfondi_dir) as $f) {
        if (preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $f)) {
            $sfondi[] = $f;
        }
    }
}
$sfondi_json = json_encode($sfondi, JSON_UNESCAPED_UNICODE);

// 2. SCANSIONE ARTICOLI per il menu dinamico
$articoli_dir = __DIR__ . '/documenti_free/';
$articoli = [];
if (is_dir($articoli_dir)) {
    foreach (scandir($articoli_dir) as $f) {
        if (preg_match('/\.html$/i', $f)) {
            $base = pathinfo($f, PATHINFO_FILENAME);
            $json_path = $articoli_dir . $base . '.json';
            $titolo = $base;
            $data = '';
            if (file_exists($json_path)) {
                $j = json_decode(file_get_contents($json_path), true);
                if ($j) {
                    if (!empty($j['headline'])) $titolo = $j['headline'];
                    if (!empty($j['datePublished'])) $data = $j['datePublished'];
                }
            }
            $articoli[] = ['file' => $base, 'titolo' => $titolo, 'data' => $data];
        }
    }
}
$articoli_json = json_encode($articoli, JSON_UNESCAPED_UNICODE);

// 3. LETTURA HTML STATICO HOMEPAGE
$html_statico = file_get_contents(__DIR__ . '/Unione_Materiali_Testuali_Weebly_e_WebNode_06.html');

// 4. LETTURA SCHEMA.ORG HOMEPAGE
$schema_org = file_get_contents(__DIR__ . '/Schema_ORG_HomePage.json');

// 5. BLOCCO DA INIETTARE PRIMA DI </head>
$head_injection = '
<link rel="stylesheet" href="/MyStyleSheet.css">
<script type="application/ld+json">' . $schema_org . '</script>
<style>
  #sfondo-layer, #sfondo-layer-next {
    position: fixed; inset: 0;
    width: 100vw; height: 100vh;
    z-index: -2;
    background-size: 100% 100%;
    background-repeat: no-repeat;
    background-position: center;
    transition: opacity 1.5s ease-in-out;
  }
  #sfondo-layer-next { opacity: 0; z-index: -1; }

  .contenuto-principale {
    position: relative;
    z-index: 1;
    background-color: rgba(255, 255, 255, 0.92);
    min-height: 100vh;
    padding: 1em 2em;
  }

  #menu-toggle-btn {
    position: fixed;
    top: 10px; right: 10px;
    z-index: 1000;
    background: #222; color: #fff;
    border: none; padding: 8px 14px;
    cursor: pointer; font-size: 1.2em;
    border-radius: 4px;
  }
  #menu-dinamico {
    position: fixed;
    top: 50px; right: 10px;
    z-index: 999;
    background: rgba(255,255,255,0.97);
    border: 1px solid #888;
    padding: 1em;
    max-width: 420px;
    max-height: 70vh;
    overflow-y: auto;
    display: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }
  #menu-dinamico.aperto { display: block; }
  #menu-dinamico ul { list-style: none; padding: 0; margin: 0; }
  #menu-dinamico li { padding: 0.4em 0; border-bottom: 1px dotted #ccc; }
  #menu-dinamico a { text-decoration: none; color: #0066cc; }
  #menu-dinamico a:hover { text-decoration: underline; }
  .menu-controlli { margin-bottom: 0.8em; }
  .menu-controlli button {
    background: #eee; border: 1px solid #999;
    padding: 4px 8px; margin-right: 4px;
    cursor: pointer; font-size: 0.9em;
  }
</style>
';

$html_statico = str_replace('</head>', $head_injection . "\n</head>", $html_statico);

// 6. BLOCCO DA INIETTARE DOPO <body>
$body_injection = '
<div id="sfondo-layer"></div>
<div id="sfondo-layer-next"></div>

<button id="menu-toggle-btn" title="Menu Articoli">☰ Articoli</button>
<div id="menu-dinamico">
  <div class="menu-controlli">
    <button id="btn-data" title="Ordina per data">📅 Data</button>
    <button id="btn-az" title="Ordina A-Z">🔤 A-Z</button>
    <button id="btn-inverti" title="Inverti ordine">⇅ Inverti</button>
  </div>
  <ul id="lista-articoli"></ul>
</div>

<script>
  const sfondi = ' . $sfondi_json . ';
  const articoli = ' . $articoli_json . ';

  // ===== ROTAZIONE SFONDI =====
  let idxCur = 0, idxNext = 1;
  const layerA = document.getElementById("sfondo-layer");
  const layerB = document.getElementById("sfondo-layer-next");

  function precarica(path) {
    return new Promise((res, rej) => {
      const img = new Image();
      img.onload = () => res(path);
      img.onerror = () => rej(path);
      img.src = path;
    });
  }

  async function cambiaSfondo() {
    if (sfondi.length === 0) return;
    const p = "/grafica_sfondi/" + sfondi[idxNext];
    try {
      await precarica(p);
      layerB.style.backgroundImage = "url(\'" + p + "\')";
      layerB.style.opacity = "1";
      layerA.style.opacity = "0";
      setTimeout(() => {
        layerA.style.backgroundImage = "url(\'" + p + "\')";
        layerB.style.opacity = "0";
        layerA.style.opacity = "1";
        idxCur = idxNext;
        idxNext = (idxNext + 1) % sfondi.length;
      }, 1500);
    } catch(e) {
      idxNext = (idxNext + 1) % sfondi.length;
    }
  }

  (async function() {
    if (sfondi.length === 0) return;
    idxCur = Math.floor(Math.random() * sfondi.length);
    idxNext = (idxCur + 1) % sfondi.length;
    try {
      const p1 = "/grafica_sfondi/" + sfondi[idxCur];
      await precarica(p1);
      layerA.style.backgroundImage = "url(\'" + p1 + "\')";
      const p2 = "/grafica_sfondi/" + sfondi[idxNext];
      await precarica(p2);
      setInterval(cambiaSfondo, 25000);
    } catch(e) { console.error(e); }
  })();

  // ===== MENU DINAMICO =====
  const btnToggle = document.getElementById("menu-toggle-btn");
  const menu = document.getElementById("menu-dinamico");
  const lista = document.getElementById("lista-articoli");
  let ordine = "data";
  let invertito = false;

  function renderLista() {
    let arr = [...articoli];
    if (ordine === "data") {
      arr.sort((a,b) => a.data.localeCompare(b.data));
    } else {
      arr.sort((a,b) => a.titolo.localeCompare(b.titolo, "it"));
    }
    if (invertito) arr.reverse();
    lista.innerHTML = arr.map(a =>
      `<li><a href="/${encodeURIComponent(a.file)}">${a.titolo}</a>` +
      (a.data ? ` <small>(${a.data})</small>` : "") + `</li>`
    ).join("");
  }

  btnToggle.addEventListener("click", () => menu.classList.toggle("aperto"));
  document.getElementById("btn-data").addEventListener("click", () => { ordine = "data"; renderLista(); });
  document.getElementById("btn-az").addEventListener("click", () => { ordine = "az"; renderLista(); });
  document.getElementById("btn-inverti").addEventListener("click", () => { invertito = !invertito; renderLista(); });

  renderLista();
</script>
';

$html_statico = preg_replace('/<body([^>]*)>/i', '<body$1>' . $body_injection, $html_statico, 1);

// 7. OUTPUT
echo $html_statico;
