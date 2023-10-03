import * as coreui from '@coreui/coreui'
import 'bootstrap';
import jQuery from 'jquery';

window.$ = window.jQuery = jQuery;

console.log('Ths is the version from packages/maxi032');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.jQuery(document).ready(function () {
    console.log("inside document ready");
    $("#saveButton").on("click", function () {
        $('#updateOrCreateForm').submit();
    });
});
