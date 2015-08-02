$(document).ready(function () {
    $('#nav li').hover(
        function () {
            $('ul', this).css('display', 'block');
        },
        function () {
            $('ul', this).css('display', 'none');
        });
});
