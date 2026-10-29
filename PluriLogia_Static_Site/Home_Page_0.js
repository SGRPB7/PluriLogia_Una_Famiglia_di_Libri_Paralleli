var sfondi = window.sfondi || [];
var articoli = window.articoli || [];

var idxCur = 0;
var idxNext = 1;
var ordine = "az";
var invertito = false;

var pathCorrente = "";
var layerA = null;
var layerB = null;
var btnToggle = null;
var topmost = null;
var lista = null;

function onImgLoaded() { cambiaSfondoContinua(pathCorrente); }
function onImgError() { saltaSfondo(); }
function onTimeoutScambio() {
    layerA.style.backgroundImage = "url('" + pathCorrente + "')";
    layerB.style.opacity = "0";
    layerA.style.opacity = "1";
    idxCur = idxNext;
    idxNext = idxNext + 1;
    if (idxNext >= sfondi.length) { idxNext = 0; }
}
function onTimerSfondo() { cambiaSfondoPeriodico(); }

function precaricaImmagine(path) {
    var img;
    pathCorrente = path;
    img = new Image();
    img.onload = onImgLoaded;
    img.onerror = onImgError;
    img.src = path;
}

function cambiaSfondoContinua(path) {
    pathCorrente = path;
    layerB.style.backgroundImage = "url('" + path + "')";
    layerB.style.opacity = "1";
    layerA.style.opacity = "0";
    setTimeout(onTimeoutScambio, 1500);
}

function saltaSfondo() {
    idxNext = idxNext + 1;
    if (idxNext >= sfondi.length) { idxNext = 0; }
}

function avviaRotazione() {
    if (sfondi.length === 0) { return; }
    setInterval(onTimerSfondo, 25000);
}

function cambiaSfondoPeriodico() {
    var path;
    if (sfondi.length === 0) { return; }
    path = "/grafica_sfondi/" + sfondi[idxNext];
    precaricaImmagine(path);
}

function inizializzaSfondi() {
    var path1, path2, imgPreload;
    if (sfondi.length === 0 || layerA === null) { return; }
    idxCur = Math.floor(Math.random() * sfondi.length);
    idxNext = idxCur + 1;
    if (idxNext >= sfondi.length) { idxNext = 0; }
    path1 = "/grafica_sfondi/" + sfondi[idxCur];
    layerA.style.backgroundImage = "url('" + path1 + "')";
    path2 = "/grafica_sfondi/" + sfondi[idxNext];
    imgPreload = new Image();
    imgPreload.src = path2;
    avviaRotazione();
}

function renderListaArticoli() {
    var i, j, temp, arr, html, href;
    if (lista === null) { return; }
    arr = articoli.slice();
    
    /* Bubble sort per A-Z */
    for (i = 0; i < arr.length - 1; i++) {
        for (j = 0; j < arr.length - 1 - i; j++) {
            if (arr[j].localeCompare(arr[j + 1], "it") > 0) {
                temp = arr[j];
                arr[j] = arr[j + 1];
                arr[j + 1] = temp;
            }
        }
    }
    if (invertito) { arr.reverse(); }

    html = "";
    for (i = 0; i < arr.length; i++) {
        href = "/leggi.php?file=" + encodeURIComponent(arr[i]);
        html = html + "<li><a href=\"" + href + "\" target=\"_blank\">" + arr[i] + "</a></li>";
    }
    lista.innerHTML = html;
}

function onClickAZ() { ordine = "az"; renderListaArticoli(); }
function onClickInverti() { invertito = !invertito; renderListaArticoli(); }

function onClickToggleTopmost() {
    if (topmost === null) { return; }
    if (topmost.className.indexOf("visibile") >= 0) {
        topmost.className = topmost.className.replace("visibile", "").replace(/\s+/g, " ").replace(/^\s+|\s+$/g, "");
    } else {
        topmost.className = (topmost.className === "") ? "visibile" : topmost.className + " visibile";
    }
}

function forzaNuovaScheda(event) {
    // Trova il tag <a> più vicino all'elemento cliccato
    var target = event.target;
    while (target && target.tagName !== 'A') {
        target = target.parentElement;
    }
    
    // Se è un link e appartiene alla lista articoli
    if (target && target.tagName === 'A' && target.closest('#lista-articoli')) {
        event.preventDefault(); // Ferma il comportamento default
        window.open(target.href, '_blank', 'noopener,noreferrer'); // Apre in nuova scheda in modo sicuro
    }
}

// Nella funzione inizializzaTopmost(), aggiungi questa riga:
function inizializzaTopmost() {
    var btnAZ = document.getElementById("btn-az");
    var btnInverti = document.getElementById("btn-inverti");
    
    if (btnToggle !== null && topmost !== null) { btnToggle.onclick = onClickToggleTopmost; }
    if (btnAZ !== null) { btnAZ.onclick = onClickAZ; }
    if (btnInverti !== null) { btnInverti.onclick = onClickInverti; }
    
    // AGGIUNGI QUESTA RIGA:
    if (lista !== null) { lista.addEventListener('click', forzaNuovaScheda); }
    
    renderListaArticoli();
}

function init() {
    layerA = document.getElementById("sfondo-layer");
    layerB = document.getElementById("sfondo-layer-next");
    btnToggle = document.getElementById("btn-toggle-topmost");
    topmost = document.getElementById("layer-topmost");
    lista = document.getElementById("lista-articoli");
    inizializzaSfondi();
    inizializzaTopmost();
}

window.onload = init;