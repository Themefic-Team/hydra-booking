(function (window, document) {
    const Widget = {
        /**
         * Automatically initializes all widgets.
         */
        autoInit: function () {
            // Find all containers with a specific class
            const containers = document.querySelectorAll('.hydra-booking-embed-container');
            
            if (!containers.length) {
                console.error('No Hydra Booking containers found.');
                return;
            }

            containers.forEach(container => {
                // Extract meeting ID and URL from data attributes
                const meetingId = container.getAttribute('data-meeting-id');
                const url = container.getAttribute('data-url');
                
                if (!meetingId) {
                    console.error('No meeting ID found in the data-meeting-id attribute.');
                    return;
                }

                const src_url = `${url}/?hydra-booking=meeting&meetingId=${meetingId}`;

                // Create an iframe and set its properties
                const iframe = document.createElement('iframe');
                iframe.src = src_url;
                iframe.style.width = '100%'; // Set iframe width
                iframe.style.height = '100%'; // Set iframe height
                iframe.style.minHeight = '600px'; // Set minimum height
                iframe.style.border = 'none'; // Remove border
 

                // Clear the container and append the iframe
                container.innerHTML = ''; // Clear any existing content
                container.appendChild(iframe);

                // Customize iframe content after it loads
                iframe.onload = function () {
                    // Access the iframe's document
                    const iframeDoc = iframe.contentWindow.document;  
                    // Hide the wpadminbar from the iframe
                    const wpAdminBar = iframeDoc.getElementById('wpadminbar');
                    if (wpAdminBar) {
                        wpAdminBar.style.display = 'none';
                    }

                    // get the iframe tfhb-meeting-embed-section height and set it to the iframe
                    const iframeMeetingEmbedSection = iframeDoc.querySelector('.tfhb-meeting-embed-section');
                    if (iframeMeetingEmbedSection) {
                        iframe.style.height = iframeMeetingEmbedSection.offsetHeight + 'px';
                    } 
                };
             

                console.log(`Hydra Booking widget initialized for Meeting ID: ${meetingId}`);
            });
        },
    };

    // Automatically initialize the widgets when the DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', () => {
        Widget.autoInit();
    });
})(window, document);
