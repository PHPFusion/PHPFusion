
let forum_summary = {

    answer_panel : function( nav_id , container_id, counter_id, uid) {
        $('#'+nav_id + ' li > a').bind('click', function(e) {
            // the value
            let uri;
            let target = $(this).attr('href');
            //console.log(target);
            uri = INFUSIONS + 'forum/templates/ajax/php/summary/panel.php';
            $.ajax({
                'type': 'GET',
                'data' : { 'type' : target, 'uid': uid },
                'dataType': 'json',
                'url' : uri,
                'beforeSend' : function() {
                    $('#' + container_id).html('<div class="text-center"><img style="width:24px; margin:20px auto 0; display:block;" src=\"' + IMAGES + 'loader/Preloader_7.gif\"/>Loading&hellip;</div>');
                },
                'success' : function(e) {
                    //console.log(e);
                },
                'error' : function() {
                    console.log('File not found');
                },
                'complete' : function(e) {
                    console.log(e);
                    if (e.status == 200 && e.responseJSON.count && e.responseJSON.html)  {
                        $('#' + container_id).html(e.responseJSON.html);
                        console.log(e.responseJSON.count);
                        $('.' + counter_id).html(e.responseJSON.count);
                    }
                }
            });

        });
    },

}

