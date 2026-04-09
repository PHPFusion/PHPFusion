/*!
 * English Translations for krajee-markdown-editor
 *
 * This file must be loaded after 'markdown-editor.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 * 
 * @see http://plugins.krajee.com/markdown-editor
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
$.fn.markdownEditorLocales['en-US'] = $.fn.markdownEditorLocales['en-GB'] = $.fn.markdownEditorLocales.en = {
    ajaxParserErrorMsg: 'Error parsing markdown text. Please try again later.',
    ajaxParserProgressMsg: 'Parsing markdown text ...',
    noDataMsg: 'No valid source data found!',
    exportFileName: 'export',
    buttonTitles: {
        undo: 'Undo',
        redo: 'Redo',
        bold: 'Bold',
        italic: 'Italic',
        ins: 'Underline / Inserted Text',
        del: 'Strike Through',
        sup: 'Superscript',
        sub: 'Subscript',
        mark: 'Highlighted Text',
        paragraph: 'Paragraph',
        newline: 'Append line break',
        heading: 'Heading',
        link: 'Hyperlink',
        image: 'Image Link',
        indent: 'Indent Text',
        outdent: 'Outdent Text',
        ul: 'Unordered List',
        ol: 'Ordered List',
        dl: 'Definition List',
        footnote: 'Footnote',
        blockquote: 'Block Quote',
        code: 'Inline Code',
        codeblock: 'Code Block',
        hr: 'Horizontal Line',
        emoji: 'Emojis / Emoticons',
        fullscreen: 'Toggle full screen mode',
        hint: 'Usage Hints',
        modeEditor: 'Editor mode',
        modePreview: 'Preview mode',
        modeSplit: 'Split mode',
        export: 'Export content',
        exportHtml: 'Export as HTML',
        exportText: 'Export as Text'
    },
    buttonLabels: {
        export: 'Export',
        exportHtml: 'HTML',
        exportText: 'Text'
    },
    buttonPrompts: {
        link: {
            header: 'Insert Hyperlink',
            hintInput: 'Enter hyperlink address...',
            hintTitle: 'Enter text for the link...'
        },
        image: {
            header: 'Insert Image Link',
            hintInput: 'Enter image link address...',
            hintTitle: 'Enter alternate text for the image...'
        },
        ol: {
            header: 'Ordered List Starting Number',
            hintInput: 'Integer starting from 1'
        },
        codeblock: {
            header: 'Enter code language',
            hintInput: 'e.g. html, php, js'
        }
    },
    buttonActions: {
        bold: {markup: '**(bold text here)**'},
        italic: {markup: '_(italic text here)_'},
        ins: {markup: '_(inserted text here)_'},
        del: {markup: '_(strikethrough text here)_'},
        mark: {markup: '_(marked text here)_'},
        sup: {markup: '_(superscript text here)_'},
        sub: {markup: '_(subscript text here)_'},
        paragraph: {markup: '\n(paragraph text here)\n'},
        heading1: {markup: '# (heading 1 text here)'},
        heading2: {markup: '## (heading 2 text here)'},
        heading3: {markup: '### (heading 3 text here)'},
        heading4: {markup: '#### (heading 4 text here)'},
        heading5: {markup: '##### (heading 5 text here)'},
        heading6: {markup: '###### (heading 6 text here)'}
    },
    templates: {
        exportHeader: '> - - -\n' +
        '> Markdown Export\n' +
        '> ==============\n' +
        '> *Generated {TODAY} by {CREDITS}*\n' +
        '> - - -\n\n',
        hint: '<ul>\n' +
        '  <li><p>You may follow the {LINK_CM} specification (generated via {LINK_MI} plugin) for writing your markdown text.</p></li>\n' +
        '  <li><p>In order to use the formatting buttons on the toolbar, you generally need to highlight a text ' +
        '  within the editor on which the formatting is to be applied. You can also undo the format action on the ' +
        '  highlighted text by clicking the button again (for most buttons).</p></li>\n' +
        '  <li><p>Keyboard access shortcuts for buttons:</p>' +
        '    {ACCESS_KEYS}' +
        '  </li>\n' +
        '</ul>'
    },
    dialogCancelText: 'Cancel',
    dialogSubmitText: 'Submit',
    previewErrorTitle: 'Preview Error',
    previewModeTitle: 'Preview Mode',
    noPreviewUrlMsg: 'Markdown preview processor unavailable. Please contact the system administrator.',
    previewProgressMsg: 'Generating preview ...',
    noExportUrlMsg: 'Export processor unavailable. Please contact the system administrator.',
    emojiSearchHint: 'Search emojis ...',
    loadingMsg: 'Loading ...'
};