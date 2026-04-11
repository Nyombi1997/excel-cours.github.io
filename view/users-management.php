<!-- container all user -->
<div class="container_all_users">
    <div class="admin-user-shell">
        <section class="admin-user-create-card">
            <div class="admin-user-create-card__head">
                <div>
                    <span class="footer-kicker">Gestion utilisateurs</span>
                    <h2>Cr&eacute;er un nouvel utilisateur</h2>
                    <p>Ajoutez un compte depuis l'espace admin et g&eacute;n&eacute;rez un mot de passe si vous ne souhaitez pas le saisir manuellement.</p>
                </div>
            </div>

            <form id="admin_create_user_form" class="admin-user-form" autocomplete="off">
                <div class="admin-user-form__grid">
                    <div class="admin-user-field">
                        <label for="admin_user_name">Nom d'utilisateur</label>
                        <input type="text" id="admin_user_name" name="user_name" placeholder="Ex. Jean Dupont" required>
                    </div>

                    <div class="admin-user-field">
                        <label for="admin_user_email">Adresse e-mail</label>
                        <input type="email" id="admin_user_email" name="email" placeholder="nom@domaine.com" required>
                    </div>

                    <div class="admin-user-field admin-user-field--full">
                        <label for="admin_user_password">Mot de passe</label>
                        <div class="admin-user-password-row">
                            <input type="text" id="admin_user_password" name="password" placeholder="Saisir un mot de passe ou en g&eacute;n&eacute;rer un" required>
                            <button type="button" class="account-secondary-btn admin-user-generate-btn" id="admin_generate_password">G&eacute;n&eacute;rer</button>
                        </div>
                        <small>Le mot de passe g&eacute;n&eacute;r&eacute; peut ensuite &ecirc;tre communiqu&eacute; &agrave; l'utilisateur.</small>
                    </div>
                </div>

                <div class="admin-user-form__actions">
                    <button type="submit" class="account-primary-btn">Cr&eacute;er l'utilisateur</button>
                </div>
            </form>
        </section>

        <section class="admin-user-list-card">
            <div class="div_barre_recherche">
                <div class="barre_recherche">
                    <input type="search" name="" id="search_user" placeholder="Rechercher un utilisateur">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
            </div>

            <div class="admin-user-list-card__body">
                <div class="admin-user-list-card__head">
                    <span class="footer-kicker">Utilisateurs inscrits</span>
                    <h2>Liste des utilisateurs</h2>
                </div>

                <!-- div_detail_user -->
                <div class="div_detail_user" id="div_detail_user">
                    <!-- no actif user -->
                    <!-- details -->
                    <?php
                        /* afficher les utilisateurs */
                        $user = select_bdd($bdd, "utilisateur", $where = "admin = '0'", $limit = null, $offset = 0, $order = "id", $random = false);
                        foreach($user as $users)
                        {
                            echo '
                                <a href="'.$users['slug'].'" class="detail_user">
                                    <div class="sous_detail_user">
                                        <div class="profil_manage_user">
                                            <div class="picture_profil_manage_user">
                                                <img src="'.ASSET.'images/profile/'.$users['profile'].'" alt="" srcset="">
                                            </div>
                                            <div class="nom_profil_manage_user">
                                                '.$users['user_name'].'
                                            </div>
                                            <div class="email_profil_manage_user">
                                                '.$users['email'].'
                                            </div>
                                        </div>
                                        <div class="status_manage_user">
                                            <div class="details_manage_user">
                                                <div class="titre_details_manage_user">
                                                    Status
                                                </div>
                                                <div class="text_details_manage_user">
                                                    Actif
                                                </div>
                                            </div>
                                            <div class="details_manage_user">
                                                <div class="titre_details_manage_user">
                                                    Date inscription
                                                </div>
                                                <div class="text_details_manage_user">
                                                    '.date_fr_short($users['date_ajout']).'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>';
                        }
                        /* si y'a personne  */
                        if(count($user) == 0)
                        {
                            echo '
                                <div class="div_no_actif_user_manage_user">
                                    Aucun utilisateur enregistrer
                                </div>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- search user -->
<script src="<?php echo ASSET; ?>js/search_user.js?<?= filemtime(ROOT."asset/js/search_user.js") ?>"></script>
<script src="<?php echo ASSET; ?>js/admin_users.js?<?= filemtime(ROOT."asset/js/admin_users.js") ?>"></script>
