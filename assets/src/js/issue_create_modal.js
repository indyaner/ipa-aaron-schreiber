(function ($) {

    // document ready function that listens on having all the dom elements loaded in
    jQuery(document).ready(function ($) {
        let modal = $("#codess-issue-modal")[0]; // Use the native DOM element, not a jQuery object
        let adminBarBtn = $("#wp-admin-bar-codess-github-issue-creator-adminbar-btn");
        let issue_view_port_size = document.getElementById("issue_view_port_size");
        let issue_operating_system = document.getElementById("issue_operating_system");
        let issue_title = document.getElementById("issue_title");
        let issue_description = document.getElementById("issue_description");
        let issue_current_page_url = document.getElementById("issue_current_page_url");


        // Click event for the admin bar button
        adminBarBtn.on("click", function (e) {
            e.preventDefault(); // prevent default redirect on button click
            modal.showModal(); // open modal using native method

            // fill with browser and operating system data and fills into a a hidden field: issue_operating_system
            issue_operating_system.value = navigator.userAgent;

            // gets current browser window size and saves it into hidden field: issue_view_port_size
            let viewportWidth = $(window).width();
            let viewportHeight = $(window).height();

            issue_view_port_size.value = viewportWidth + 'px x ' + viewportHeight + 'px';

            issue_current_page_url.value = window.location.href;
        });

        // Close the modal by pressing X
        $(".codess-close").on("click", function () {

            // clears values before closing the modal
            issue_operating_system.value = "";
            issue_view_port_size.value = "";
            issue_current_page_url.value = "";

            $('.alert-dismissible').remove();
            modal.close(); // close modal using native method
        });


        $(document).on("click", ".alert-dismissible > .btn-close", function () {
            $(this).closest('.alert-dismissible').remove();
        });

        // Close the modal by clicking outside the modal
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {

                // clears values before closing the modal
                issue_operating_system.value = "";
                issue_view_port_size.value = "";
                issue_current_page_url.value = "";

                $('.alert-dismissible').remove();


                modal.close(); // close modal if clicked outside
            }
        });

        $('#codess-issue-modal #submit_btn_modal').click(function (event) {
            event.preventDefault();

            // Get form data from the form
            let formData = {
                action: 'create_issue', // action hook
                nonce: modal_ajax.nonce,
                title: issue_title.value.trim(), // data from form
                description: issue_description.value.trim(), // data from form
                operating_system: issue_operating_system.value.trim(), // data from form
                view_port_size: issue_view_port_size.value.trim(), // data from form
                current_page_url: issue_current_page_url.value.trim(), // data from form
            };

            // Check form field validity before sending AJAX request
            let titleIsValid = formData.title.length >= 3 && formData.title.length <= 30;
            let descriptionIsValid = formData.description.length >= 3 && formData.description.length <= 300;

            // Reset previous validation states
            $('#issue_title, #issue_description').removeClass('is-invalid is-valid');

            // Highlight fields based on validity
            if (titleIsValid) {
                $('#issue_title').addClass('is-valid');
            } else {
                $('#issue_title').addClass('is-invalid');
                showAlert('warning', codess_github_issue_creator.title_error);
            }

            if (descriptionIsValid) {
                $('#issue_description').addClass('is-valid');
            } else {
                $('#issue_description').addClass('is-invalid');
                showAlert('warning', codess_github_issue_creator.description_error);
            }

            // If either field is invalid, stop here
            if (!titleIsValid || !descriptionIsValid) {
                return;
            }

            // If fields are valid, proceed with the AJAX request
            $.ajax({
                url: modal_ajax.ajax_url,
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Check if the response has the 'success' or 'error' status
                    if (response.status === 'error') {
                        // If there's an error, remove 'is-valid' and add 'is-invalid' for all fields
                        $('#issue_title, #issue_description, #issue_operating_system, #issue_view_port_size, #issue_current_page_url')
                            .removeClass('is-valid')
                            .addClass('is-invalid');

                        // Show error message
                        showAlert('error', response.message);  // Using response message from server
                        return; // Stop further processing
                    }

                    // If no error (status == 'success'), reset form and show success message
                    if (response.status === 'success') {
                        $('#issue_title, #issue_description').removeClass('is-invalid').addClass('is-valid'); // Confirm valid inputs

                        // Reset input values
                        issue_title.value = "";
                        issue_description.value = "";

                        showAlert('success', response.message);  // Success message from server
                    } else if (response.status === 'warning') {
                        showAlert('warning', response.message);  // Handle warnings
                    }
                },
                error: function () {
                    // Handle AJAX errors
                    $('#issue_title, #issue_description, #issue_operating_system, #issue_view_port_size, #issue_current_page_url')
                        .removeClass('is-valid')
                        .addClass('is-invalid');

                    showAlert('error', codess_github_issue_creator.ajax_error);
                }
            });

            // Function to show alert message in modal
            function showAlert(type, message) {
                let modal_content = $('#codess-issue-modal > .modal-form');
                let alertClass;

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

                modal_content.append('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">'
                    + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    + '</div>');

                // Remove the last alert if there's more than one
                if ($('.alert-dismissible').length > 1) {
                    $('.alert-dismissible').first().remove();
                }
            }
        });

    });



})(jQuery);