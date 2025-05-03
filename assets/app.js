import './styles/app.scss';
import * as bootstrap from 'bootstrap';
import './bootstrap';
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import 'tabulator-tables/dist/css/tabulator_bootstrap5.min.css';
window.Tabulator = Tabulator;
window.bootstrap = bootstrap;

document.addEventListener("turbo:frame-load", (event) => {
    const frame = event.target;
    const modal = frame.closest(".modal");
    if (modal) {
        const instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        instance.show();
    }
});