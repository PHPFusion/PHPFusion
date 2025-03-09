/*-------------------------------------------------------+
 | PHPFusion Content Management System
 | Copyright (C) PHP Fusion Inc
 | https://phpfusion.com/
 +--------------------------------------------------------+
 | Filename: bbcode.js
 | Author: Core Development Team
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/

/**
 * Executes PHPFusion BBcode Editor Action
 */
let bbcode_action = function () {

// Smileys action
    $(document).on("click", "[data-action='bbcode_smileys']", function (e) {
        e.preventDefault();
        let bbcode = $(this).data('smiley'),
            editor = $(this).closest('.panel-txtarea').find('textarea');
        if (editor.length === 1) {
            let curPos = editor[0].selectionStart, curVal = $(editor).val();
            $(editor).val(curVal.slice(0, curPos) + bbcode + curVal.slice(curPos));
        }
    });


}

window.onload = bbcode_action();
