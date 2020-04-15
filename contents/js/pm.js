function checkedCheckbox() {
    var checkList = "";
    $("input[type=checkbox]").each(function () {
        if (this.checked) {
            checkList += $(this).val() + ",";
        }
    });
    return checkList;
}
$("#check_all_pm").bind("click", function () {
    var unread_checkbox = $("#unread_tbl tr").find(":checkbox");
    var read_checkbox = $("#read_tbl tr").find(":checkbox");
    var action = $(this).data("action");
    if (action == "check") {
        unread_checkbox.prop("checked", true);
        read_checkbox.prop("checked", true);
        $("#unread_tbl tr").addClass("warning");
        $("#read_tbl tr").addClass("warning");
        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
        $(this).data("action", "uncheck");
        $("#selectedPM").val(checkedCheckbox());
    } else {
        unread_checkbox.prop("checked", false);
        read_checkbox.prop("checked", false);
        $("#unread_tbl tr").removeClass("warning");
        $("#read_tbl tr").removeClass("warning");
        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
        $(this).data("action", "check");
        $("#selectedPM").val(checkedCheckbox());
    }
});
$("#check_read_pm").bind("click", function () {
    var read_checkbox = $("#read_tbl tr").find(":checkbox");
    var action = $(this).data("action");
    if (action == "check") {
        read_checkbox.prop("checked", true);
        $("#read_tbl tr").addClass("warning");
        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
        $(this).data("action", "uncheck");
        $("#selectedPM").val(checkedCheckbox());
    } else {
        read_checkbox.prop("checked", false);
        $("#read_tbl tr").removeClass("warning");
        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
        $(this).data("action", "check");
        $("#selectedPM").val(checkedCheckbox());
    }
});
$("#check_unread_pm").bind("click", function () {
    var unread_checkbox = $("#unread_tbl tr").find(":checkbox");
    var action = $(this).data("action");
    if (action == "check") {
        unread_checkbox.prop("checked", true);
        $("#unread_tbl tr").addClass("warning");
        $("#chkv").removeClass("fa fa-square-o").addClass("fa fa-minus-square-o");
        $(this).data("action", "uncheck");
        $("#selectedPM").val(checkedCheckbox());
    } else {
        unread_checkbox.prop("checked", false);
        $("#unread_tbl tr").removeClass("warning");
        $("#chkv").removeClass("fa fa-minus-square-o").addClass("fa fa-square-o");
        $(this).data("action", "check");
        $("#selectedPM").val(checkedCheckbox());
    }
});
$("input[type=checkbox]").bind("click", function () {
    var checkList = $("#selectedPM").val();
    if ($(this).is(":checked")) {
        $(this).parents("tr").addClass("warning");
        checkList += $(this).val() + ",";
    } else {
        $(this).parents("tr").removeClass("warning");
        checkList = checkList.replace($(this).val() + ",", "");
    }
    $("#selectedPM").val(checkList);
});