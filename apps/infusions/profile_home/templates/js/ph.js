// Fusion Home Panel Script
$(function() {
   phJs.cInit();
});
let phJs = {
    'cInit' : function() {
        $('a[data-toggle="showhide"]').bind('click', function(e) {
            e.preventDefault();
            let closestDOM = $(this).closest('div.profile-details').find('.profile-fields');
            $(this).text('Show More');
            if (closestDOM.hasClass('collapsed')) {
                $(this).text('Show Less');
            }
            closestDOM.toggleClass('collapsed');
        });
    }
}

