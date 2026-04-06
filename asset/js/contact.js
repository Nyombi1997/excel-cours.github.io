(function () {
    var contactForm = document.getElementById('contact-message-form');
    var redirectField = document.getElementById('contact_redirect_url');

    if (!contactForm) {
        return;
    }

    function showAlert(icon, title) {
        Swal.fire({
            icon: icon,
            title: title,
            text: '',
            confirmButtonText: 'OK',
            confirmButtonColor: site_color,
            iconColor: site_color
        });
    }

    $('#contact-message-form').on('submit', function (e) {
        e.preventDefault();

        var message = $('#contact_message').val().trim();

        if (message.length < 3) {
            showAlert('error', 'Merci de saisir un message un peu plus détaillé.');
            return;
        }

        $.post('/fonctions/contact_send.php', $(this).serialize(), function (data) {
            if (data.result === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: data.msg,
                    text: '',
                    confirmButtonText: 'OK',
                    confirmButtonColor: site_color,
                    iconColor: site_color,
                    timer: 1600
                }).then(function () {
                    /*
                        On remet le formulaire à zéro après succès
                        pour garder une interface propre, comme demandé.
                    */
                    contactForm.reset();

                    if (data.conversation_id && redirectField) {
                        window.location = redirectField.value + data.conversation_id;
                    }
                });
            } else {
                showAlert('error', data.msg);
            }
        }, 'json').fail(function () {
            showAlert('error', "Une erreur est survenue pendant l'envoi du message.");
        });
    });
})();
