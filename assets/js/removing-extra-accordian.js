jQuery(document).ready(function ($) {

    function isEmpty(el) {
        var meaningful = [];

        $(el).contents().each(function () {
            // Text node with real content
            if (this.nodeType === 3 && $.trim(this.nodeValue) !== "") {
                meaningful.push(this);
            }

            // Element node with non-empty text
            if (this.nodeType === 1 && $.trim($(this).text()) !== "") {
                meaningful.push(this);
            }
        });

        return meaningful.length === 0;
    }

    // 1. Remove all empty inner containers inside ministries-group
    $('.ministries-group .elementor-element, .ministries-group .e-con').each(function () {
        if (isEmpty(this)) {
            $(this).remove();
        }
    });

    // 2. Remove parent containers that became empty
    $('.ministries-group .elementor-element, .ministries-group .e-con').each(function () {
        if (isEmpty(this)) {
            $(this).remove();
        }
    });

    // 3. Remove the top-level ministries-group if fully empty
    $('.ministries-group').each(function () {
        if (isEmpty(this)) {
            $(this).remove();
        }
    });

});
