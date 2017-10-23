function FullScreen() {
    if (!document.fullscreenElement &&
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement &&
        !document.msFullscreenElement) {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }
}

function get(name) {
    if (typeof (Storage) !== "undefined") {
        return localStorage.getItem(name);
    }
}

function store(name, val) {
    if (typeof (Storage) !== "undefined") {
        localStorage.setItem(name, val);
    }
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $(".dropdown").on("show.bs.dropdown", function () {$(this).find(".dropdown-menu").first().stop(true, true).slideDown(300);});
    $(".dropdown").on("hide.bs.dropdown", function () {$(this).find(".dropdown-menu").first().stop(true, true).slideUp(200);});
    $("li.home-link a").text("").html('<i class="fa fa-home fa-lg"></i>');

    $("body").on("click", "[data-action]", function (e) {
        switch ($(this).data("action")) {
            case "hide-sidebar":
                e.preventDefault();
                if ($("body").hasClass("sidebar-toggled")) {
                    $("body").removeClass("sidebar-toggled");
                    $("#hide-sidebar .btn-toggle").removeClass("on");
                    store("sidebar-toggled", 0);
                } else {
                    $("body").addClass("sidebar-toggled");
                    $("#hide-sidebar .btn-toggle").addClass("on");
                    store("sidebar-toggled", 1);
                }
                break;
            case "sidebar-sm":
                e.preventDefault();
                if ($("body").hasClass("sidebar-sm")) {
                    $("body").removeClass("sidebar-sm");
                    $("#sidebar-sm .btn-toggle").removeClass("on");
                    store("sidebar-sm", 0);
                } else {
                    $("body").addClass("sidebar-sm");
                    $("#sidebar-sm .btn-toggle").addClass("on");
                    store("sidebar-sm", 1);
                }
                break;
            case "fixedsidebar":
                e.preventDefault();
                if ($(".sidebar").hasClass("fixed")) {
                    $(".sidebar").removeClass("fixed");
                    $("#fixedsidebar .btn-toggle").removeClass("on");
                    store("fixedsidebar", 1);
                } else {
                    $(".sidebar").addClass("fixed");
                    $("#fixedsidebar .btn-toggle").addClass("on");
                    store("fixedsidebar", 0);
                }
                break;
            case "search-box":
                e.preventDefault();
                $(".sidebar-sm .search-box").toggle();
                break;
            case "togglemenu":
                e.preventDefault();
                $("body").toggleClass("sidebar-toggled");
                break;
            case "fixedmenu":
                e.preventDefault();
                if ($(".top-menu").hasClass("fixed") && $(".sidebar .header").hasClass("fixed")) {
                    $(".top-menu").removeClass("fixed");
                    $(".sidebar .header").removeClass("fixed");
                    $("#fixedmenu .btn-toggle").removeClass("on");
                    store("fixedmenu", 1);
                } else {
                    $(".top-menu").addClass("fixed");
                    $(".sidebar .header").addClass("fixed");
                    $("#fixedmenu .btn-toggle").addClass("on");
                    store("fixedmenu", 0);
                }
                break;
            case "messages":
                e.preventDefault();
                $(".messages-box").toggleClass("open");
                $("body").addClass("overlay-active");
                break;
            case "theme-settings":
                e.preventDefault();
                e.stopPropagation();
                $("#theme-settings").toggleClass("open");
                break;
            case "fixedfootererrors":
                e.preventDefault();
                if ($(".errors").hasClass("fixed")) {
                    $(".errors").removeClass("fixed");
                    $("#fixedfootererrors .btn-toggle").removeClass("on");
                    store("fixedfootererrors", 1);
                } else {
                    $(".errors").addClass("fixed");
                    $("#fixedfootererrors .btn-toggle").addClass("on");
                    store("fixedfootererrors", 0);
                }
                break;
            case "fullscreen":
                e.preventDefault();
                $("#fullscreen .btn-toggle").toggleClass("on");
                FullScreen();
                break;
            default:
                break;
        }
    });

    $(document).click(function () {
        $("#theme-settings").removeClass("open");
    });

    if (get("sidebar-toggled") !== undefined) {
        if (get("sidebar-toggled") == 1) {
            $("body").addClass("sidebar-toggled");
            $("#hide-sidebar .btn-toggle").addClass("on");
        }
    }

    if (get("sidebar-sm") !== undefined) {
        if (get("sidebar-sm") == 1) {
            $("body").addClass("sidebar-sm");
            $("#sidebar-sm .btn-toggle").addClass("on");
        }
    }

    if (get("fixedsidebar") !== undefined) {
        if (get("fixedsidebar") == 1) {
            $(".sidebar").removeClass("fixed");
            $("#fixedsidebar .btn-toggle").removeClass("on");
        }
    }

    if (get("fixedmenu") !== undefined) {
        if (get("fixedmenu") == 1) {
            $(".top-menu").removeClass("fixed");
            $(".sidebar .header").removeClass("fixed");
            $("#fixedmenu .btn-toggle").removeClass("on");
        }
    }

    if (get("fixedfootererrors") !== undefined) {
        if (get("fixedfootererrors") == 1) {
            $(".errors").removeClass("fixed");
            $("#fixedfootererrors .btn-toggle").removeClass("on");
        }
    }

    $("#messages-box-close").on("click", function (e) {
        e.preventDefault();
        $(".messages-box").removeClass("open");
        $("body").removeClass("overlay-active");
    });

    $("#search_box").focus(function () {
        $(".input-search-icon").addClass("focus");
    });

    $("#search_box").blur(function () {
        $(".input-search-icon").removeClass("focus");
    });

    if ($("body").hasClass("sidebar-sm")) {
        $(".admin-vertical-link li.active .adl-link").addClass("collapsed");
        $(".admin-vertical-link li.active .collapse").removeClass("in");
    }

    $(".overlay").bind("click", function () {
        $("body").removeClass("overlay-active");
        $(".messages-box").removeClass("open");
    });

    $("#backtotop").on("click", function (e) {
        e.preventDefault();
        $("html, body").animate({scrollTop:0}, 800);
    });

    $(window).scroll(function () {
        $(this).scrollTop() > $(this).outerHeight() ? $("#backtotop").addClass("visible") : $("#backtotop").removeClass("visible");
    });
});

$(document).mouseup(function (e) {
    if ($(".sidebar-sm")[0]) {
        if (!$(".admin-vertical-link .adl-link").is(e.target) && $(".admin-vertical-link .adl-link").has(e.target).length === 0) {
            $(".admin-vertical-link li .adl-link").addClass("collapsed");
            $(".admin-vertical-link li .collapse").removeClass("in");
        }
    }
});
