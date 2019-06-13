let fusion_textarea_tab = function ( dom_id ) {

    $(document).delegate('#'+dom_id, 'keydown', function(e) {

        let keyCode = e.keyCode || e.which;

        if (keyCode == 9) {

            e.preventDefault();

            let start = $(this).get(0).selectionStart;

            let end = $(this).get(0).selectionEnd;

            // set textarea value to: text before caret + tab + text after caret
            $(this).val($(this).val().substring(0, start) + "\t" + $(this).val().substring(end));

            // put caret at right position again
            $(this).get(0).selectionStart = $(this).get(0).selectionEnd = start + 1;
        }
    });


}

