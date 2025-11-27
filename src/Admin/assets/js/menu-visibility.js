jQuery(document).ready(function($) {

    let spinner; // Global spinner variable

    function showLoader(target) {
        const opts = {
            lines: 12,
            length: 7,
            width: 5,
            radius: 10,
            color: '#0073aa',
            top: '50%',
            left: '50%',
            position: 'absolute'
        };
        spinner = new Spinner(opts).spin(target);
    }

    function hideLoader() {
        if (spinner) {
            spinner.stop();
        }
    }

    function load_menu_pages(menu_id) {
        const $tbody = $('#menu-pages-tbody');
        $tbody.empty();

        // Create overlay for loader
        let $overlay = $('#loader-overlay');
        if ($overlay.length === 0) {
            $overlay = $('<div id="loader-overlay"></div>').css({
                position: 'fixed',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                background: 'rgba(255, 255, 255, 0.7)',
                zIndex: 9999
            }).appendTo('body');
        }
        showLoader($overlay[0]);

        $.ajax({
            url: MenuVisibility.ajax_url,
            method: 'POST',
            data: {
                action: 'load_menu_pages',
                menu_id: menu_id,
                nonce: MenuVisibility.nonce
            },
            success: function(res) {
                hideLoader();
                $overlay.remove();
                $tbody.empty();

                if (res.success) {
                    res.data.pages.forEach(function(page) {
                        const checked = page.visible ? 'checked' : '';
                        $tbody.append(`
                            <tr>
                                <td>${page.title}</td>
                                <td>
                                    <label class="dev-switch">
                                        <input type="checkbox" class="page-visibility-toggle" data-menu-item-id="${page.ID}" ${checked}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                            </tr>
                        `);

                    });
                }
            },
            error: function() {
                hideLoader();
                $overlay.remove();
                alert('Failed to load pages.');
            }
        });
    }

    // Menu change
    $('#selected_menu').on('change', function() {
        load_menu_pages($(this).val());
    });

    // Toggle visibility
    $('#menu-pages-tbody').on('change', '.page-visibility-toggle', function() {
        const menu_item_id = $(this).data('menu-item-id');
        const visible = $(this).is(':checked') ? 1 : 0;

        $.post(MenuVisibility.ajax_url, {
            action: 'update_page_visibility',
            menu_item_id: menu_item_id,
            visible: visible,
            nonce: MenuVisibility.nonce
        });
    });

    // Initial load
    if ($('#selected_menu').val()) {
        load_menu_pages($('#selected_menu').val());
    }

});
