//based on TinyMce color map plugin - modified by wooya
function showMapBColor(color, mapId) {
    $("#selectedMapBColor" + mapId).css('background-color', color);
    $("#selectedMapBColorBox" + mapId).val(color);
}

//based on TinyMce color map plugin - modified by wooya
function BColorMap(elname, formname) {
    var html = "";
    var colors = new Array(
        "#000000", "#000033", "#000066", "#000099", "#0000cc", "#0000ff", "#330000", "#330033",
        "#330066", "#330099", "#3300cc", "#3300ff", "#660000", "#660033", "#660066", "#660099",
        "#6600cc", "#6600ff", "#990000", "#990033", "#990066", "#990099", "#9900cc", "#9900ff",
        "#cc0000", "#cc0033", "#cc0066", "#cc0099", "#cc00cc", "#cc00ff", "#ff0000", "#ff0033",
        "#ff0066", "#ff0099", "#ff00cc", "#ff00ff", "#003300", "#003333", "#003366", "#003399",
        "#0033cc", "#0033ff", "#333300", "#333333", "#333366", "#333399", "#3333cc", "#3333ff",
        "#663300", "#663333", "#663366", "#663399", "#6633cc", "#6633ff", "#993300", "#993333",
        "#993366", "#993399", "#9933cc", "#9933ff", "#cc3300", "#cc3333", "#cc3366", "#cc3399",
        "#cc33cc", "#cc33ff", "#ff3300", "#ff3333", "#ff3366", "#ff3399", "#ff33cc", "#ff33ff",
        "#006600", "#006633", "#006666", "#006699", "#0066cc", "#0066ff", "#336600", "#336633",
        "#336666", "#336699", "#3366cc", "#3366ff", "#666600", "#666633", "#666666", "#666699",
        "#6666cc", "#6666ff", "#996600", "#996633", "#996666", "#996699", "#9966cc", "#9966ff",
        "#cc6600", "#cc6633", "#cc6666", "#cc6699", "#cc66cc", "#cc66ff", "#ff6600", "#ff6633",
        "#ff6666", "#ff6699", "#ff66cc", "#ff66ff", "#009900", "#009933", "#009966", "#009999",
        "#0099cc", "#0099ff", "#339900", "#339933", "#339966", "#339999", "#3399cc", "#3399ff",
        "#669900", "#669933", "#669966", "#669999", "#6699cc", "#6699ff", "#999900", "#999933",
        "#999966", "#999999", "#9999cc", "#9999ff", "#cc9900", "#cc9933", "#cc9966", "#cc9999",
        "#cc99cc", "#cc99ff", "#ff9900", "#ff9933", "#ff9966", "#ff9999", "#ff99cc", "#ff99ff",
        "#00cc00", "#00cc33", "#00cc66", "#00cc99", "#00cccc", "#00ccff", "#33cc00", "#33cc33",
        "#33cc66", "#33cc99", "#33cccc", "#33ccff", "#66cc00", "#66cc33", "#66cc66", "#66cc99",
        "#66cccc", "#66ccff", "#99cc00", "#99cc33", "#99cc66", "#99cc99", "#99cccc", "#99ccff",
        "#cccc00", "#cccc33", "#cccc66", "#cccc99", "#cccccc", "#ccccff", "#ffcc00", "#ffcc33",
        "#ffcc66", "#ffcc99", "#ffcccc", "#ffccff", "#00ff00", "#00ff33", "#00ff66", "#00ff99",
        "#00ffcc", "#00ffff", "#33ff00", "#33ff33", "#33ff66", "#33ff99", "#33ffcc", "#33ffff",
        "#66ff00", "#66ff33", "#66ff66", "#66ff99", "#66ffcc", "#66ffff", "#99ff00", "#99ff33",
        "#99ff66", "#99ff99", "#99ffcc", "#99ffff", "#ccff00", "#ccff33", "#ccff66", "#ccff99",
        "#ccffcc", "#ccffff", "#ffff00", "#ffff33", "#ffff66", "#ffff99", "#ffffcc", "#ffffff"
    );

    html += '<table border="0" cellspacing="1" cellpadding="0" class="tbl"><tr>';
    for (var i = 0; i < colors.length; i++) {
        if (formname == undefined) {
            html += "<td style='width:10px;height:10px;cursor:crosshair;background-color:" + colors[i] + "' onmousedown=\"addText('" + elname + "', '[bcolor=" + colors[i] + "]', '[/bcolor]');return false;\" onfocus=\"showMapBColor('" + colors[i] + "', '" + elname + "');\" onmouseover=\"showMapBColor('" + colors[i] + "', '" + elname + "');\">"
        } else {
            html += "<td style='width:10px;height:10px;cursor:crosshair;background-color:" + colors[i] + "' onmousedown=\"addText('" + elname + "', '[bcolor=" + colors[i] + "]', '[/bcolor]', '" + formname + "');return false;\" onfocus=\"showMapBColor('" + colors[i] + "', '" + elname + "');\" onmouseover=\"showMapBColor('" + colors[i] + "', '" + elname + "');\">"
        }
        html += '</td>';
        if ((i + 1) % 18 == 0)    html += '</tr><tr>';
    }
    html += '<tr><td colspan="18">'
        + '<table width="100%" border="0" cellspacing="0" cellpadding="0">'
        + '<tr><td id="selectedMapBColor' + elname + '" width="50%" height="16">'
        + '</td><td width="50%">'
        + '<input id="selectedMapBColorBox' + elname + '" name="selectedMapBColorBox' + elname + '" type="text" size="7" maxlength="7" style="text-align:center;font-weight:bold;width:100%" class="textbox" value="" />'
        + '</td></tr>'
        + '</table>'
        + '</td></tr>'
        + '</table>';

    $('#bbcode_bcolor_map_' + elname).append(html);
}
