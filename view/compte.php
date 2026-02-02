<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /* si l'utilisateur est connecter */
    if(!isset($_SESSION['use_cours_excel_987654321']))
    {
        header("location: connexion");
    }
    else
    {
        /* retrouver le profil utilisateur */
        $user = select_bdd($bdd, "utilisateur", $where = "unique_id = '".$_SESSION['use_cours_excel_987654321']."'", $limit = null, $offset = 0, $order = null, $random = false);
        if(count($user)==0)
        {
            header("location: connexion");
        }
        else
        {
            $user = $user[0];
            if($user['profile']=='')
            {
                $profile = ASSET.'images/profile/default.jpg';
            }
            else
            {
                $profile = ASSET.'images/profile/'.$user['profile'];
            }
        }
    }

?>
<div class="container_profile_cours_excel">
    <aside class="sidebar">
        <div class="card" style="padding: 15px; box-shadow: none; border: 1px solid #edf2f7;">
            <span style="font-weight: bold;">Ma Progression</span>
            <div class="progress-bar"><div class="progress-fill"></div></div>
            <small>65% complété - Module 4</small>
            <a href="" class="btn" style="margin-top: 10px; padding: 8px;">Continuer le cours</a>
        </div>
        
        <div style="margin-top: auto;">
            <h4>Besoin d'aide ?</h4>
            <textarea id="supportMsg" placeholder="Votre question..." rows="3"></textarea>
            <button onclick="sendSupport()" style="margin-top: 10px; padding: 8px;">Contacter le support</button>
        </div>
    </aside>

    <main class="main-content">
        <div class="card">
            <h3>Informations Personnelles</h3>
            <div class="profile-pic-container">
                <img src="<?= $profile ?>" id="avatar-preview" class="profile-pic">
                <label for="file-input" class="upload-btn">＋</label>
                <input type="file" id="file-input" style="display:none" accept="image/*">
            </div>
            
            <!-- form -->
            <div action="update_profile.php" method="POST" class="grid-form">
                <div>
                    <label>Nom complet</label>
                    <input type="text" name="username" value="<?= $user['user_name'] ?>">
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $user['email'] ?>">
                </div>
                <div class="full-width">
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                    <h4>Sécurité</h4>
                </div>
                <div>
                    <label>Ancien mot de passe</label>
                    <input type="password" name="old_pwd">
                </div>
                <div>
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_pwd">
                </div>
                <div class="full-width">
                    <button type="submit">Mettre à jour mon compte</button>
                </div>
            </div>
        </div>
    </main>

    <div id="cropper-modal">
        <div class="cropper-box">
            <div style="height: 300px; margin-bottom: 20px;">
                <img id="image-to-crop" style="max-width: 100%;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="cropImage()" style="flex:2">Valider</button>
                <button onclick="closeModal()" style="flex:1; background:#718096;">Annuler</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Logique Cropper (identique mais adaptée au responsive)
    let cropper;
    const fileInput = document.getElementById('file-input');
    const imageToCrop = document.getElementById('image-to-crop');
    const modal = document.getElementById('cropper-modal');

    fileInput.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                imageToCrop.src = event.target.result;
                modal.style.display = 'flex';
                if(cropper) cropper.destroy();
                cropper = new Cropper(imageToCrop, { 
                    aspectRatio: 1, 
                    viewMode: 1,
                    responsive: true 
                });
            };
            reader.readAsDataURL(file);
        }
    };

    function cropImage() {
        const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
        const dataURL = canvas.toDataURL();
        document.getElementById('avatar-preview').src = dataURL;
        
        // Ici, on envoie le dataURL au serveur via AJAX/Fetch
        fetch('/fonctions/upload_avatar.php', {
            method: 'POST',
            body: JSON.stringify({ image: dataURL }),
            headers: { 'Content-Type': 'application/json' }
        });
        closeModal();
    }

    function closeModal() { modal.style.display = 'none'; }
    
    function sendSupport() {
        const msg = document.getElementById('supportMsg').value;
        document.getElementById('supportMsg').value = "";
        /* composer le message whatsapp */
        const message = msg+``;

        const whatsappLink =
        "https://wa.me/+243813689713?text=" +
        encodeURIComponent(message);
        
        window.open(whatsappLink, "_blank");
    }
</script>