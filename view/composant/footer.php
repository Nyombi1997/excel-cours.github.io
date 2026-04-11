<footer class="footer">
    <div class="div_logo_footer">
        <div class="div_img_logo_footer">
            <img src="<?= ASSET ?>images/logo/AbsoluHub-N-B.webp" alt="Logo AbsoluHub">
        </div>
    </div>

    <div class="container_rubriques_footer">
        <div class="contact_rubriques_footer">
            <span class="footer-kicker">AbsoluHub</span>
            <h3 class="footer-title">Une identit&eacute; digitale claire, moderne et fiable.</h3>
            <div class="details_contact_rubriques_footer">
                <div class="sous_details_contact_rubriques_footer">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Pr&eacute;sence digitale et accompagnement &agrave; distance</span>
                </div>
                <div class="sous_details_contact_rubriques_footer">
                    <i class="fa-solid fa-phone"></i>
                    <span>+243 813 689 713</span>
                </div>
                <div class="sous_details_contact_rubriques_footer">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Contact via l'espace messagerie du site</span>
                </div>
            </div>
        </div>

        <div class="div_souscription_rubriques_footer">
            <div class="text_souscription_rubriques_footer">
                Restez connect&eacute; aux nouveaut&eacute;s et aux nouvelles ressources de
                <span class="nom_logo_text_souscription_rubriques_footer">AbsoluHub</span>.
            </div>
            <div class="div_input_rubriques_footer">
                <div class="input_rubriques_footer">
                    <form action="" method="post" id="form_souscription_new_letter">
                        <input type="email" id="email_souscription_new_letter" placeholder="Entrez votre adresse e-mail" required>
                        <button type="submit">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">
        Tous droits r&eacute;serv&eacute;s <strong>AbsoluHub</strong> <span>|</span> design et exp&eacute;rience pens&eacute;s pour une image premium.
    </div>
</footer>
<script src="<?php echo ASSET; ?>js/footer.js?<?= filemtime(ROOT . "asset/js/footer.js") ?>"></script>
