jQuery(document).ready(function($) {
    $('#url-preview-form').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
        var url = $('#url-input').val(); // Get the URL from the input field

        // AJAX request to fetch the URL preview
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'url_preview', // Action hook for WordPress
                url: url
            },
            beforeSend: function() {
                $('#url-preview-output').html('<p>Loading preview...</p>'); // Show loading message
            },
            success: function(response) {
                $('#url-preview-output').html(response); // Display the response
                // If response contains an iframe, call resizeIframe to adjust its height
                $('#url-preview-output iframe').on('load', function() {
                    resizeIframe(this); // Resize the iframe after it loads
                });
            },
            error: function() {
                $('#url-preview-output').html('<p>There was an error fetching the preview.</p>'); // Show error message
            }
        });
    });
});