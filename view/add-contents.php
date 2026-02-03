<div class="container_ajout_contents">
    <div class="div_ajout_contents">
        <button class="ajout_contents" id="add_video">Ajouter une vidéo</button>
        <button class="ajout_contents active">Ajouter un quiz</button>
    </div>
</div>
<!-- pop up -->
<div class="container_popup" id="container_popup">
    <div class="background" id="background"></div>
    <!-- pop up -->
    <div class="div_video_contents" id="video_contents">
        <div class="titre_popup_contents">Ajouter une vidéo</div>
        <div class="div_form_contents">
            <label for="">Titre</label>
            <input type="text" name="" id="titre_video" placeholder="Titre de la video">
        </div>
        <div class="div_form_contents">
            <label for="">Charger une vidéo</label>
            <input type="file" name="" id="video" accept="video/*">
        </div>
        <div class="div_form_contents">
            <textarea name="" id="description_video" placeholder="Description de la vidéo"></textarea>
        </div>
        <div class="div_form_contents">
            <label for="">Charger un fichier</label>
            <input type="file" name="" id="fichier_video" accept="*">
        </div>
        <div class="div_form_contents">
            <button type="button" id="enregistrer_video">Enregistrer</button>
        </div>
        <div class="div_form_contents null" id="progress">
        </div>
    </div>
    <!-- quiz -->
    <div class="div_quiz_contents active" id="quiz_contents">
        <div class="titre_popup_contents">Ajouter un quiz</div>
        <div class="div_form_contents">
            <textarea name="" id="" placeholder="Titre de la question"></textarea>
        </div>
        <div class="div_form_contents">
            <button>Ajouter une question</button>
            <div class="quiz_form">
                <input type="radio" name="choix" id="">
                <input type="text" name="" id="" placeholder="Réponse 1">
            </div>
            <div class="quiz_form">
                <input type="radio" name="choix" id="">
                <input type="text" name="" id="" placeholder="Réponse 2">
            </div>
        </div>
        <div class="div_form_contents">
            <button type="button">Enregistrer</button>
        </div>
    </div>
</div>
<!-- cropper -->
<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
