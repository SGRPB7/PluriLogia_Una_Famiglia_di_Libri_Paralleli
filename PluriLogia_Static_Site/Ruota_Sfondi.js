// ============================================================
// Ruota_Sfondi.js - Rotazione sfondi per pagine articolo
// Versione ridotta: SOLO rotazione, nessun menu topmost
// ============================================================

var sfondoLayer = null;
var sfondoLayerNext = null;
var idxSfondo = 0;
var intervalloSfondi = null;

function cambiaSfondo() {
    if (!window.sfondi || window.sfondi.length === 0) return;

    var nuovoIdx = (idxSfondo + 1) % window.sfondi.length;
    var nuovaImg = "/Sfondi/" + window.sfondi[nuovoIdx];

    // Precarica l'immagine
    var img = new Image();
    img.onload = function () {
        sfondoLayerNext.style.backgroundImage = "url('" + nuovaImg + "')";
        sfondoLayerNext.style.opacity = "1";

        setTimeout(function () {
            sfondoLayer.style.backgroundImage = "url('" + nuovaImg + "')";
            sfondoLayerNext.style.opacity = "0";
            idxSfondo = nuovoIdx;
        }, 1500);
    };
    img.src = nuovaImg;
}

function avviaRotazioneSfondi() {
    if (!window.sfondi || window.sfondi.length === 0) return;
    if (window.sfondi.length === 1) {
        sfondoLayer.style.backgroundImage = "url('/Sfondi/" + window.sfondi[0] + "')";
        return;
    }

    // Imposta il primo sfondo
    sfondoLayer.style.backgroundImage = "url('/Sfondi/" + window.sfondi[0] + "')";
    idxSfondo = 0;

    // Avvia la rotazione ogni 30 secondi
    intervalloSfondi = setInterval(cambiaSfondo, 30000);
}

window.addEventListener("load", function () {
    sfondoLayer = document.getElementById("sfondo-layer");
    sfondoLayerNext = document.getElementById("sfondo-layer-next");

    if (sfondoLayer && sfondoLayerNext) {
        avviaRotazioneSfondi();
    }
});