
<?php
session_start();

$fichier = "personne.json";

// Initialisation session
if (!isset($_SESSION['tabpers'])) {
    $_SESSION['tabpers'] = [];
}

// Charger depuis JSON
if (file_exists($fichier)) {
    $_SESSION['tabpers'] = json_decode(file_get_contents($fichier), true);
}

if (isset($_POST['prenom'], $_POST['nom'], $_POST['adr'], $_POST['tel'])) {

    $personne = [
        "prenom" => $_POST['prenom'],
        "nom"    => $_POST['nom'],
        "adresse"    => $_POST['adr'],
        "telephone"    => $_POST['tel']
    ];

    // Ajouter à la session
    $_SESSION['tabpers'][] = $personne;

    // Enregistrer dans le fichier JSON
    file_put_contents($fichier, json_encode($_SESSION['tabpers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    // / empêche la répétition
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TP2 Form & Table</title>
    <link rel="stylesheet" href="css/bootstrap.css">
</head>

<body>
    <!-- #chaque personne est ajouter dans un fichier appele personne.json et dans un tableau de personne afficher le tableau de personne dans la table 
     -->
    
    <h1 class="text-center text-primary">TP Form & Table</h1>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <form method="post">
                        <div class="card-header">Ajout Personne</div>
                        <div class="card-body">
                            <div>
                                <label for="prenom">Prénom</label>
                                <input class="form-control" type="text" name="prenom" id="prenom">
                            </div>
                            <div>
                                <label for="nom">Nom</label>
                                <input class="form-control" type="text" name="nom" id="nom">
                            </div>
                            <div>
                                <label for="adr">Adresse</label>
                                <input class="form-control" type="text" name="adr" id="adr">
                            </div>
                            <div>
                                <label for="tel">Téléphone</label>
                                <input class="form-control" type="text" name="tel" id="tel">
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Liste des personnes</div>
                    <div class="card-body">
                    <table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Adresse</th>
            <th>Téléphone</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($_SESSION['tabpers'])) { ?>
            <tr>
                <td colspan="5" class="text-center">Aucune personne</td>
            </tr>
        <?php } else {
            foreach ($_SESSION['tabpers'] as $key => $pers) { ?>
                <tr>
                    <td><?= $key + 1 ?></td>
                    <td><?= $pers['prenom'] ?></td>
                    <td><?= $pers['nom'] ?></td>
                    <td><?= $pers['adresse'] ?></td>
                    <td><?= $pers['telephone'] ?></td>
                </tr>
        <?php }} ?>
    </tbody>
</table>

                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
