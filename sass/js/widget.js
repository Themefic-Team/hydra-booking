(function (window, document) {
    const Widget = {
        /**
         * Automatically initializes the widget.
         */
        autoInit: function () {
            // Find the container by its ID
            const container = document.getElementById('hydra-booking-embed-container');
            if (!container) {
                console.error('Hydra Booking container not found.');
                return;
            }

            // Extract the meeting ID from the data attribute
            const meetingId = container.getAttribute('data-meeting-id');
            if (!meetingId) {
                console.error('No meeting ID found in the data-meeting-id attribute.');
                return;
            }

            // Define the URL of the widget page
            const url = `http://hydra-booking.local/?hydra-booking=meeting&meetingId=${meetingId}`;
            // Get The the height of the iframe 


            // Create an iframe and set its properties
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%'; // Set iframe width 
            iframe.style.height = '100%'; // Set iframe width 
            // set Min height
            iframe.style.minHeight = '650px';
            iframe.style.border = 'none'; // Remove border

            // Clear the container and append the iframe
            container.innerHTML = ''; // Clear any existing content
            container.appendChild(iframe);
            // want to hide all iframe body and only show ".tfhb-meeting-embed-section" div form the inserted iframe
            iframe.onload = function () { 
                // hide te  wpadminbar from the iframe
                const iframeDoc = iframe.contentWindow.document;
                const wpAdminBar = iframeDoc.getElementById('wpadminbar');
                if (wpAdminBar) {
                    wpAdminBar.style.display = 'none';
                }
                
                
            }
            
              

            
            

            console.log(`Hydra Booking widget initialized with Meeting ID: ${meetingId}`);
        },
    };

    // Automatically initialize the widget when the DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', () => {
        Widget.autoInit();
    });
})(window, document);
