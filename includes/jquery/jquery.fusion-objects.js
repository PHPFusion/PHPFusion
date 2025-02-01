(function ($) {
    // jQuery FusionObjects Plugin
    $.fn.fusionObjects = function () {
        // Use event delegation to handle clicks on elements with data-pf-toggle and data-toggle-id
        $('body').on('click', '[data-pf-toggle]', function () {
            const toggleType = $(this).data('pf-toggle');  // The type of the fusion object (e.g., canvas, dropdown, accordion)
            const toggleId = $(this).data('toggle-id');    // The unique ID for the specific element
            const element = $('#' + toggleId);              // Get the corresponding element using the ID
            const overlay = $('#overlay-' + toggleId);      // Get the corresponding overlay for canvas type
            const closeBtn = element.find('.close-btn');    // Close button inside the element

            // Handle different types of fusion objects (canvas, dropdown, accordion, etc.)
            if (toggleType === 'canvas') {
                // Open the offcanvas sidebar
                element.addClass('opening');  // Add opening class to trigger slide-in
                overlay.addClass('active');    // Show overlay

                // Wait for the opening animation to finish before adding 'active'
                setTimeout(function () {
                    element.removeClass('opening').addClass('active');
                }, 300);  // Match this timeout with the transition speed in CSS

                // Close the offcanvas sidebar when close button is clicked
                closeBtn.on('click', function () {
                    closeFusionElement(element, overlay);
                });

                // Close the offcanvas sidebar when overlay is clicked
                overlay.on('click', function () {
                    closeFusionElement(element, overlay);
                });
            }

            // Add other component-specific logic here for dropdowns, accordions, etc.
            // Example for dropdown:
            if (toggleType === 'dropdown') {
                // Toggle dropdown visibility
                element.toggleClass('active');
            }

            return false;  // Prevent default behavior of the link (anchor tag)
        });

        // Function to close the fusion element (offcanvas, dropdown, etc.)
        function closeFusionElement(element, overlay) {
            element.removeClass('active').addClass('closing');  // Add closing class to trigger slide-out
            overlay.removeClass('active');  // Hide overlay

            // Wait for the closing animation to finish before removing 'closing'
            setTimeout(function () {
                element.removeClass('closing');
            }, 300);  // Match this timeout with the transition speed in CSS
        }

        return this;
    };

    // Initialize the fusionObjects plugin on elements with data-pf-toggle
    $('[data-pf-toggle]').fusionObjects();
})(jQuery);
