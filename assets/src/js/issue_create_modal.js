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

            // javascript objects
            let formData = {
                action: 'create_issue', // action hook
                nonce: modal_ajax.nonce,
                title: issue_title.value, // data from form
                description: issue_description.value, // data from form
                operating_system: issue_operating_system.value, // data from form
                view_port_size: issue_view_port_size.value, // data from form
                current_page_url: issue_current_page_url.value, // data from form
            };

            $.ajax({
                url: modal_ajax.ajax_url,
                method: 'POST',
                data: formData,
                success: function (response) {

                    issue_title.value = "";
                    issue_description.value = "";



                    if (response.status === 'success') {
                        showAlert('success', response.message);
                    } else if (response.status === 'error') {
                        showAlert('error', response.message);
                    }
                },
                error: function (response) {

                }
            });
            function showAlert(type, message) {
                let modal_content = $('#codess-issue-modal > .modal-form');
                let alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';

                modal_content.append('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">'
                    + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    + '</div>');
            }
        });

    });



})(jQuery);