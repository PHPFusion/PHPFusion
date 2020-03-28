$(document).ready(function(e) {
    $('.admin-actions a[href=\'#screen-options\']').bind('click', function(e) {
        e.preventDefault();
        var target = document.getElementById('screen-options');
        var caret = $(this).find('.fas');
        target = $(target);
        $('.screen-caret').removeClass('fa-caret-up').addClass('fa-caret-down');
        if (target.is(':visible')) {
            target.slideUp(300);
            caret.removeClass('fa-caret-up').addClass('fa-caret-down');
        } else {
            $('.helper-options').hide();
            target.slideDown(300);
            caret.removeClass('fa-caret-down').addClass('fa-caret-up');
        }
    });
    $('.admin-actions a[href=\'#screen-help\']').bind('click', function(e) {
        e.preventDefault();
        var target = document.getElementById('screen-help');
        var caret = $(this).find('.fas');
        target = $(target);
        $('.screen-caret').removeClass('fa-caret-up').addClass('fa-caret-down');
        if (target.is(':visible')) {
            target.slideUp(300);
            caret.removeClass('fa-caret-up').addClass('fa-caret-down');
        } else {
            $('.helper-options').hide();
            target.slideDown(300);
            caret.removeClass('fa-caret-down').addClass('fa-caret-up');
        }
    });
});
