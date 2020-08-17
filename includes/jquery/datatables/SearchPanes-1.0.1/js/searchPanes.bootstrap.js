/*******************************************************************************
 * -------------------------------------------------------+
 * | PHP-Fusion Content Management System
 * | Copyright (C) PHP-Fusion Inc
 * | https://www.phpfusion.com/
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

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-bs', 'datatables.net-searchPanes'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }
            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-bs')(root, $).$;
            }
            if (!$.fn.dataTable.searchPanes) {
                require('datatables.net-searchPanes')(root, $);
            }
            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document) {
    'use strict';
    var DataTable = $.fn.dataTable;
    $.extend(true, DataTable.SearchPane.classes, {
        buttonGroup: 'btn-group',
        disabledButton: 'disabled',
        dull: 'disabled',
        narrow: 'col narrow',
        narrowSub: 'row',
        pane: {
            container: 'table'
        },
        paneButton: 'btn btn-light',
        pill: 'badge badge-pill badge-light pill',
        search: 'col-sm form-control search',
        searchCont: 'input-group dtsp-searchCont',
        searchLabelCont: 'input-group-btn',
        show: 'col',
        subRow1: 'dtsp-subRow1 text-right',
        subRow2: 'dtsp-subRow2 text-right',
        table: 'table table-condensed',
        topRow: 'dtsp-topRow'
    });
    $.extend(true, DataTable.SearchPanes.classes, {
        clearAll: 'dtsp-clearAll col-1 btn btn-light',
        container: 'dtsp-searchPanes row',
        panes: 'dtsp-panesContainer',
        title: 'dtsp-title col-10',
        titleRow: 'row'
    });
    return DataTable.searchPanes;
}));
