let WIKI = INFUSIONS + "wiki/";

function search_ajax() {
    let url = WIKI + "includes/ajax/search.php";

    $("#wiki_search").bind("keyup", function () {
        $.ajax({
            url: url,
            get: "GET",
            data: $.param({"searchstring": $(this).val()}),
            dataType: "json",
            success: function (e) {
                if ($("#wiki_search").val() === "") {
                    $(".search-dropdown").removeClass("open");
                } else {
                    let result = "";

                    if (!e.status) {
                        $.each(e, function (i, data) {
                            if (data) {
                                result += '<li><a href="' + data.link + '">' + data.title + '</a><small class="p-l-20 p-r-20"><i class="fa fa-hashtag"></i> <a href="' + data.cat_link + '">' + data.cat_title + '</a></small></li>';
                            }
                        });
                    } else {
                        result = '<li class="p-10"><span>' + e.status + '</span></li>';
                    }

                    $("#wiki_search_results").html(result);
                    $(".search-dropdown").addClass("open");
                }
            }
        });
    });
}

search_ajax();

$(function () {
    $(document.body).scrollspy({
        target: "#scrollspy",
        offset: $(".headerBg, #main-menu, #DefaultMenu").height()
    });

    $(window).on("load", function () {
        $(document.body).scrollspy("refresh");
    });
});
