<!-- container all user -->
<div class="container_all_users">
    <!-- barre de recherche -->
    <div class="div_barre_recherche">
        <div class="barre_recherche">
            <input type="search" name="" id="search_user" placeholder="Recherche">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
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

<!-- search user -->
<script src="<?php echo ASSET; ?>js/search_user.js?<?= filemtime(ROOT."asset/js/search_user.js") ?>"></script>