// we need to push to a form to submit to us.
var action = $('.submit-reports').data('type');
var value = $('.submit-reports').data('val');


reportObserver = function () {
    $('.submit-reports').bind('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        section_value = $(this).data('value');
        section_type = $(this).data('type');


        $('#section_type').val(section_value);
        $('#section_value').val(section_type);
        $('#search_filter_frm').submit();
    });
}
reportObserver();
