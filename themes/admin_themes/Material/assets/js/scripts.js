$(function () {
    var body = $("body"),
        sidebar = $(".sidebar"),
        top_menu = $(".top-menu"),
        sidebar_header = $(".sidebar .header");

    $('[data-toggle="tooltip"]').tooltip();
    $("li.home-link a").text("").html('<i class="fa fa-home fa-lg"></i>');

    body.on("click", "[data-action]", function (e) {
        switch ($(this).data("action")) {
            case "hide-sidebar":
                e.preventDefault();
                if (body.hasClass("sidebar-toggled")) {
                    body.removeClass("sidebar-toggled");
                    $("#hide-sidebar .btn-toggle").removeClass("on");
                    Cookies.set("sidebar-toggled", 0);
                } else {
                    body.addClass("sidebar-toggled");
                    $("#hide-sidebar .btn-toggle").addClass("on");
                    Cookies.set("sidebar-toggled", 1);
                }
                break;
            case "sidebar-sm":
                e.preventDefault();
                if (body.hasClass("sidebar-sm")) {
                    body.removeClass("sidebar-sm");
                    $("#sidebar-sm .btn-toggle").removeClass("on");
                    Cookies.set("sidebar-sm", 0);
                } else {
                    body.addClass("sidebar-sm");
                    $("#sidebar-sm .btn-toggle").addClass("on");
                    Cookies.set("sidebar-sm", 1);
                }
                break;
            case "fixedsidebar":
                e.preventDefault();
                if (sidebar.hasClass("fixed")) {
                    sidebar.removeClass("fixed");
                    $("#fixedsidebar .btn-toggle").removeClass("on");
                    Cookies.set("fixedsidebar", 1);
                } else {
                    sidebar.addClass("fixed");
                    $("#fixedsidebar .btn-toggle").addClass("on");
                    Cookies.set("fixedsidebar", 0);
                }
                break;
            case "search-box":
                e.preventDefault();
                $(".sidebar-sm .search-box").toggle();
                break;
            case "togglemenu":
                e.preventDefault();
                body.toggleClass("sidebar-toggled");
                break;
            case "fixedmenu":
                e.preventDefault();
                if (top_menu.hasClass("fixed") && sidebar_header.hasClass("fixed")) {
                    top_menu.removeClass("fixed");
                    sidebar_header.removeClass("fixed");
                    $("#fixedmenu .btn-toggle").removeClass("on");
                    Cookies.set("fixedmenu", 1);
                } else {
                    top_menu.addClass("fixed");
                    sidebar_header.addClass("fixed");
                    $("#fixedmenu .btn-toggle").addClass("on");
                    Cookies.set("fixedmenu", 0);
                }
                break;
            case "messages":
                e.preventDefault();
                $(".messages-box").toggleClass("open");
                body.addClass("overlay-active");
                break;
            case "theme-settings":
                e.preventDefault();
                e.stopPropagation();
                $("#theme-settings").toggleClass("open");
                break;
            default:
                break;
        }
    });

    $(document).click(function () {
        $("#theme-settings").removeClass("open");
    });

    if (Cookies.get("sidebar-toggled") !== undefined) {
        if (Cookies.get("sidebar-toggled") == 1) {
            body.addClass("sidebar-toggled");
            $("#hide-sidebar .btn-toggle").addClass("on");
        }
    }

    if (Cookies.get("sidebar-sm") !== undefined) {
        if (Cookies.get("sidebar-sm") == 1) {
            body.addClass("sidebar-sm");
            $("#sidebar-sm .btn-toggle").addClass("on");
        }
    }

    if (Cookies.get("fixedsidebar") !== undefined) {
        if (Cookies.get("fixedsidebar") == 1) {
            sidebar.removeClass("fixed");
            $("#fixedsidebar .btn-toggle").removeClass("on");
        }
    }

    if (Cookies.get("fixedmenu") !== undefined) {
        if (Cookies.get("fixedmenu") == 1) {
            top_menu.removeClass("fixed");
            sidebar_header.removeClass("fixed");
            $("#fixedmenu .btn-toggle").removeClass("on");
        }
    }

    $("#messages-box-close").on("click", function (e) {
        e.preventDefault();
        $(".messages-box").removeClass("open");
        body.removeClass("overlay-active");
    });

    var search_box = $("#search_box");

    search_box.focus(function () {
        $(".input-search-icon").addClass("focus");
    });

    search_box.blur(function () {
        $(".input-search-icon").removeClass("focus");
    });

    if (body.hasClass("sidebar-sm")) {
        $(".admin-vertical-link li.active .adl-link").addClass("collapsed");
        $(".admin-vertical-link li.active .collapse").removeClass("in");
    }

    $(".overlay").bind("click", function () {
        body.removeClass("overlay-active");
        $(".messages-box").removeClass("open");
    });

});

$(document).mouseup(function (e) {
    if ($(".sidebar-sm")[0]) {
        var ald_link = $(".admin-vertical-link .adl-link");
        if (!ald_link.is(e.target) && ald_link.has(e.target).length === 0) {
            $(".admin-vertical-link li .adl-link").addClass("collapsed");
            $(".admin-vertical-link li .collapse").removeClass("in");
        }
    }
});

function search_ajax(url) {
    $("#search_box").bind("keyup", function () {
        $.ajax({
            url: url,
            method: "get",
            data: $.param({"pagestring": $(this).val()}),
            dataType: "json",
            beforeSend: function () {
                $("#ajax-loader").show();
            },
            success: function (e) {
                if ($("#search_box").val() === "") {
                    $("#adl").show();
                    $("#search_result").html(e).hide();
                    $("#search_result li").html(e).hide();
                } else {
                    if ($("body").hasClass("sidebar-sm")) {
                        $("#adl").show();
                    } else {
                        $("#adl").hide();
                    }

                    var result = "";

                    if (!e.status) {
                        $.each(e, function (i, data) {
                            if (data) {
                                result += "<li><a href=\"" + data.link + "\"><img class=\"admin-image\" alt=\"" + data.title + "\" src=\"" + data.icon + "\"/> " + data.title + "</a></li>";
                            }
                        });
                    } else {
                        result = "<li><span id=\"search-status\">" + e.status + "</span></li>";
                    }

                    $("#search_result").html(result).show();
                }
            },
            complete: function () {
                $("#ajax-loader").hide();
            }
        });
    });
}
