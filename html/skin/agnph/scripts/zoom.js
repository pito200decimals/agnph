function SetUpFontSizes(cookie_name, switcher_container_selector, positive_selector, negative_selector) {
    $(document).ready(function() {
        selector_container = $(switcher_container_selector);
        selector = selector_container.find("select");
        if (selector_container.length == 0 || selector.length == 0) return;

        function setZoom(zoom) {
            // Update selectors.
            var found = false;
            var options = selector.children().each(function() {
                if (zoom.trim() == $(this).text().trim()) {
                    found = true;
                    $(this).prop("selected", true);
                    setCookie(cookie_name, zoom);
                    updateFontSize(zoom);
                }
            });
            if (!found) setZoom("100%");
        }

        function updateFontSize(zoom) {
            var sizable_elements = $(positive_selector);
            if (negative_selector != undefined) {
                sizable_elements = sizable_elements.children(":not(" + negative_selector + ")");
            }
            sizable_elements.css("font-size", zoom);
        }

        // Set zoom level of page to cookie value, initially. If empty, uses default in setZoom.
        setZoom(getCookie(cookie_name));
        selector.change(function() {
            setZoom($(this).val());
        });
        selector_container.show();  // Only show if js is enabled.
    });
}

// By default, set up basic zoom controls.
SetUpFontSizes("zoom", ".site-font-size-switcher", ".font-scalable", ".not-font-scalable");