/*
Original Author: @mrjeremyblaze (https://github.com/jeremyblaze/Segmented-Field)
Modified for PHPFusion CMS Version 9 by PHPFusion Developers Team (Core Devs)
*/

(function ($) {
    $.fn.PHPFusionSegmentedInput = function (settingsOverrides) {

        // options

        var settings = $.extend({
            autoSubmit: false,
            fieldClasses: '',
        }, settingsOverrides);

        var sourceField = $(this);

        // plugin

        var cells = $(sourceField).attr('maxlength');
        var name = $(sourceField).attr('name');

        if (!name) {
            var name = 'segmentedInput';
        }

        var parentClass = name + '-parent';

        if (cells) {

            // hide original field

            $(sourceField).blur().hide();

            // build new segmented input

            var segmentedInput = '<div class="' + parentClass + ' display-flex flex-row col-gap-sm">';

            var i = 0;
            while (i < cells) {
                var segmentedInput = segmentedInput + '<input type="decimal" inputmode="decimal" autocomplete="false" aria-autocomplete="none" class="' + settings.fieldClasses + '" maxlength="1" name="' + name + '-' + i + '"/>';
                i++;
            }

            var segmentedInput = segmentedInput + "</div>";

            $(sourceField).after(segmentedInput);

            // interactions

            var parent = $('.' + parentClass);

            // autofocus first cell

            if ($(sourceField).attr('autofocus')) {
                $(parent).children('input:first-child').focus();
            }

            // handle pasting content

            $(parent).children('input').bind('paste', function (e) {
                $(this).attr('maxlength', 'none');
                var cell = $(this);
                setTimeout(function () {
                    var pastedContent = $(cell).val();
                    var pastedContentAsArray = pastedContent.split('');
                    $(parent).find('input').each(function (index) {
                        $(this).val(pastedContent[index]);
                    });
                    $(cell).attr('maxlength', '1');
                    $(cell).blur();
                }, 100);
            });

            // sync values, auto submit, and auto progress through cells

            $(parent).find('input').on('keyup', function () {

                // auto progress through cells

                if ($(this).val()) {
                    var nextI = $(this).index() + 2;
                    var next = $(parent).find('input:nth-child(' + nextI + ')');
                    if (next) {
                        $(next).focus();
                    }
                }

                // get full value

                var fullVal = '';
                var empty = false;
                $(parent).children('input').each(function () {
                    fullVal = fullVal + $(this).val();
                    if (!$(this).val()) {
                        empty = true;
                    }
                });

                $(sourceField).val(fullVal);

                // auto submit

                if (!empty && settings.autoSubmit) {
                    $(sourceField).parents('form').submit();
                }

            });

        } else {
            console.log('jquery.segmentedInput.js asks you to kindly set the maxlength parameter on your field');
        }

    };
})(jQuery);
