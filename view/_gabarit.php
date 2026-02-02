<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= $title_page ?></title>
        <link rel="stylesheet" href="<?= ASSET ?>css/fontawesome/css/all.min.css?<?= filemtime("/asset/css/fontawesome/css/all.min.css") ?>">
        <link rel="stylesheet" href="<?php echo ASSET; ?>css/sweetalert2.min.css?<?= filemtime("/asset/css/sweetalert2.min.css") ?>">
        <!-- croppe css -->
        <link rel="stylesheet" href="<?php echo ASSET; ?>css/cropper.min.css?<?= filemtime("/asset/css/cropper.min.css") ?>">

        <link rel="stylesheet" href="<?= ASSET ?>css/style.css?<?= filemtime("/asset/css/style.css") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/responsive.css?<?= filemtime("/asset/css/responsive.css") ?>">
        <!-- sweat alert -->
        <script src="<?php echo ASSET; ?>js/sweetalert2.all.min.js?<?= filemtime("/asset/js/sweetalert2.all.min.js") ?>"></script>
        <!-- jquery -->
        <script src="<?php echo ASSET; ?>js/jquery-2.2.4.min.js?<?= filemtime("/asset/js/jquery-2.2.4.min.js") ?>"></script>
        <!-- cropper -->
        <script src="<?php echo ASSET; ?>js/cropper.min.js?<?= filemtime("/asset/js/cropper.min.js") ?>"></script>
    </head>
    <body>
    <header>
        <div class="logo"><a href="index.html">Excel <span>Cours</span></a></div>
        <nav class="" id="mobile_menu">
        <div class="div_sortie_start_btn_mobile" id="sortie_nav_mobile">
            <button class="start_btn_mobile view"><i class="fa-solid fa-times"></i></button>
        </div>
        <ul>
            <li><a href="/contact">Contacts</a></li>
            <li><a href="/cours">Cours</a></li>
            <?php
                /* si l'utilisateur est connecter */
                if(isset($_SESSION['use_cours_excel_987654321']))
                {
                    echo '
                        <li><a href="/compte">Compte</a></li>';
                }
            ?>
        </ul>
        </nav>
        <?php
            /* si l'utilisateur est connecter */
            if(!isset($_SESSION['use_cours_excel_987654321']))
            {
                echo '
                    <a href="/connexion" class="start-btn">Se connecter</a>';
            }
            else
            {
                echo '
                    <a href="/deconnexion" class="start-btn">Se déconnecter</a>';
            }
        ?>
        <div class="div_start_btn_mobile">
        <?php
            /* si l'utilisateur est connecter */
            if(!isset($_SESSION['use_cours_excel_987654321']))
            {
                echo '
                    <a href="/connexion" class="start_btn_mobile">Se connecter</a>';
            }
            else
            {
                echo '
                    <a href="/deconnexion" class="start_btn_mobile">Se déconnecter</a>';
            }
        ?>
        <button class="start_btn_mobile view" id="voir_menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </header>
        <!-- afficher le contenue -->
        <?php echo $contentPage; ?>
        <!-- script voir menu -->
        <script>
            let
            voir_menu = document.getElementById("voir_menu"),
            mobile_menu = document.getElementById('mobile_menu'),
            sortie_nav_mobile = document.getElementById("sortie_nav_mobile")
            ;

            voir_menu.addEventListener('click',function(){
            mobile_menu.classList.add("active");
            })
            sortie_nav_mobile.addEventListener('click',function(){
            mobile_menu.classList.remove("active");
            })

        </script>

    </body>
</html>