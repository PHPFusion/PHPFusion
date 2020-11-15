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

/*! Bootstrap integration for DataTables' Responsive
 * ©2015-2016 SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-se', 'datatables.net-responsive'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-se')(root, $).$;
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
    var _modal = $(
        '<div class="ui modal" role="dialog">' +
        '<div class="header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
        '</div>' +
        '<div class="content"/>' +
        '</div>'
    );

    _display.modal = function (options) {
        return function (row, update, render) {
            if (!$.fn.modal) {
                _original(row, update, render);
            } else {
                if (!update) {
                    if (options && options.header) {
                        _modal.find('div.header')
                            .empty()
                            .append('<h4 class="title">' + options.header(row) + '</h4>');
                    }

                    _modal.find('div.content')
                        .empty()
                        .append(render());

                    _modal
                        .appendTo('body')
                        .modal('show');
                }
            }
        };
    };


    return DataTable.Responsive;
}));
