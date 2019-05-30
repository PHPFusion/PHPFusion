let flag = {
    'show_flag' : function show_flag(item) {
    if(!item.id) {return item.text;}
    let icon = IMAGES +'small_flag/flag_'+ item.id.replace(/-/gi,'_').toLowerCase() +'.png';
    return '<img style=\"float:left; margin-right:5px; margin-top:3px;\" src=\"' + icon + '\"/></i>' + item.text;
    }
}
