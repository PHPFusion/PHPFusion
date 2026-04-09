$(document).on("scroll", event => {

    clearTimeout($.data(this, 'scrollTimer'));

    $.data(this, 'scrollTimer', setTimeout(function () {
        let scrollPos = $(document).scrollTop();
        $('.form-tab-selection').each(function () {
            let currLink = $(this);

            let elementPos = currLink.position().top;

            let $nav_id = $("#" + $(this).attr("id") + "-nav");

            if (elementPos <= scrollPos + 700 && elementPos >= scrollPos - 300) {
                $(this).addClass('active');

                if ($nav_id.length) {
                    $nav_id.addClass("active");
                }

            } else {
                $(this).removeClass('active');
                if ($nav_id.length) {
                    $nav_id.removeClass("active");
                }

            }
        });
    }, 35));

});

/* Adds social network row */
$('#add_social').on('click', event => {
    event.preventDefault();

    $.post(document.location.origin + '/includes/api/?api=item-add-network',
        $('#pcfrm').serialize() + '&method=add', response => {

            $('#social-network-field-wrapper').html(response);

        }).fail(error => {

        alert('Social networks could not be added');
    });

});

/* Removes social network row */
$(document).on('click', 'a[data-action="network_rm"]', function (event) {

    event.preventDefault();

    let row_index = $(this).data('crows');

    $.post(document.location.origin + '/includes/api/?api=item-add-network',

        $('#pcfrm').serialize() + '&method=rm&row=' + row_index, response => {

            $('#social-network-field-wrapper').html(response);

        }).fail(error => {

        alert('Social networks could not be added');
    });
});

/* Adds working hours row */
$(document).on('click', 'button[name="add_hours"]', function (event) {

    event.preventDefault();

    let day = $(this).val();

    $.post(document.location.origin + '/includes/api/?api=item-add-hours',

        $('#pcfrm').serialize() + '&method=add&day=' + day, response => {

            $('div.hourfield[data-day=\"' + day + '\"]').html(response);

        }).fail(error => {

        alert('Social networks could not be added');
    });

});

/* Removes hours row */
$(document).on('click', 'a[data-action="hours_rm"]', function (event) {

    event.preventDefault();

    let row_index = $(this).data('crows');

    let day_index = $(this).data('day');

    $.post(document.location.origin + '/includes/api/?api=item-add-hours',

        $('#pcfrm').serialize() + '&method=rm&row=' + row_index + '&day=' + day_index, response => {

            $('div.hourfield[data-day=\"' + day_index + '\"]').html(response);

        }).fail(error => {

        alert('Hours could not be removed');
    });

    //$('div.social-network[data-row=\"' + id + '\"][data-day=\"' + day + '\"]').remove();
});


/* If current selected is hours, then show the hidden forms */
for (let i = 0; i <= 7; i++) {
    $('input[name="workhours_time[' + i + ']"]').each(function () {
        if (this.checked) {
            if (this.value == 1) {
                $('div.hourfield[data-day=\"' + i + '\"]').show();
            }
        }
    });
}


$(document).on('change', '#tab-content-workhours .tab-pane .radio label > input', function (ev) {
    let val = $(this).val();
    let day = $(this).data('day');
    let $obj_frm = $(this).closest('.tab-pane').find("div.hourfield");
    if (val > 1) {
        $obj_frm.hide();
    } else {
        $obj_frm.show();
    }
});
