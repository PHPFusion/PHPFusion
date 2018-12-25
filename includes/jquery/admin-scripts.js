"use strict";

function closeDiv() {
    $('#close-message').fadeTo('slow', 0.01, function () {
        $(this).slideUp('slow', function () {
            $(this).hide()
        })
    })
}

window.setTimeout('closeDiv()', 5000);

function run_admin(action, table_action, reset_table) {
    table_action = table_action || '#table_action';
    reset_table = reset_table || '#reset_table';

    $(table_action).val(action);
    $(reset_table).submit();
}
