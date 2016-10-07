var scrolltotop = {
  setting: {
    startline: 100,
    scrollto: 0,
    scrollduration: 400,
    fadeduration: [500, 100]
  },
  controlHTML: '<button class="btn btn-info btn-scroll"><i class="fa fa-chevron-up"></i></button>',
  controlattrs: {offsetx: 5, offsety: 5},
  anchorkeyword: '#top',
  state: {isvisible: false, shouldvisible: false},

  scrollup: function() {
    if (!this.cssfixedsupport) {
      this.$control.css({opacity: 0});
    }

    var dest = isNaN(this.setting.scrollto) ? this.setting.scrollto : parseInt(this.setting.scrollto);

    if (typeof dest == "string" && jQuery('#' + dest).length == 1) {
      dest = jQuery('#' + dest).offset().top;
    } else {
      dest = 0;
    }
    this.$body.animate({scrollTop: dest}, this.setting.scrollduration);
  },

  keepfixed: function() {
    var $window  = jQuery(window);
    var controlx = $window.scrollLeft() + $window.width() - this.$control.width() - this.controlattrs.offsetx;
    var controly = $window.scrollTop() + $window.height() - this.$control.height() - this.controlattrs.offsety;

    this.$control.css({left:controlx + 'px', top:controly + 'px'});
  },

  togglecontrol: function() {
    var scrolltop = jQuery(window).scrollTop();

    if (!this.cssfixedsupport) {
      this.keepfixed();
    }

    this.state.shouldvisible = (scrolltop >= this.setting.startline) ? true : false;

    if (this.state.shouldvisible && !this.state.isvisible) {
      this.$control.stop().animate({opacity:1}, this.setting.fadeduration[0]);
      this.state.isvisible = true;
    } else if (this.state.shouldvisible == false && this.state.isvisible) {
      this.$control.stop().animate({opacity:0}, this.setting.fadeduration[1]);
      this.state.isvisible = false;
    }
  },

  init: function() {
    jQuery(document).ready(function($) {
      var mainobj = scrolltotop;
      var iebrws = document.all;
      mainobj.cssfixedsupport = !iebrws || iebrws && document.compatMode == "CSS1Compat" && window.XMLHttpRequest;
      mainobj.$body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html, body');
      mainobj.$control = $('<div id="topcontrol">' + mainobj.controlHTML + '</div>')
        .css({position:mainobj.cssfixedsupport ? 'fixed' : 'absolute', bottom:mainobj.controlattrs.offsety, right:mainobj.controlattrs.offsetx, opacity:0, cursor: 'pointer'})
        .click(function(){mainobj.scrollup(); return false})
        .appendTo('body');

      if (document.all && !window.XMLHttpRequest && mainobj.$control.text() != '') {
        mainobj.$control.css({width:mainobj.$control.width()});
      }

      mainobj.togglecontrol();

      $('a[href="' + mainobj.anchorkeyword + '"]').click(function() {
        mainobj.scrollup();
         return false;
      });

      $(window).bind('scroll resize', function(e) {
        mainobj.togglecontrol();
      });
    });
  }
}

scrolltotop.init();

