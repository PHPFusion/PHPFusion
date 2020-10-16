$('input:radio[name=rank_type]').change(function () {
    var val = $('input:radio[name=rank_type]:checked').val(),
        special = $('#select_special'),
        normal = $('#select_normal'),
        posts = $('#rank_posts');
    if (val == 2) {
        special.show();
        normal.hide();
        posts.attr('readonly', 'readonly');
    } else {
        if (val == 1) {
            posts.attr('readonly', 'readonly');
        } else {
            posts.removeAttr('readonly');
        }
        special.hide();
        normal.show();
    }
});