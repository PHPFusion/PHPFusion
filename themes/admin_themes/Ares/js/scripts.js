$(function () {
    $("#aressub li a.dropdown-toggle").click(function (e) {
        $("#aressub .in").collapse("hide");
    });

    $(".navtoggle").click(function (e) {
        e.preventDefault();

        $(".navtoggle").toggleClass("air");

        var aressub_ul = $("#aressub > li > ul");

        if ($(".navtoggle").hasClass("air")) {
            $("#aressub .dropdown-toggle").attr("data-toggle", "dropdown");
            aressub_ul.removeClass("collapse");
            aressub_ul.addClass("dropdown-menu dropdown-menu-right");
        } else {
            $("#aressub .dropdown-toggle").attr("data-toggle", "collapse");
            aressub_ul.removeClass("dropdown-menu dropdown-menu-right");
            aressub_ul.addClass("collapse");

            $("#ares-nav li a").click(function (e) {
                $("#ares-nav .in").collapse("hide");
            });
        }

        $("#ares-brand").toggleClass("air");
        $("#ares-nav").toggleClass("air");
        $("#ares-nav li a").toggleClass("air");
        $("#admin-info").toggleClass("hide");
        $("#ares-descriptor").toggleClass("hide");
        $(".search-box").toggleClass("hide");
        $("#ares-content").toggleClass("air");
    });

    $(".navtogglem").click(function (e) {
        e.preventDefault();
        $("body").toggleClass("sidebar-visible");
    });
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
                    $(".ares-mitem").show();
                    $("#search_result").html(e).hide();
                    $("#search_result li").html(e).hide();
                } else {
                    $(".ares-mitem").hide();

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
