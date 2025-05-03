import './styles/app.scss';
import * as bootstrap from 'bootstrap';
import './bootstrap';
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import 'tabulator-tables/dist/css/tabulator_bootstrap5.min.css';
window.Tabulator = Tabulator;
window.bootstrap = bootstrap;