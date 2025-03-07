(function ($) {

    // Document ready function to ensure the DOM is fully loaded before any operations
    jQuery(document).ready(function ($) {
        const modal = $("#codess-issue-modal")[0]; // The modal
        const adminBarBtn = $("#wp-admin-bar-codess-github-issue-creator-adminbar-btn"); // Admin bar button
        const issue_view_port_size = document.getElementById("issue_view_port_size"); // Hidden field for viewport size
        const issue_operating_system = document.getElementById("issue_operating_system"); // Hidden field for the OS info
        const issue_title = document.getElementById("issue_title"); // User-editable field for the issue title
        const issue_description = document.getElementById("issue_description"); // User-editable field for the issue description
        const issue_current_page_url = document.getElementById("issue_current_page_url"); // Hidden field for the current page URL

        // Event listener for the admin bar button, opening the modal and pre-filling some fields
        adminBarBtn.on("click", function (e) {
            e.preventDefault(); // Prevent the default action of the button (navigation)
            modal.showModal(); // Open the modal using the native method (Dialog Element)

            // Pre-fill the hidden issue_operating_system field with the user's OS and browser information
            issue_operating_system.value = navigator.userAgent;

            // Get the current browser window size and store it in the hidden issue_view_port_size field
            const viewportWidth = $(window).width(); // Get viewport width
            const viewportHeight = $(window).height(); // Get viewport height
            issue_view_port_size.value = viewportWidth + 'px x ' + viewportHeight + 'px';

            // Store the current page URL in the hidden issue_current_page_url field
            issue_current_page_url.value = window.location.href;
        });

        // Event listener for the modal close button
        $(".codess-close").on("click", function () {
            // Clear all values from hidden fields when closing the modal
            issue_operating_system.value = "";
            issue_view_port_size.value = "";
            issue_current_page_url.value = "";

            // Remove validation classes (both valid and invalid) from the title and description fields
            $('#issue_title, #issue_description').removeClass('is-valid is-invalid');

            // Remove any displayed alert messages
            $('.alert-dismissible').remove();

            // Close the modal using the native method
            modal.close();
        });

        // Event listener for the close button in alert messages
        $(document).on("click", ".alert-dismissible > .btn-close", function () {
            // Remove the alert message when the close button is clicked
            $(this).closest('.alert-dismissible').remove();
        });

        // Event listener for clicks outside the modal to close it
        modal.addEventListener("click", function (e) {
            if (e.target === modal) { // Check if the click was outside the modal (on the backdrop)
                // Clear all values from hidden fields when closing the modal
                issue_operating_system.value = "";
                issue_view_port_size.value = "";
                issue_current_page_url.value = "";

                // Remove validation classes (both valid and invalid) from the title and description fields
                $('#issue_title, #issue_description').removeClass('is-valid is-invalid');

                // Remove any displayed alert messages
                $('.alert-dismissible').remove();

                // Close the modal using the native method
                modal.close();
            }
        });

        // Event listener for the submit button within the modal
        $('#codess-issue-modal #submit_btn_modal').click(function (event) {
            event.preventDefault(); // Prevent the default form submission

            // Collect form data (both visible and hidden fields)
            const formData = {
                action: 'create_issue', // The action to be handled by the server (AJAX action hook)
                nonce: modal_ajax.nonce, // A security token (nonce) to prevent CSRF attacks
                title: issue_title.value.trim(), // Trim the title value to remove extra spaces
                description: issue_description.value.trim(), // Trim the description value to remove extra spaces
                operating_system: issue_operating_system.value.trim(), // Hidden OS info
                view_port_size: issue_view_port_size.value.trim(), // Hidden viewport size
                current_page_url: issue_current_page_url.value.trim(), // Hidden current page URL
            };

            let editableFields = ['#issue_title', '#issue_description']; // Define the editable fields (title and description)

            // Reset validation classes (remove any 'is-valid' or 'is-invalid' classes) for the editable fields
            $(editableFields.join(', ')).removeClass('is-invalid is-valid');

            // Proceed with the AJAX request
            $.ajax({
                url: modal_ajax.ajax_url, // The URL where the AJAX request will be sent (WordPress AJAX URL)
                method: 'POST', // HTTP method (POST)
                data: formData, // Send the form data
                success: function (response) {
                    if (response.status === 'error') {
                        // If the response indicates an error, mark the editable fields as invalid and show an error message
                        $(editableFields.join(', ')).addClass('is-invalid');
                        showAlert('error', response.message); // Display an error alert
                        return;
                    }

                    if (response.status === 'warning') {
                        // If the response indicates a warning, mark fields that failed validation as invalid
                        if (response.field.includes('title')) {
                            $('#issue_title').addClass('is-invalid');
                        } else {
                            $('#issue_title').addClass('is-valid'); // If no error, mark it valid
                        }

                        if (response.field.includes('description')) {
                            $('#issue_description').addClass('is-invalid');
                        } else {
                            $('#issue_description').addClass('is-valid');
                        }

                        showAlert('warning', response.message); // Display a warning alert
                        return;
                    }

                    if (response.status === 'success') {
                        // If the request was successful, mark the editable fields as valid
                        $(editableFields.join(', ')).removeClass('is-invalid').addClass('is-valid');

                        // Reset the input values of title and description after successful submission
                        $('#issue_title, #issue_description').val('');

                        showAlert('success', response.message); // Display a success alert
                    }
                },
                error: function () {
                    // Handle AJAX request failure by marking the fields as invalid
                    $(editableFields.join(', ')).addClass('is-invalid');
                    showAlert('error', codess_github_issue_creator.ajax_error); // Display a generic error alert
                }
            });

            // Function to display alerts inside the modal (success, error, warning messages)
            function showAlert(type, message) {
                let modal_content = $('#codess-issue-modal > .modal-form');
                let alertClass;

                // Determine the alert class based on the type of message (success, error, warning)
                switch (type) {
                    case 'success':
                        alertClass = 'alert-success';
                        break;
                    case 'error':
                        alertClass = 'alert-danger';
                        break;
                    case 'warning':
                        alertClass = 'alert-warning';
                        break;
                    default:
                        alertClass = 'alert-info';
                }

                // Append the alert to the modal content
                modal_content.append('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">'
                    + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    + '</div>');

                // Remove the first alert if there are more than one to avoid multiple alerts showing at once
                if ($('.alert-dismissible').length > 1) {
                    $('.alert-dismissible').first().remove();
                }
            }
        });

    });

})(jQuery);