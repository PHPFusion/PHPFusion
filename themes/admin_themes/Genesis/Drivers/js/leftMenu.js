/**
 * Genesis ACP Jquery for Menu Administration Interface
 * Uses Cookie to remember refresh state
 * @type {*|jQuery|HTMLElement}
 */

var menu_wrap = $('#devlpr .left_menu');
var body_wrap = $('#devlpr #main_content');
var app_wrap = $('#devlpr .app_menu');
var menu_header = $('#devlpr .left_menu > header');
var menu_li = $('#devlpr .menu li');

function clearSearch() {
    $('#main_content').removeClass('open');
}

// Tray Loader
$('a[data-load]').bind('click', function (e) {
    var body = $('div#main_content');
    var ca = $(this).data('load');
    var cl = $('#main_content').data('load');
    if (ca != cl) {
        if (!body.hasClass('open')) {
            body.addClass('open');
            $('.app_menu').show();
        }
    } else {
        body.toggleClass('open');
    }
    body.data('load', ca);
    menu_li.removeClass('active');
    $(this).closest('li').addClass('active');
    $('.app_page_list').hide();
    $('#ap-' + ca).show();

    e.preventDefault();
});

$('.main_content_overlay').bind('click', function (e) {
    menu_li.removeClass('active');
    body_wrap.removeClass('open');
});

// Hover on dropdowns
$('li.dropdown').hover(
    function (e) {
        $(this).addClass('open');
    },
    function (e) {
        $(this).removeClass('open');
    }
);
