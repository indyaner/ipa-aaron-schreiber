import 'bootstrap';

(function ($) {
    jQuery(document).ready(function ($) {
        // Add event listener for the "close-issue" buttons (delete buttons for issues)
        $('.close-issue').on('click', function () {
            const issueId = $(this).data('issue-id'); // Retrieve the issue ID stored in the data attribute of the clicked button

            // Confirm with the user if they are sure about deleting the issue
            if (confirm(codess_github_issue_close.confirm_close_message)) {
                // Prepare the data to be sent to the server for closing the issue
                const formData = {
                    action: 'close_issue', // WordPress action hook to handle the issue close
                    nonce: close_ajax.nonce, // Security nonce to prevent CSRF attacks
                    issue_id: issueId, // The ID of the issue being closed (from the clicked button)
                };

                // Make an AJAX request to close the issue
                $.ajax({
                    url: close_ajax.ajax_url, // The WordPress admin-ajax URL
                    method: 'POST', // Use the POST method for sending data
                    data: formData, // Send the form data (action, nonce, issue ID)
                    success: function (response) {
                        // Handle the successful response from the server
                        if (response.status === 'success') {
                            // If the issue is closed successfully, fade out the corresponding issue card
                            $('#collapse-' + issueId).fadeOut(500, function () {
                                $(this).remove(); // Remove the issue card from the DOM after it fades out
                            });
                        } else if (response.status === 'error') {
                            // If there is an error with the closure, show the error message
                            showAlert('error', response.message); // Display the error message
                        }
                    },
                    error: function (response) {
                        // Handle any error response from the AJAX request (e.g., network error)
                        showAlert('error', 'Error: ' + response.responseText); // Display a generic error message
                    }
                });
            }
        });

        // Function to display alert messages (success, error, or other types)
        function showAlert(type, message) {
            // For now, simply display the message using the browser's default alert box
            // This could be replaced with a custom alert/modal in the future if needed
            alert(message);
        }
    });
})(jQuery);