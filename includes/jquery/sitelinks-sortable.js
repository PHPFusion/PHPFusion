$().ready(function(){

    $(document).on('click', 'a[data-toggle=\"collapse\"]', function(e) {
        let target = $(this).attr('href');
        $(target).collapse('toggle');
    });

    let ns = $('ol.sortable').nestedSortable({
        forcePlaceholderSize: true,
        handle: 'div',
        helper:	'clone',
        items: 'li',
        opacity: .6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        tolerance: 'pointer',
        toleranceElement: '> div',
        maxLevels: 4,
        isTree: true,
        expandOnHover: 700,
        startCollapsed: false,
        update: function(){
            //console.log('Relocated item');
            // console.log($(this));
            let hiered = $('ol.sortable').nestedSortable('serialize', {startDepthCount: 0});
            // update the sort links
            $('#sortlinks_form #link_sort').val(hiered);
            //console.log('updated');
        }
    });

    /**
     * These are no longer applicable.
     */
    $('.disclose').on('click', function() {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
        $(this).toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
    });
    $('.expandEditor, .itemTitle').click(function(){
        var id = $(this).attr('data-id');
        $('#menuEdit'+id).toggle();
        $(this).toggleClass('ui-icon-triangle-1-n').toggleClass('ui-icon-triangle-1-s');
    });
    $('.deleteMenu').click(function(){
        var id = $(this).attr('data-id');
        $('#menuItem_'+id).remove();
    });

    $('#serialize').click(function(){
        serialized = ns.nestedSortable('serialize');
        $('#serializeOutput').text(serialized+'\n\n');
    });
    $('#toHierarchy').click(function(e){
        hiered = ns.nestedSortable('toHierarchy', {startDepthCount: 0});
        hiered = dump(hiered);
        (typeof($('#toHierarchyOutput')[0].textContent) != 'undefined') ?
            $('#toHierarchyOutput')[0].textContent = hiered : $('#toHierarchyOutput')[0].innerText = hiered;
    });

    $('#toArray').click(function(e){
        arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});
        arraied = dump(arraied);
        (typeof($('#toArrayOutput')[0].textContent) != 'undefined') ?
            $('#toArrayOutput')[0].textContent = arraied : $('#toArrayOutput')[0].innerText = arraied;
    });
});

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') { //Array/Hashes/Objects
        for(var item in arr) {
            var value = arr[item];

            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Strings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}
