/* global $, Swal, site_color */
(function () {
    var form = document.getElementById("admin_create_user_form");
    var passwordInput = document.getElementById("admin_user_password");
    var generateButton = document.getElementById("admin_generate_password");

    if (!form || !passwordInput || !generateButton) {
        return;
    }

    function generatePassword(length) {
        var characters = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%";
        var password = "";
        var index = 0;

        for (index = 0; index < length; index += 1) {
            password += characters.charAt(Math.floor(Math.random() * characters.length));
        }

        return password;
    }

    function showAlert(icon, title, html) {
        Swal.fire({
            icon: icon,
            title: title,
            html: html || "",
            confirmButtonText: "OK",
            confirmButtonColor: site_color,
            iconColor: site_color
        });
    }

    generateButton.addEventListener("click", function () {
        passwordInput.value = generatePassword(14);
        passwordInput.focus();
        passwordInput.select();
    });

    $("#admin_create_user_form").on("submit", function (event) {
        event.preventDefault();

        var formData = $(this).serialize();
        var submitButton = $(this).find('button[type="submit"]');
        var defaultLabel = submitButton.text();

        submitButton.prop("disabled", true).text("Création...");

        $.post("/fonctions/admin_create_user.php", formData, function (data) {
            if (data.result === "ok") {
                showAlert(
                    "success",
                    data.msg,
                    "<p><strong>Nom :</strong> " + data.user.user_name + "</p>" +
                    "<p><strong>E-mail :</strong> " + data.user.email + "</p>" +
                    "<p><strong>Mot de passe :</strong> " + data.user.password + "</p>"
                );

                form.reset();
                $("#search_user").trigger("input");
            } else {
                showAlert("error", data.msg);
            }
        }, "json").fail(function () {
            showAlert("error", "Une erreur est survenue pendant la création du compte.");
        }).always(function () {
            submitButton.prop("disabled", false).text(defaultLabel);
        });
    });
})();
