(function () {
    var profileForm = document.getElementById('account-profile-form');
    var fileInput = document.getElementById('file-input');
    var cropperModal = document.getElementById('cropper-modal');
    var imageToCrop = document.getElementById('image-to-crop');
    var saveAvatarButton = document.getElementById('save-cropped-avatar');
    var closeCropperButton = document.getElementById('close-cropper-modal');
    var cancelCropperButton = document.getElementById('cancel-cropper-modal');
    var uploadProgressWrap = document.getElementById('account_upload_progress_wrap');
    var uploadProgressFill = document.getElementById('account_upload_progress_fill');
    var uploadProgressText = document.getElementById('account_upload_progress_text');
    var cropper = null;

    if (!profileForm) {
        return;
    }

    function canEdit() {
        return profileForm.getAttribute('data-can-edit') === '1';
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

    function syncIdentityBlocks(name, email) {
        $('.js-account-name').text(name);
        $('.js-account-email').text(email);
    }

    function setUploadProgress(percent) {
        if (!uploadProgressWrap || !uploadProgressFill || !uploadProgressText) {
            return;
        }

        uploadProgressWrap.classList.remove('null');
        uploadProgressFill.style.width = percent + '%';
        uploadProgressText.innerText = percent + '%';
    }

    function resetUploadProgress() {
        if (!uploadProgressWrap || !uploadProgressFill || !uploadProgressText) {
            return;
        }

        uploadProgressWrap.classList.add('null');
        uploadProgressFill.style.width = '0%';
        uploadProgressText.innerText = '0%';
    }

    function openCropperModal(imageSource) {
        imageToCrop.src = imageSource;
        cropperModal.classList.add('active');
        resetUploadProgress();

        if (cropper) {
            cropper.destroy();
        }

        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            responsive: true,
            background: false
        });
    }

    function closeCropperModal() {
        cropperModal.classList.remove('active');

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (fileInput) {
            fileInput.value = '';
        }

        resetUploadProgress();
    }

    $('.account-password-toggle').on('click', function () {
        var targetSelector = $(this).data('target');
        var targetInput = $(targetSelector);
        var nextType = targetInput.attr('type') === 'password' ? 'text' : 'password';

        targetInput.attr('type', nextType);
        $(this).text(nextType === 'password' ? 'Afficher' : 'Masquer');
    });

    $('#account-profile-form, #account-security-form').on('submit', function (e) {
        e.preventDefault();

        if (!canEdit()) {
            showAlert('info', 'Ce profil est actuellement en lecture seule.');
            return;
        }

        var payload = {
            user_name: $('#account_user_name').val().trim(),
            email: $('#account_email').val().trim(),
            old_password: $('#account_old_password').val(),
            new_password: $('#account_new_password').val(),
            confirm_password: $('#account_confirm_password').val()
        };

        $.post('/fonctions/update_profile.php', payload, function (data) {
            if (data.result === 'ok') {
                syncIdentityBlocks(data.user_name, data.email);

                Swal.fire({
                    icon: 'success',
                    title: data.msg,
                    text: '',
                    confirmButtonText: 'OK',
                    confirmButtonColor: site_color,
                    iconColor: site_color,
                    timer: 1600
                });

                $('#account_old_password').val('');
                $('#account_new_password').val('');
                $('#account_confirm_password').val('');
            } else {
                showAlert('error', data.msg);
            }
        }, 'json').fail(function () {
            showAlert('error', "Une erreur est survenue pendant l'enregistrement.");
        });
    });

    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            if (!canEdit()) {
                return;
            }

            var file = e.target.files[0];
            if (!file) {
                return;
            }

            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if ($.inArray(file.type, allowedTypes) === -1) {
                showAlert('error', "Le format d'image n'est pas pris en charge.");
                fileInput.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                openCropperModal(event.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    if (saveAvatarButton) {
        saveAvatarButton.addEventListener('click', function () {
            if (!cropper) {
                showAlert('error', "Impossible de recadrer l'image.");
                return;
            }

            var canvas = cropper.getCroppedCanvas({
                width: 600,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            var base64Image = canvas.toDataURL('image/png', 0.95);

            saveAvatarButton.disabled = true;
            setUploadProgress(0);

            $.ajax({
                url: '/fonctions/upload_avatar.php',
                type: 'POST',
                dataType: 'json',
                data: { image: base64Image },
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function (event) {
                            if (event.lengthComputable) {
                                var percent = Math.round((event.loaded / event.total) * 100);
                                setUploadProgress(percent);
                            }
                        }, false);
                    }
                    return xhr;
                },
                success: function (data) {
                    if (data.result === 'ok') {
                        setUploadProgress(100);
                        $('.js-account-avatar').attr('src', data.image_url + '?t=' + new Date().getTime());

                        Swal.fire({
                            icon: 'success',
                            title: data.msg,
                            text: '',
                            confirmButtonText: 'OK',
                            confirmButtonColor: site_color,
                            iconColor: site_color,
                            timer: 1500
                        });

                        setTimeout(function () {
                            closeCropperModal();
                        }, 350);
                    } else {
                        showAlert('error', data.msg);
                    }
                },
                error: function () {
                    showAlert('error', "Une erreur est survenue pendant l'envoi de l'image.");
                },
                complete: function () {
                    saveAvatarButton.disabled = false;
                }
            });
        });
    }

    if (closeCropperButton) {
        closeCropperButton.addEventListener('click', closeCropperModal);
    }

    if (cancelCropperButton) {
        cancelCropperButton.addEventListener('click', closeCropperModal);
    }

    if (cropperModal) {
        cropperModal.addEventListener('click', function (e) {
            if (e.target === cropperModal) {
                closeCropperModal();
            }
        });
    }
})();
