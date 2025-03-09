(function ($) {

    // Define globally accessible functions
    window.openFusionElement = function (selector) {
        const element = $(selector);
        const overlay = $('#overlay-' + element.attr('id'));

        element.addClass('opening'); // Add opening class to trigger slide-in
        overlay.addClass('active');  // Show overlay

        // Wait for the opening animation to finish before adding 'active'
        setTimeout(function () {
            element.removeClass('opening').addClass('active');
            element.trigger('fusionOpened');  // Custom event when opened
        }, 300);  // Match this timeout with the transition speed in CSS

        // Handle close button inside the element
        element.find('.close-btn').on('click', function () {
            closeFusionElement(selector);
        });

        // Handle overlay click to close
        overlay.on('click', function () {
            closeFusionElement(selector);
        });
    };

    window.closeFusionElement = function (selector) {
        const element = $(selector);
        const overlay = $('#overlay-' + element.attr('id'));

        element.removeClass('active').addClass('closing'); // Add closing class to trigger slide-out
        overlay.removeClass('active'); // Hide overlay

        // Wait for the closing animation to finish before removing 'closing'
        setTimeout(function () {
            element.removeClass('closing');
            element.trigger('fusionClosed'); // Custom event when closed
        }, 300);  // Match this timeout with the transition speed in CSS
    };

    window.fusionSwapElement = function (selector) {
        let element = $(selector);
        let linkTitle = element.find('.swap-title');        
        let swapContent = element.find('.swap-box');  // Find the swapbox by its unique id
                
        if (swapContent.is(':visible')) {
            swapContent.hide();
            linkTitle.show();
            element.trigger('fusionHideSwap'); // Custom event when closed
        } else {
            swapContent.show();
            linkTitle.hide();  // Hide the show link          
            element.trigger('fusionShowSwap'); // Custom event when closed
        }
    };

    // jQuery FusionObjects Plugin
    $.fn.fusionObjects = function () {
        $('body').on('click', '[data-pf-toggle]', function () {
            const toggleType = $(this).data('pf-toggle');
            const toggleId = $(this).data('toggle-id');
            const elementSelector = '#' + toggleId;

            if (toggleType === 'canvas') {
                openFusionElement(elementSelector);
            } else if (toggleType === 'dropdown') {
                $(elementSelector).toggleClass('active');
            } else if (toggleType == 'swap') {                
                // Call fusionSwapElement with the element selector
                fusionSwapElement(elementSelector);
            }

            return false; // Prevent default behavior
        });

        return this;
    };

    // Initialize the fusionObjects plugin on elements with data-pf-toggle
    $('[data-pf-toggle]').fusionObjects();

})(jQuery);
