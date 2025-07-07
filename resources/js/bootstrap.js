import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// JQuery
import $ from 'jquery';
window.jQuery = window.$ = $;

// Select2
import select2 from 'select2';
import "/node_modules/select2/dist/css/select2.css";
select2(); // <-- select2 must be called