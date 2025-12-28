import './styles/app.scss';
import * as bootstrap from 'bootstrap';
import './stimulus_bootstrap';
import {TabulatorFull as Tabulator} from 'tabulator-tables';

window.Tabulator = Tabulator;
window.bootstrap = bootstrap;

import './js/tabulator-locales';
import './js/grid-helpers';

function autoDismissAlerts() {
    const alerts = document.querySelectorAll('#flash-messages .alert');
    alerts.forEach(alert => {
        if (alert.dataset.autoDismiss === 'true') {
            return;
        }

        alert.dataset.autoDismiss = 'true';

        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                new bootstrap.Alert(alert).close();
            }
        }, 5000);
    });
}

document.addEventListener("turbo:frame-load", (event) => {
    const frame = event.target;
    const modal = frame.closest(".modal");
    if (modal) {
        const instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        instance.show();
    }
});

// Full page load
document.addEventListener('turbo:load', () => {
    autoDismissAlerts();
});

// Turbo Stream updates
document.addEventListener('turbo:before-stream-render', () => {
    // Malé zpoždění aby se DOM stihl aktualizovat
    setTimeout(() => {
        autoDismissAlerts();
    }, 50);
});
