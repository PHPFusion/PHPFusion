$('#filter_panel').hide();
$('#user_action_bar').hide();
$('#filter_btn').bind('click', function (e) {
    e.preventDefault();
    $(this).toggleClass('active');
    slide_hide('filter_panel');
});

$('#check_all').bind('click', function () {
    if ($(this).is(':checked')) {
        $('input[name^=user_id]:checkbox').prop('checked', true);
        $("#user_table tbody tr").addClass("active");
        $('#user_action_bar').slideDown();
    } else {
        $('input[name^=user_id]:checkbox').prop('checked', false);
        $("#user_table tbody tr").removeClass("active");
        $('#user_action_bar').slideUp();
    }
});

$('#member_frm').on('change', 'input[name^=user_id]:checkbox', function (e) {
    var count = $('#member_frm input[name^=user_id]:checkbox:checked').length;
    if (count) {
        $('#user_action_bar').slideDown();
    } else {
        $('#user_action_bar').slideUp();
    }
});
