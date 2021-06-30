function update_checker(force = false) {
    let force_update = force === true ? "&force=true" : "";

    $.ajax({
        url: BASEDIR + "administration/includes/?api=update-checker" + force_update,
        method: "get",
        dataType: "json",
        beforeSend: function () {
            $("#forceupdate > i").show();
        },
        success: function (e) {
            $("#updatechecker_result").html(e.result).show();
        },
        complete: function () {
            $("#forceupdate > i").hide();
        }
    });
}

function update_locales() {
    $.ajax({
        url: BASEDIR + "administration/includes/?api=update-core&step=update_langs",
        method: "get",
        dataType: "json",
        beforeSend: function () {
            $("#updatelocales > i").show();
        },
        success: function (e) {
            $("#update-results").append(e.result);
        },
        complete: function () {
            $("#updatelocales > i").hide();
        }
    });
}