/**
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD (Register as an anonymous module)
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function ($) {
  var pluses = /\+/g;
  function encode(s) {return config.raw ? s : encodeURIComponent(s);}
  function decode(s) {return config.raw ? s : decodeURIComponent(s);}
  function stringifyCookieValue(value) {return encode(config.json ? JSON.stringify(value) : String(value));}
  function parseCookieValue(s) {
    if (s.indexOf('"') === 0) {
      // This is a quoted cookie as according to RFC2068, unescape...
      s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }
    try {
      // Replace server-side written pluses with spaces.
      // If we can't decode the cookie, ignore it, it's unusable.
      // If we can't parse the cookie, ignore it, it's unusable.
      s = decodeURIComponent(s.replace(pluses, ' '));
      return config.json ? JSON.parse(s) : s;
    } catch(e) {}
  }
  function read(s, converter) {
    var value = config.raw ? s : parseCookieValue(s);
    return $.isFunction(converter) ? converter(value) : value;
  }
  var config = $.cookie = function (key, value, options) {
    // Write

    if (arguments.length > 1 && !$.isFunction(value)) {
      options = $.extend({}, config.defaults, options);

      if (typeof options.expires === 'number') {
        var days = options.expires, t = options.expires = new Date();
        t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
      }

      return (document.cookie = [
        encode(key), '=', stringifyCookieValue(value),
        options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
        options.path    ? '; path=' + options.path : '',
        options.domain  ? '; domain=' + options.domain : '',
        options.secure  ? '; secure' : ''
      ].join(''));
    }

    // Read

    var result = key ? undefined : {},
      // To prevent the for loop in the first place assign an empty array
      // in case there are no cookies at all. Also prevents odd result when
      // calling $.cookie().
      cookies = document.cookie ? document.cookie.split('; ') : [],
      i = 0,
      l = cookies.length;

    for (; i < l; i++) {
      var parts = cookies[i].split('='), name = decode(parts.shift()), cookie = parts.join('=');

      if (key === name) {
        // If second argument (value) is a function it's a converter...
        result = read(cookie, value);
        break;
      }

      // Prevent storing a cookie that we couldn't decode.
      if (!key && (cookie = read(cookie)) !== undefined) {
        result[name] = cookie;
      }
    }

    return result;
  };

  config.defaults = {};

  $.removeCookie = function (key, options) {
    // Must not alter options, thus extending a fresh object...
    $.cookie(key, '', $.extend({}, options, { expires: -1 }));
    return !$.cookie(key);
  };
}));

function FullScreen() {
  if (!document.fullscreenElement &&
      !document.mozFullScreenElement &&
      !document.webkitFullscreenElement &&
      !document.msFullscreenElement) {
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
}

$(document).ready(function() {
  $('[data-toggle="tooltip"]').tooltip();
  $(".dropdown").on("show.bs.dropdown", function() {$(this).find(".dropdown-menu").first().stop(true, true).fadeIn(200);});
  $(".dropdown").on("hide.bs.dropdown", function() {$(this).find(".dropdown-menu").first().stop(true, true).fadeOut(200);});
  $("#page_title-field .input-group, #news_text-field .input-group, #news_cat_name-field .input-group").addClass("input-group-sm");
  $("li.home-link a").text("").html('<i class="fa fa-home fa-lg"></i>');
  $(".pull-right.small").removeClass("position-absolute pull-right");

  $("body").on("click", "[data-action]", function(e) {
    var action = $(this).data("action");

    switch (action) {
      case "hide-sidebar":
        e.preventDefault();
        if ($("body").hasClass("sidebar-toggled")) {
          $("body").removeClass("sidebar-toggled");
          $("#hide-sidebar .btn-toggle").removeClass("on");
          $.cookie('sidebar-toggled', 0);
        } else {
          $("body").addClass("sidebar-toggled");
          $("#hide-sidebar .btn-toggle").addClass("on");
          $.cookie('sidebar-toggled', 1);
        }
        break;
      case "togglemenu":
        e.preventDefault();
        $("body").toggleClass("sidebar-toggled");
        break;
      case "search-box":
        e.preventDefault();
        $(".sidebar-sm .search-box").toggle();
        break;
      case "theme-settings":
        e.preventDefault();
        $("#theme-settings").toggleClass("open");
        break;
      case "fixedsidebar":
        e.preventDefault();
        if ($(".sidebar").hasClass("fixed")) {
          $(".sidebar").removeClass("fixed");
          $("#fixedsidebar .btn-toggle").removeClass("on");
          $.cookie('fixedsidebar', 1);
        } else {
          $(".sidebar").addClass("fixed");
          $("#fixedsidebar .btn-toggle").addClass("on");
          $.cookie('fixedsidebar', 0);
        }
        break;
      case "fixedmenu":
        e.preventDefault();
        if ($(".top-menu").hasClass("fixed") && $(".sidebar .header").hasClass("fixed")) {
          $(".top-menu").removeClass("fixed");
          $(".sidebar .header").removeClass("fixed");
          $("#fixedmenu .btn-toggle").removeClass("on");
          $.cookie('fixedmenu', 1);
        } else {
          $(".top-menu").addClass("fixed");
          $(".sidebar .header").addClass("fixed");
          $("#fixedmenu .btn-toggle").addClass("on");
          $.cookie('fixedmenu', 0);
        }
        break;
      case "fixedfootererrors":
        e.preventDefault();
        if ($(".errors").hasClass("fixed")) {
          $(".errors").removeClass("fixed");
          $("#fixedfootererrors .btn-toggle").removeClass("on");
          $.cookie('fixedfootererrors', 1);
        } else {
          $(".errors").addClass("fixed");
          $("#fixedfootererrors .btn-toggle").addClass("on");
          $.cookie('fixedfootererrors', 0);
        }
        break;
      case "fullscreen":
        e.preventDefault();
        $("#fullscreen .btn-toggle").toggleClass("on");
        FullScreen();
        break;
      case "sidebar-sm":
        e.preventDefault();
        if ($("body").hasClass("sidebar-sm")) {
          $("body").removeClass("sidebar-sm");
          $("#sidebar-sm .btn-toggle").removeClass("on");
          $.cookie('sidebar-sm', 0);
        } else {
          $("body").addClass("sidebar-sm");
          $("#sidebar-sm .btn-toggle").addClass("on");
          $.cookie('sidebar-sm', 1);
        }
        break;
      case "messages":
        e.preventDefault();
        $(".messages-box").toggleClass("open");
        if ($(".messages-box").hasClass("open")) {
          $("body").append('<div class="overlay"></div>');
          $("body").css("overflow-y", "hidden");
        }
        break;
    }
  });

  if ($.cookie('sidebar-sm') !== undefined) {
    if ($.cookie('sidebar-sm') == 1) {
      $("body").addClass("sidebar-sm");
      $("#sidebar-sm .btn-toggle").addClass("on");
    }
  }

  if ($.cookie('sidebar-toggled') !== undefined) {
    if ($.cookie('sidebar-toggled') == 1) {
      $("body").addClass("sidebar-toggled");
      $("#hide-sidebar .btn-toggle").addClass("on");
    }
  }

  if ($.cookie('fixedmenu') !== undefined) {
    if ($.cookie('fixedmenu') == 1) {
      $(".top-menu").removeClass("fixed");
      $(".sidebar .header").removeClass("fixed");
      $("#fixedmenu .btn-toggle").removeClass("on");
    }
  }

  if ($.cookie('fixedsidebar') !== undefined) {
    if ($.cookie('fixedsidebar') == 1) {
      $(".sidebar").removeClass("fixed");
      $("#fixedsidebar .btn-toggle").removeClass("on");
    }
  }

  if ($.cookie('fixedfootererrors') !== undefined) {
    if ($.cookie('fixedfootererrors') == 1) {
      $(".errors").removeClass("fixed");
      $("#fixedfootererrors .btn-toggle").removeClass("on");
    }
  }

  $("#search_box").focus(function() {
    $(".input-search-icon").addClass('focus');
  });

  $("#search_box").blur(function() {
    $(".input-search-icon").removeClass('focus');
  });

  if ($("body").hasClass("sidebar-sm")) {
    $(".admin-vertical-link li.active .adl-link").addClass("collapsed");
    $(".admin-vertical-link li.active .collapse").removeClass("in");
  }
});

$(document).mouseup(function (e) {
  if ($(".sidebar-sm")[0]) {
    if (!$(".admin-vertical-link .adl-link").is(e.target) && $(".admin-vertical-link .adl-link").has(e.target).length === 0) {
      $(".admin-vertical-link li .adl-link").addClass("collapsed");
      $(".admin-vertical-link li .collapse").removeClass("in");
    }
  }

  $(".overlay").remove();
  $(".messages-box").removeClass("open");
  $("body").css("overflow-y", "auto");
});