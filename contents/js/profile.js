/*-------------------------------------------------------+
 | PHP-Fusion Content Management System
 | Copyright (C) PHP-Fusion Inc
 | https://www.phpfusion.com/
 +--------------------------------------------------------+
 | Filename: profile.js
 | Author: Core Development Team (coredevs@phpfusion.com)
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/

// IIFE modelling http://benalman.com/news/2010/11/immediately-invoked-function-expression/
(function () {

    // Define our constructor
    this.FusionRelations = function () {

        // Create global element references
        this.button = null;
        this.requestUrl = INCLUDES + 'requests/relations.ajax.php';

        // Define option defaults
        let options = {
            buttonId: '',
            requestType: '',
            //ajaxFormat: 'html',
            ajaxFormat: 'json',
            friendId: 0,
            formId: '',
            fusionToken: '',
        }

        // Create options by extending defaults with the passed in arguments
        if (arguments[0] && typeof arguments[0] === "object") {
            this.options = extendDefaults(options, arguments[0]);
        }
    }

    // I will need to click a button, and tell what it does without revealing the url
    FusionRelations.prototype.init = function () {
        doAjaxRequest.call(this);
    }

    // Private Methods
    // Ajax script, and execution script
    function doAjaxRequest() {
        let _ = this;
        $.ajax({
            method: 'post',
            url: _.requestUrl,
            dataType: _.options.ajaxFormat,
            data: {
                friend_id: _.options.friendId,
                form_id: _.options.formId,
                fusion_token: _.options.fusionToken,
                request_type: _.options.requestType
            },
            success: function (e) {
                // console.log(e);
                /** @namespace e.hide           buttons to be hidden **/
                /** @namespace e.disable_text    text to be disabled **/
                /** @namespace e.disable        buttons to be disabled **/
                /** @namespace e.enable_text        buttons to be disabled **/
                /** @namespace e.enable        buttons to be disabled **/
                /** @namespace e.error          has general error **/
                /** @namespace e.show_after_id          id to attach after **/
                /** @namespace e.show_after_hide          show button after hiding the e.hide button **/
                /** @namespace e.show          show button **/
                if (!e.error) {
                    if (e.disable && e.disable_text) {
                        disableButton(e.disable, e.disable_text);
                    }
                    if (e.enable && e.enable_text) {
                        enableButton(e.enable, e.enable_text);
                    }
                    if (e.hide) {
                        if (e.show_after_hide && e.show_after_id) {
                            addButton(e.show_after_id, e.show_after_hide);
                        }
                        hideButton(e.hide);
                    }
                    if (e.show) {
                        showButton(e.show);
                    }
                }
            }
        });
    }

    function enableButton(button_id, text_replace) {
        console.log('button enabled');
        $('#' + button_id).removeClass('btn-primary').removeClass('disabled').addClass('btn-default').text(text_replace).prop('disabled', false);
    }

    function disableButton(button_id, text_replace) {
        $('#' + button_id).removeClass('btn-default').addClass('btn-primary').addClass('disabled').prop('disabled', true).text(text_replace);
    }

    function hideButton(button_id) {
        $('#' + button_id).hide();
    }

    function showButton(button_id) {
        $('#' + button_id).show();
    }

    function addButton(hide_button_id, show) {
        $('#' + hide_button_id).after(show);
    }

    function doNotice() {
        // This one need a 3rd party plugin, do Fusion have an official plugin? Let's wait until version "C" for Jquery edition of PHPFusion
    }

    // Bind button
    // function initEvents() {
    //     if (this.button) {
    //         this.button.addEventListener('click', this.init.bind(this));
    //     }
    // }

    // Utility method to extend defaults with user options
    function extendDefaults(source, properties) {
        let property;
        for (property in properties) {
            if (properties.hasOwnProperty(property)) {
                source[property] = properties[property];
            }
        }
        return source;
    }
}());
