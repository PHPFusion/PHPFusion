$(function () {
    $("[data-toggle=\"control-sidebar\"]").controlSidebar();

    /**
     * List of all the available skins
     *
     * @type Array
     */
    var mySkins = [
        "skin-blue",
        "skin-black",
        "skin-red",
        "skin-yellow",
        "skin-purple",
        "skin-green",
        "skin-blue-light",
        "skin-black-light",
        "skin-red-light",
        "skin-yellow-light",
        "skin-purple-light",
        "skin-green-light"
    ];

    /**
     * Get a prestored setting
     *
     * @param String name Name of of the setting
     * @returns String The value of the setting | null
     */
    function get(name) {
        if (typeof (Storage) !== "undefined") {
            return localStorage.getItem(name);
        }
    }

    /**
     * Store a new settings in the browser
     *
     * @param String name Name of the setting
     * @param String val Value of the setting
     * @returns void
     */
    function store(name, val) {
        if (typeof (Storage) !== "undefined") {
            localStorage.setItem(name, val);
        }
    }

    /**
     * Remove a settings in the browser
     *
     * @param String name Name of the setting
     * @returns void
     */
    function remove(name) {
        if (typeof (Storage) !== "undefined") {
            localStorage.removeItem(name);
        }
    }

    /**
     * Toggles layout classes
     *
     * @param String cls the layout class to toggle
     * @returns void
     */
    function changeLayout(cls) {
        $("body").toggleClass(cls);
    }

    /**
     * Replaces the old skin with the new skin
     * @param String cls the new skin class
     * @returns Boolean false to prevent link's default action
     */
    function changeSkin(cls) {
        $.each(mySkins, function (i) {
            $("body").removeClass(mySkins[i]);
        });

        $("body").addClass(cls);
        store("skin", cls);

        if (cls.match("light")) {
            $(".control-sidebar").addClass("control-sidebar-light");
        } else {
            $(".control-sidebar").removeClass("control-sidebar-light");
        }

        return false;
    }

    /**
     * Retrieve default settings and apply them to the template
     *
     * @returns void
     */
    function setup() {
        var tmp = get("skin");
        if (tmp && $.inArray(tmp, mySkins)) changeSkin(tmp);

        if (get("layout-fixed")) changeLayout("fixed");
        if (get("layout-sidebar-collapse")) changeLayout("sidebar-collapse");

        // Add the change skin listener
        $("[data-skin]").on("click", function (e) {
            if ($(this).hasClass("knob")) return;
            e.preventDefault();
            changeSkin($(this).data("skin"));
        });

        // Add the layout manager
        $("[data-layout]").on("click", function () {
            changeLayout($(this).data("layout"));

            store("layout-" + $(this).data("layout"), $(this).data("layout"));
        });

        $("[data-controlsidebar]").on("click", function () {
            changeLayout($(this).data("controlsidebar"));
        });

        $("[data-sidebarskin=\"toggle\"]").on("click", function () {
            var $sidebar = $(".control-sidebar");
            if ($sidebar.hasClass("control-sidebar-dark")) {
                $sidebar.removeClass("control-sidebar-dark");
                $sidebar.addClass("control-sidebar-light");
            } else {
                $sidebar.removeClass("control-sidebar-light");
                $sidebar.addClass("control-sidebar-dark");
            }
        });

        //  Reset options
        if ($("body").hasClass("fixed")) {
            $("[data-layout=\"fixed\"]").attr("checked", "checked");

            $("[data-layout=\"fixed\"]").on("click", function () {
                remove("layout-fixed");
            });
        }

        if ($("body").hasClass("sidebar-collapse")) {
            $("[data-layout=\"sidebar-collapse\"]").attr("checked", "checked");

            $("[data-layout=\"sidebar-collapse\"]").on("click", function () {
                remove("layout-sidebar-collapse");
            });
        }

        var skin = get("skin");

        if (skin && skin.match("light")) {
            $(".control-sidebar").addClass("control-sidebar-light");
        }
    }

    setup();
});
