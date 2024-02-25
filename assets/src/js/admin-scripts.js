
jQuery(document).ready(function ($) {
    var modal = document.getElementById("codess-issue-modal");
    var adminBarBtn = $("#wp-admin-bar-codess-github-issue-creator-adminbar-btn");

    // Klick-Event für den Admin-Bar-Button
    adminBarBtn.on("click", function (e) {
        e.preventDefault();
        modal.showModal(); // Modal öffnen
    });

    // Schließen des Modals durch Klick auf das "X"
    $(".codess-close").on("click", function () {
        modal.close(); // Modal schließen
    });

    // Schließen durch Klick außerhalb des Modals
    modal.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.close();
        }
    });
});