/*------------------------------------------
 Flipbox written by CrappoMan
 simonpatterson@dsl.pipex.com
 ------------------------------------------*/
function flipBox(b) {
    var a;
    if (document.images["b_" + b].src.indexOf("_on") == -1) {
        a = document.images["b_" + b].src.replace("_off", "_on");
        document.getElementById("box_" + b).style.display = "none";
        if (document.getElementById("box_" + b + "_diff")) {
            document.getElementById("box_" + b + "_diff").style.display = "block"
        }
        document.images["b_" + b].src = a;
        disply = "none";
        now = new Date();
        now.setTime(now.getTime() + 1000 * 60 * 60 * 24 * 365);
        expire = (now.toGMTString());
        document.cookie = "fusion_box_" + b + "=" + escape(disply) + "; expires=" + expire
    } else {
        a = document.images["b_" + b].src.replace("_on", "_off");
        document.getElementById("box_" + b).style.display = ""; //removed 'block'
        if (document.getElementById("box_" + b + "_diff")) {
            document.getElementById("box_" + b + "_diff").style.display = "none"
        }
        document.images["b_" + b].src = a;
        disply = "block";
        now = new Date();
        now.setTime(now.getTime() + 1000 * 60 * 60 * 24 * 365);
        expire = (now.toGMTString());
        document.cookie = "fusion_box_" + b + "=" + escape(disply) + "; expires=" + expire
    }
}
/**
 * Tool to scroll the window to a designated ID
 * @param hash - ID only
 */
function scrollTo(hash) {
    var hash = $('#' + hash);
    if (hash.length) {
        var scrollNav = hash.offset().top;
        $(document.body).animate({'scrollTop': scrollNav - hash.outerHeight(true)}, 600);
    }
}
/**
 * Tool to copy source element's width to target element.
 * @param source - # or .class element to copy from
 * @param target - # or .class element to copy to
 */
function copyWidth(source, target) {
    var width = $(source).width();
    $(target).width(width);
}

function addText(f, i, a, e) {
    if (e == undefined) {
        e = "inputform"
    }
    if (f == undefined) {
        f = "message"
    }
    element = document.forms[e].elements[f];
    element.focus();
    if (document.selection) {
        var c = document.selection.createRange();
        var h = c.text.length;
        c.text = i + c.text + a;
        return false
    } else {
        if (element.setSelectionRange) {
            var b = element.selectionStart,
                g = element.selectionEnd;
            var d = element.scrollTop;
            element.value = element.value.substring(0, b) + i + element.value.substring(b, g) + a + element.value.substring(g);
            element.setSelectionRange(b + i.length, g + i.length);
            element.scrollTop = d;
            element.focus()
        } else {
            var d = element.scrollTop;
            element.value += i + a;
            element.scrollTop = d;
            element.focus()
        }
    }
}

function insertText(f, h, e) {
    if (e == undefined) {
        e = "inputform"
    }
    if (document.forms[e].elements[f].createTextRange) {
        document.forms[e].elements[f].focus();
        document.selection.createRange().duplicate().text = h
    } else {
        if ((typeof document.forms[e].elements[f].selectionStart) != "undefined") {
            var a = document.forms[e].elements[f];
            var g = a.selectionEnd;
            var d = a.value.length;
            var c = a.value.substring(0, g);
            var i = a.value.substring(g, d);
            var b = a.scrollTop;
            a.value = c + h + i;
            a.selectionStart = c.length + h.length;
            a.selectionEnd = c.length + h.length;
            a.scrollTop = b;
            a.focus()
        } else {
            document.forms[e].elements[f].value += h;
            document.forms[e].elements[f].focus()
        }
    }
}

function show_hide(a) {
    document.getElementById(a).style.display = document.getElementById(a).style.display == "none" ? "block" : "none"
}

function getStyle(c, b) {
    if (typeof c == "string") {
        var a = document.getElementById(c)
    } else {
        var a = c
    }
    if (a.currentStyle) {
        var d = a.currentStyle[b]
    } else {
        if (window.getComputedStyle) {
            var d = document.defaultView.getComputedStyle(a, null).getPropertyValue(b)
        }
    }
    return d
};
/***********************************************
 * Drop Down/ Overlapping Content- � Dynamic Drive (www.dynamicdrive.com)
 * This notice must stay intact for legal use.
 * Visit http://www.dynamicdrive.com/ for full source code
 ***********************************************/
function getposOffset(a, d) {
    var c = (d == "left") ? a.offsetLeft : a.offsetTop;
    var b = a.offsetParent;
    while (b != null) {
        if (getStyle(b, "position") != "relative") {
            c = (d == "left") ? c + b.offsetLeft : c + b.offsetTop
        }
        b = b.offsetParent
    }
    return c
}

function overlay(e, d, a) {
    if (document.getElementById) {
        var c = document.getElementById(d);
        c.style.display = (c.style.display != "block") ? "block" : "none";
        var b = getposOffset(e, "left") + ((typeof a != "undefined" && a.indexOf("right") != -1) ? -(c.offsetWidth - e.offsetWidth) : 0);
        var f = getposOffset(e, "top") + ((typeof a != "undefined" && a.indexOf("bottom") != -1) ? e.offsetHeight : 0);
        c.style.left = b + "px";
        c.style.top = f + "px";
        return false
    } else {
        return true
    }
}

function overlayclose(a) {
    document.getElementById(a).style.display = "none"
}
NewWindowPopUp = null;

function OpenWindow(d, c, a, b) {
    if (NewWindowPopUp != null) {
        NewWindowPopUp.close();
        NewWindowPopUp = null
    }
    if (b == false) {
        wtop = 0;
        wleft = 0
    } else {
        wtop = (screen.availHeight - a) / 2;
        wleft = (screen.availWidth - c) / 2
    }
    NewWindowPopUp = window.open(d, "", "toolbar=no,menubar=no,location=no,personalbar=no,scrollbars=yes,status=no,directories=no,resizable=yes,height=" + a + ",width=" + c + ",top=" + wtop + ",left=" + wleft + "");
    NewWindowPopUp.focus()
}

function resize_forum_imgs() {
    var f;
    var e;
    if (self.innerWidth) {
        e = self.innerWidth
    } else {
        if (document.documentElement && document.documentElement.clientWidth) {
            e = document.documentElement.clientWidth
        } else {
            if (document.body) {
                e = document.body.clientWidth
            } else {
                e = 1000
            }
        }
    }
    if (e <= 800) {
        f = 200
    } else {
        if (e < 1152) {
            f = 300
        } else {
            if (e >= 1152) {
                f = 400
            }
        }
    }
    for (var c = 0; c < document.images.length; c++) {
        var b = document.images[c];
        if (b.className != "forum-img") {
            continue
        }
        var j = b.height;
        var a = b.width;
        var d = false;
        if (a <= j) {
            if (j > f) {
                b.height = f;
                b.width = a * (f / j);
                d = true
            }
        } else {
            if (a > f) {
                b.width = f;
                b.height = j * (f / a);
                d = true
            }
        }
        var h = b.parentNode;
        var g = h.parentNode;
        if (h.className != "forum-img-wrapper") {
            continue
        }
        if (d) {
            h.style.display = "inline";
            if (g.tagName != "A") {
                h.onclick = new Function("OpenWindow('" + b.src + "', " + (a + 40) + ", " + (j + 40) + ", true)");
                h.onmouseover = "this.style.cursor='pointer'"
            }
        } else {
            h.style.display = "inline"
        }
    }
    return true
}

function onload_events() {
    resize_forum_imgs()
}
window.onload = onload_events;