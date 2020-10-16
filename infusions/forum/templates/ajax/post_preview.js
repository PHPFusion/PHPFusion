var message_box = $('#post_message');
var message_box_buttons = $('#post_message-field .bbcode-popup, #post_message-field input.bbcode');
var form = $('#input_form');
var preview_file = $('#preview_src_file');
var preview_box = $('#preview_box');
var timer = null
$(message_box).on('input propertychange paste', function (e) {
    clearTimeout(timer);
    timer = setTimeout(get_preview, 1000)
});

$(message_box_buttons).on('click', function (e) {
    clearTimeout(timer);
    timer = setTimeout(get_preview, 10)
});

function get_preview() {
    var text = message_box.val();
    var data = {
        'text': text,
        'editor': 'bbcode',
        'url': form.prop('action'),
    };
    var sendData = form.serialize() + '&' + $.param(data);
    $.ajax({
        url: preview_file.val(),
        type: 'POST',
        dataType: 'html',
        data: sendData,
        success: function (result) {
            preview_box.html(result);
            return false;
        },
        error: function (result) {
            console.log('Could not load the external file');
        }
    });
}
