import * as coreui from '@coreui/coreui'
import 'bootstrap';
import jQuery from 'jquery';

window.$ = window.jQuery = jQuery;
window.coreui = coreui;

console.log('Ths is the version from packages/maxi032');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.jQuery(document).ready(function () {
    console.log("inside document ready from bootstrap.js");
});
