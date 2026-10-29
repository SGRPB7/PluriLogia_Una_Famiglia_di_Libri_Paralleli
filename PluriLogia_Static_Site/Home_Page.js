// ============================================================
// Home_Page.js - Funzionalità specifiche della HOME PAGE
// Dipende da: Ruota_Sfondi.js (deve essere caricato PRIMA)
// NON contiene logica di rotazione sfondi (gestita da Ruota_Sfondi.js)
// ============================================================

var topmost = null;
var btnToggle = null;
var lista = null;

function onClickToggleTopmost() {
    if (topmost) {
        topmost.classList.toggle("visibile");
    }
}

function onClickAZ() {
    if (!lista) return;
    var items = Array.from(lista.querySelectorAll("li"));
    items.sort(function(a, b) {
        return a.textContent.localeCompare(b.textContent, "it");
    });
    items.forEach(function(item) { lista.appendChild(item); });
}

function onClickInverti() {
    if (!lista) return;
    var items = Array.from(lista.querySelectorAll("li"));
    items.reverse();
    items.forEach(function(item) { lista.appendChild(item); });
}

function forzaNuovaScheda(event) {
    var target = event.target;
    while (target && target.tagName !== "A") {
        target = target.parentElement;
    }
    if (target && target.tagName === "A" && target.closest("#lista-articoli")) {
        event.preventDefault();
        window.open(target.href, "_blank", "noopener,noreferrer");
    }
}

function inizializzaHomePage() {
    topmost = document.getElementById("layer-topmost");
    btnToggle = document.getElementById("btn-toggle-topmost");
    lista = document.getElementById("lista-articoli");

    if (btnToggle && topmost) {
        btnToggle.onclick = onClickToggleTopmost;
    }

    var btnAZ = document.getElementById("btn-az");
    var btnInverti = document.getElementById("btn-inverti");

    if (btnAZ) { btnAZ.onclick = onClickAZ; }
    if (btnInverti) { btnInverti.onclick = onClickInverti; }

    if (lista) {
        lista.addEventListener("click", forzaNuovaScheda);
    }
}

window.addEventListener("load", inizializzaHomePage);