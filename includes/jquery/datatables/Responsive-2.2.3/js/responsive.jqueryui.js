/*******************************************************************************
 * -------------------------------------------------------+
 * | PHP-Fusion Content Management System
 * | Copyright (C) PHP-Fusion Inc
 * | https://www.php-fusion.co.uk/
 * +--------------------------------------------------------+
 * | Filename:
 * | Author:
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 ******************************************************************************/

/*! jQuery UI integration for DataTables' Responsive
 * ©2015 SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-jqui', 'datatables.net-responsive'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-jqui')(root, $).$;
            }

            if (!$.fn.dataTable.Responsive) {
                require('datatables.net-responsive')(root, $);
            }

            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document, undefined) {
    'use strict';
    var DataTable = $.fn.dataTable;


    var _display = DataTable.Responsive.display;
    var _original = _display.modal;

    _display.modal = function (options) {
        return function (row, update, render) {
            if (!$.fn.dialog) {
                _original(row, update, render);
            } else {
                if (!update) {
                    $('<div/>')
                        .append(render())
                        .appendTo('body')
                        .dialog($.extend(true, {
                            title: options && options.header ? options.header(row) : '',
                            width: 500
                        }, options.dialog));
                }
            }
        };
    };


    return DataTable.Responsive;
}));
