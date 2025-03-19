// Fill up the page with jQuery detection.
let calculateBodyHeight = () => {
    var menu = $('#main-menu'),
        footer = $('footer'),
        menuHeight = menu.outerHeight(),
        footerHeight = footer.outerHeight();

    var grossHeight = menuHeight + footerHeight;

    $('.login-wrapper').css('height', `calc(100vh - ${grossHeight}px)`);
};

calculateBodyHeight();
$(window).resize(calculateBodyHeight);
