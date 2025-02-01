(function ($) {
    $.fn.offCanvas = function () {
        // Use event delegation to ensure it works even for dynamically added elements
        $('body').on('click', '[data-pf-toggle]', function () {
            const uniqueId = $(this).data('pf-toggle');  // Get the unique id from data-pf-toggle
            const offcanvas = $('#' + uniqueId);         // Select the corresponding offcanvas by id
            const overlay = $('#overlay-' + uniqueId);   // Select the overlay by id
            const closeBtn = offcanvas.find('.close-btn'); // Close button inside offcanvas

            // Open the offcanvas sidebar
            offcanvas.addClass('opening'); // Add opening class to start the transition
            overlay.addClass('active'); // Show overlay

            // After opening transition completes, add the 'active' class to keep it fully open
            offcanvas.on('transitionend', function () {
                if (offcanvas.hasClass('opening')) {
                    offcanvas.removeClass('opening').addClass('active');
                }
            });

            // Close the offcanvas sidebar when close button is clicked
            closeBtn.on('click', function () {
                offcanvas.removeClass('active').addClass('closing'); // Add closing class
                overlay.removeClass('active'); // Hide overlay

                // After closing transition completes, remove 'closing' class and ensure it's hidden
                offcanvas.on('transitionend', function () {
                    if (offcanvas.hasClass('closing')) {
                        offcanvas.removeClass('closing'); // Fully close it
                    }
                });
            });

            // Close the offcanvas sidebar when overlay is clicked
            overlay.on('click', function () {
                offcanvas.removeClass('active').addClass('closing'); // Add closing class
                overlay.removeClass('active'); // Hide overlay

                // After closing transition completes, remove 'closing' class and ensure it's hidden
                offcanvas.on('transitionend', function () {
                    if (offcanvas.hasClass('closing')) {
                        offcanvas.removeClass('closing'); // Fully close it
                    }
                });
            });

            return false;  // Prevent default behavior of the link (anchor tag)
        });

        return this;
    };

    // Initialize the offcanvas plugin on buttons with data-pf-toggle
    $('[data-pf-toggle]').offCanvas();
})(jQuery);
