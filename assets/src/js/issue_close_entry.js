import 'bootstrap';
(function ($) {
    jQuery(document).ready(function ($) {
        // Add event listener for delete buttons
        $('.delete-issue').on('click', function () {
            const issueId = $(this).data('issue-id'); // Get issue ID from data attribute

            // Confirm if the user really wants to delete
            if (confirm(codess_github_issue_close.confirm_close_message)) {
                let formData = {
                    action: 'close_issue', // action hook
                    nonce: close_ajax.nonce, // Include nonce for security
                    issue_id: issueId, // Issue ID from the clicked delete button
                };

                $.ajax({
                    url: close_ajax.ajax_url, // WordPress admin-ajax URL
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        // Handle success response
                        if (response.status === 'success') {
                            // Try to remove the issue card with the correct ID
                            $('#collapse-' + issueId).fadeOut(500, function () {
                                $(this).remove(); // Remove the issue from the DOM after fading out
                            });
                        } else if (response.status === 'error') {
                            showAlert('error', response.message); // Error: Show error message
                        }
                    },
                    error: function (response) {
                        // Handle error response
                        showAlert('error', 'Error: ' + response.responseText); // Show error if AJAX fails
                    }
                });
            }
        });

        // Function to show alert messages
        function showAlert(type, message) {
            // Display the message using the standard JavaScript alert function
            alert(message);
        }
    });
})(jQuery);