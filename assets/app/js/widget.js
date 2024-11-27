(function ($, window, document) {
    const Widget = {
        /**
         * Initialize the widget.
         * @param {Object} options - Configuration options.
         */
        init: function (options) {
            const defaults = {
                containerId: 'widget-container', // The ID of the container div
                url: 'https://example.com/widget', // URL for the iframe or external content
                width: '100%', // Widget width
                height: '500px', // Widget height
                params: {}, // Additional query parameters
            };

            // Merge user options with defaults
            const config = $.extend({}, defaults, options);

            // Validate container
            const $container = $(`#${config.containerId}`);
            if (!$container.length) {
                console.error(`Container with ID "${config.containerId}" not found.`);
                return;
            }

            // Build query string from params
            const queryString = $.param(config.params);
            const iframeSrc = queryString ? `${config.url}?${queryString}` : config.url;

            // Inject the iframe into the container
            const $iframe = $('<iframe>', {
                src: iframeSrc,
                css: {
                    width: config.width,
                    height: config.height,
                    border: 'none',
                },
            });

            // Clear existing content and append the iframe
            $container.empty().append($iframe);

            console.log(`Widget initialized in #${config.containerId}`);
        },
    };

    // Expose the widget globally
    window.Widget = Widget;
})(jQuery, window, document);
