import './styles/app.scss';
import * as bootstrap from 'bootstrap';
import './bootstrap';
import {TabulatorFull as Tabulator} from 'tabulator-tables';

window.Tabulator = Tabulator;
window.bootstrap = bootstrap;

import './js/tabulator-locales';

// Funkce pro inicializaci tabulky s lokalizací


document.addEventListener("turbo:frame-load", (event) => {
    const frame = event.target;
    const modal = frame.closest(".modal");
    if (modal) {
        const instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        instance.show();
    }
});