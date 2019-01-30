/**
 * Artemis ACP Jquery for Menu Administration Interface
 * Uses Cookie to remember refresh state
 * @type {*|jQuery|HTMLElement}
 */

var menu_wrap = $('#devlpr .left_menu');
var body_wrap = $('#devlpr #main_content');
var app_wrap = $('#devlpr .app_menu');
var menu_header = $('#devlpr .left_menu > header');
var menu_li = $('#devlpr .menu li');

function menuToggle(locale) {
    if (menu_wrap.hasClass('collapsed')) {
        // close menu
        $('.admin-menu-item .section_name').hide();
        $('.admin-menu-item .caret-icon').hide();
        menu_header.html('<h4 class=\"php-fusion text-white text-center\"></h4>');
        $('.menu-action').html('<i class=\"fa fa-chevron-circle-right hidden-xs\"></i>');
        Cookies.set('acpState', 0);
    } else {
        // open menu
        $('.admin-menu-item .section_name').show();
        $('.admin-menu-item .caret-icon').show();
        menu_header.html('<h2>Artemis</h2>');
        $('.menu-action').html('<i class=\"fa fa-chevron-circle-left hidden-xs\"></i> <span class=\"m-l-10\">' + locale + '</span>');
        Cookies.set('acpState', 1);
    }
}

var CookieState = Cookies.get('acpState');
if (CookieState !== undefined) {
    if (CookieState == 0) {
        menu_wrap.addClass('collapsed');
        body_wrap.addClass('collapsed');
        app_wrap.addClass('collapsed');
        $('.menu-action').html('<i class=\"fa fa-chevron-circle-right hidden-xs\"></i>');
    }
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
