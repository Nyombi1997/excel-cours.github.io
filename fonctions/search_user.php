<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    $q = html_entity_decode(filter_var($_POST['q'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $query = found($q, $limit = null, $offset = 0, $order = null, $random = false);
    $donnee = '';
    foreach($query as $query_)
    {
        $donnee .= '
                    <a href="'.$query_['label'].'" class="detail_user">
                        <div class="sous_detail_user">
                            <div class="profil_manage_user">
                                <div class="picture_profil_manage_user">
                                    <img src="/asset/images/profile/'.$query_['profile'].'" alt="" srcset="">
                                </div>
                                <div class="nom_profil_manage_user">
                                    '.$query_['label'].'
                                </div>
                                <div class="email_profil_manage_user">
                                    '.$query_['email'].'
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
                                        '.date_fr_short($query_['date_ajout']).'
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>';
    }

    if($q=='')
    {
            /* afficher les utilisateurs */
            $user = select_bdd($bdd, "utilisateur", $where = "admin = '0'", $limit = null, $offset = 0, $order = "id", $random = false);
            foreach($user as $users)
            {
                $donnee .= '
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
    }
    
    $results = [
        "result" => "ok",
        "msg" => $donnee
    ];

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>