jQuery(function( $ ) {

    $('#contributor-tab li a').bind('click', function(e){
        var i = $(this).data('value');
        var t = $(this).attr('href');
        // only if it is empty
        if( !$.trim( $(t).html() ).length ) {
            $.ajax({
                'url' : INFUSIONS +'forum/templates/assets/php/contributor.php',
                'dataType': 'html',
                'data' : {q:i},
                'method' : 'get',
                'beforeSend': function(e) {
                    $(t).html('Loading...');
                },
                'success': function(e) {
                    setTimeout(function(f){
                        $(t).html(e);
                    },300);
                },
                'error': function() {
                    console.log('error fetching data');
                }
            });
        }
    });


});