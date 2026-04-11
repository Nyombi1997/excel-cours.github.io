<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isUserConnected = isset($_SESSION['use_cours_excel_987654321']);
    $isAdminConnected = isset($_SESSION['admin_cours_excel_987654321']);
    $isConnected = $isUserConnected || $isAdminConnected;
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= $title_page ?></title>
        <meta name="description" content="AbsoluHub, plateforme tech premium pour apprendre, structurer et faire grandir vos comp&eacute;tences digitales avec clart&eacute;." />
        <link rel="icon" type="image/png" href="<?= ASSET ?>images/icons/favicon-1.png?<?= filemtime(ROOT . "asset/images/icons/favicon-1.png") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/fontawesome/css/all.min.css?<?= filemtime(ROOT . "asset/css/fontawesome/css/all.min.css") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/sweetalert2.min.css?<?= filemtime(ROOT . "asset/css/sweetalert2.min.css") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/cropper.min.css?<?= filemtime(ROOT . "asset/css/cropper.min.css") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/style.css?<?= filemtime(ROOT . "asset/css/style.css") ?>">
        <link rel="stylesheet" href="<?= ASSET ?>css/responsive.css?<?= filemtime(ROOT . "asset/css/responsive.css") ?>">
        <script src="<?= ASSET ?>js/sweetalert2.all.min.js?<?= filemtime(ROOT . "asset/js/sweetalert2.all.min.js") ?>"></script>
        <script src="<?= ASSET ?>js/jquery-2.2.4.min.js?<?= filemtime(ROOT . "asset/js/jquery-2.2.4.min.js") ?>"></script>
        <script src="<?= ASSET ?>js/cropper.min.js?<?= filemtime(ROOT . "asset/js/cropper.min.js") ?>"></script>
        <script src="<?= ASSET ?>js/site_color.js?<?= filemtime(ROOT . "asset/js/site_color.js") ?>"></script>
    </head>
    <body>
        <div class="site-shell">
            <header class="site-header">
                <div class="site-header__inner">
                    <div class="logo">
                        <a href="/accueil" class="brand-lockup" aria-label="Retour &agrave; l'accueil AbsoluHub">
                            <img src="<?= ASSET ?>images/logo/AbsoluHub_Color_2.webp" alt="Logo AbsoluHub">
                        </a>
                    </div>

                    <div class="site-nav-overlay" id="mobile_menu_overlay"></div>

                    <nav id="mobile_menu" class="site-nav" aria-label="Navigation principale">
                        <div class="site-nav__mobile-head">
                            <span>Navigation</span>
                            <button class="start_btn_mobile view" id="js_close_mobile_menu" type="button" aria-label="Fermer le menu">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        <ul>
                            <li><a href="/accueil">Accueil</a></li>
                            <li><a href="/cours">Cours</a></li>
                            <li><a href="/contact">Contact</a></li>
                            <?php if ($isUserConnected) { ?>
                                <li><a href="/compte">Compte</a></li>
                            <?php } ?>
                            <?php if ($isAdminConnected) { ?>
                                <li><a href="/admin">Admin</a></li>
                            <?php } ?>
                        </ul>
                    </nav>

                    <div class="site-header__actions">
                        <div class="site-header__status">
                            <span class="site-status-dot"></span>
                            <span><?= $isConnected ? 'Session active' : 'Acc&egrave;s visiteur' ?></span>
                        </div>

                        <?php if (!$isConnected) { ?>
                            <a href="/connexion" class="start-btn">Se connecter</a>
                        <?php } else { ?>
                            <a href="/deconnexion" class="start-btn">Se d&eacute;connecter</a>
                        <?php } ?>

                        <div class="div_start_btn_mobile">
                            <?php if (!$isConnected) { ?>
                                <a href="/connexion" class="start_btn_mobile">Connexion</a>
                            <?php } else { ?>
                                <a href="/deconnexion" class="start_btn_mobile">D&eacute;connexion</a>
                            <?php } ?>
                            <button class="start_btn_mobile view" id="voir_menu" type="button" aria-label="Ouvrir le menu">
                                <i class="fa-solid fa-bars"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="site-main">
                <?= $contentPage; ?>
            </main>
        </div>

        <script src="<?= ASSET ?>js/navigation.js?<?= filemtime(ROOT . "asset/js/navigation.js") ?>"></script>
    </body>
</html>
