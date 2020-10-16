var section_value = $('#section_value').val();
var section_type = $('#section_type').val();
var propagate = false;
$('#search_filter_frm').submit(function (e) {
    e.preventDefault();
    var data = {
        'section_value': section_value, // type - all, attach, poll, discussions
        'section_type': section_type, // this will be the forum id.
    }
    //console.log('filter activated');
    //console.log(data);
    var sendData = $(this).serialize() + '&' + $.param(data);
    console.log(sendData);
    $.ajax({
        'url': INFUSIONS + 'forum/templates/assets/php/thread-search.php',
        'dataType': 'html',
        'data': sendData,
        'method': 'get',
        'beforeSend': function (e) {
            $('#thread_results').html('<tr><td colspan=\"7\" class=\"text-center p-t-20 p-b-20\"><img class=\"img-responsive\" style=\"max-width:50px;\" src=\"./../../images/loader.gif\"/></td>')
        },
        'success': function (e) {
            setTimeout(function (f) {
                $('#thread_results').html(e);
            }, 300);
        },
        'error': function () {
            //console.log('error fetching data');
        }
    });
});

sectionObserver = function () {
    $('.tfilters').bind('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        section_value = $(this).data('section');
        section_type = $(this).data('type');
        $('#section_type').val(section_value);
        $('#section_value').val(section_type);
        $('.tfilters').removeClass('text-active');
        $(this).addClass('text-active');
        $('#search_filter_frm').submit();
    });
}
sectionObserver();


$('#filter_search').on('input propertychange paste', function (e) {
    var span_state = $(this).siblings('.form-control-feedback').length;
    var val = $(this).val();
    if (!span_state && val) {
        $('<span data-remove=\"#filter_search\" class=\"fa fa-times-circle form-control-feedback\" aria-hidden=\"true\"></span>').insertAfter($(this));
    }
});
$('body').on('click', '.form-control-feedback', function (e) {
    console.log(e);
    var value = $(this).data('remove');
    var hide_dom = $(this).data('hide');
    var show_dom = $(this).data('show');
    var reset_dom = $(this).data('reset');
    var reset_select = $(this).data('reset-select');
    if (value) {
        $(value).val('');
        $(this).remove();
        if (hide_dom) {
            $(hide_dom).hide();
        }
        if (show_dom) {
            $(show_dom).show();
        }
        if (reset_dom) {
            $(reset_dom).val('');
        }
        if (reset_select) {
            $(reset_select).select2('val', '');
        }
    }
});