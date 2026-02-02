

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin – Gestion des cours</title>
<link rel="stylesheet" href="style.css">
<style>
    body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    padding: 30px;
}

h1 {
    margin-bottom: 30px;
}

.bloc {
    background: #fff;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 8px;
}

input, select, button {
    display: block;
    width: 100%;
    margin-bottom: 10px;
    padding: 10px;
}

button {
    background: #4f46e5;
    color: white;
    border: none;
    cursor: pointer;
}

</style>
</head>
<body>

<h1>🎓 Gestion des cours</h1>

<section class="bloc">
<h2>📂 Ajouter une vidéo</h2>

<form action="save_video.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="titre" placeholder="Titre de la vidéo" required>

    <input type="number" name="chapitre_id" placeholder="ID Chapitre" required>

    <input type="number" name="ordre" placeholder="Ordre d'affichage" required>

    <input type="file" name="video" accept="video/*" required>

    <button>Uploader la vidéo</button>
</form>
</section>

<section class="bloc">
<h2>🧪 Ajouter un quiz</h2>

<form id="quizForm">
    <input type="number" name="chapitre_id" placeholder="ID Chapitre" required>

    <input type="text" name="question[]" placeholder="Question 1" required>

    <input type="text" name="reponse[0][]" placeholder="Réponse A">
    <input type="text" name="reponse[0][]" placeholder="Réponse B">
    <input type="text" name="reponse[0][]" placeholder="Réponse C">

    <select name="bonne[0]">
        <option value="0">A</option>
        <option value="1">B</option>
        <option value="2">C</option>
    </select>

    <button type="button" onclick="ajouterQuestion()">➕ Ajouter une question</button>
    <button type="submit">Enregistrer le quiz</button>
</form>
</section>

<script src="script.js"></script>
</body>
</html>
