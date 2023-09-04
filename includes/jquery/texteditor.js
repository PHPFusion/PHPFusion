/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: texteditor.php
 * | Author:  meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

(function ($) {

    // Character counter at the footer of the editor
    $.fn.charCounter = function () {
        if (this.length) {
            const id = this.attr('id');
            const cDOM = $('#' + id + '-counter'), initStr = this.val().length;
            if (cDOM.length) {
                this.on('input propertychange paste', function () {
                    cDOM.text($(this).val().length);
                });
                cDOM.text(initStr);
            } else {
                console.log(dom + ' does not have character count container');
            }
        } else {
            console.log(dom + ' does not exists.');
        }
    };

    /**
     * Add plain smiley text to the textarea input of the editor
     */
    $.fn.smileys = function () {
        if (this.length) {
            const id = this.attr('id'), formName = this.closest('form').attr('name'), domName = this.attr('name');
            // we have a textarea which has a smiley.
            $('#' + id + '-field').find('[data-action="bbcode_smileys"]').on('click', function () {
                let smileyCode = $(this).data('smiley');
                addText(domName, '', smileyCode, formName)
            });
        } else {
            console.log(dom + ' does not exists.');
        }
    }


}(jQuery));