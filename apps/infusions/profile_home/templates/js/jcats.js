$('document').ready(function () {

    let subject_val = $('#journal_subject');

    let text_val = $('#journal_text');

    disable_buttons();

    if (check_fields(subject_val) && check_fields(text_val)) {
        enable_buttons();
    }
    subject_val.bind('input propertychange paste', function (e) {
        disable_buttons();
        if (check_fields($(this)) && check_fields(text_val)) {
            enable_buttons();
        }
    });
    text_val.bind('input propertychange paste', function (e) {
        disable_buttons();
        if (check_fields($(this)) && check_fields(subject_val)) {
            enable_buttons();
        }
    });

// Now if any of the button was pressed, then execute save.
    $('#save_draft, #save_journal').bind('click', function (e) {
        e.preventDefault();
        let btnId = this.id;
        let btnData = {
            post_button: btnId
        }
        let frmVal = $(this).closest('form').serialize() + '&' + $.param(btnData);
        let uid = $('#uid').val();
        $.ajax({
            method: 'post',
            dataType: 'json',
            data: frmVal,
            url: INFUSIONS + 'profile_home/templates/php/jform.php',
            beforeSend: function (e) {
                $('#' + btnId).text('Saving Journal..');
            },
            success: function (e) {
                /** @namespace e.journal_id **/
                if (!e.error && e.journal_id) {
                    window.location = BASEDIR + 'profile.php?lookup=' + uid + '&profile_page=journals&journal=' + e.journal_id;
                }
            },
            error: function (e) {
                console.log('Jcats invalid');
            }
        })
    });

})


/**
 * @param fields dom
 */
function check_fields(fields) {
    if (fields.val()) {
        return true;
    }
    return false;
}

function enable_buttons() {
    $('#save_draft, #save_journal').removeClass('disabled').prop('disabled', false);
}

function disable_buttons() {
    $('#save_draft, #save_journal').addClass('disabled').prop('disabled', true);
}
