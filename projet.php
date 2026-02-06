<?php
$taches=[];
//lire le fichier json
$jsonFileContent= file_get_contents("tache.json");
//mettre le contenu du fichier json dans le tableau $taches
$taches= json_decode($jsonFileContent,true); 
//verifier si on a cliqué sur le bouton ajouter
if (isset($_POST['btnAjout'])) {
    //recuperation des valeurs des champs du formulaire
    $titre= $_POST['titre'];
    $description= $_POST['description'];
    $priorite= $_POST['priorite'];
    $date_limite= $_POST['date_limite'];

    //créer un tableau associatif pour une seule tache
    $tache=[
        "id" => uniqid(), // Générer un identifiant unique pour chaque tâche
        "titre"=> $titre,
        "description"=> $description,
        "priorite"=> $priorite,
        "statut"=> "à faire",
        "date_creation"=> date("Y-m-d"),
        "date_limite"=> $date_limite
    ];
    $taches[]= $tache;
    // Reconvertir le tableau en json
    $nouveauJson = json_encode($taches, JSON_PRETTY_PRINT);
    file_put_contents("tache.json", $nouveauJson);
}

//Suppression dans le tableau
    if (isset($_GET['indice'])) {
        $indice = $_GET['indice'];
    $newtab= array_splice($taches, $indice, 1); //permet de supprimmer un élement au niveau de l'indice spécifié et de rétourner le nouveua tableau
    
        $nouveauJson = json_encode($taches, JSON_PRETTY_PRINT);
        file_put_contents("tache.json", $nouveauJson);

        //Redirection vers la page
        header("Location: projet1.php");
        exit;
}
// Changer le statut de la tâche
if (isset($_GET['action']) && $_GET['action'] === 'changerStatut') {
    $id = $_GET['id'];
    foreach ($taches as &$tache) {
        if ($tache['id'] === $id) {
            if ($tache['statut'] === 'à faire') {
                $tache['statut'] = 'en cours';
            } elseif ($tache['statut'] === 'en cours') {
                $tache['statut'] = 'terminée';
            }
            elseif ($tache['statut'] === 'terminée') {
                $tache['statut'] = 'à faire';
            }
            break;
        } 
    }
    file_put_contents("tache.json", json_encode($taches, JSON_PRETTY_PRINT));
}


// Récupérer les paramètres GET envoyés par le formulaire
$mot = $_GET['mot'] ?? "";
$statut = $_GET['statut'] ?? "";
$priorite = $_GET['priorite'] ?? "";

// Fonction de  recherche et de filtrage des tâches
function filtrerTaches($taches, $mot = "", $statut = "", $priorite = "") {
    $resultats = [];
    foreach ($taches as $tache) {
        $okMot = empty($mot) || stripos($tache['titre'], $mot) !== false || stripos($tache['description'], $mot) !== false;
        $okStatut = empty($statut) || trim($tache['statut']) === trim($statut);
        $okPriorite = empty($priorite) || trim($tache['priorite']) === trim($priorite);

        if ($okMot && $okStatut && $okPriorite) {
            $resultats[] = $tache;
        }
    }
    return $resultats;
}

// Appliquer le filtre
$resultats = filtrerTaches($taches, $mot, $statut, $priorite);
// gestion des taches en retard
function tachesEnRetard($taches) {
    $enRetard = [];
    $aujourdhui = date("Y-m-d"); // date du jour au format AAAA-MM-JJ

    foreach ($taches as $tache) {
        // Vérifier si la tâche n'est pas terminée
        if ($tache['statut'] !== "terminée") {
            // Comparer la date limite avec la date du jour
            if (!empty($tache['date_limite']) && $tache['date_limite'] < $aujourdhui) {
                $enRetard[] = $tache;
            }
        }
    }
    return $enRetard;
}
$enRetard = tachesEnRetard($taches);

if (!empty($enRetard)) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Attention !</strong> Certaines tâches sont en retard :<br>";
    foreach ($enRetard as $tache) {
        echo "- " . htmlspecialchars($tache['titre']) . " (date limite : " . $tache['date_limite'] . ")<br>";
    }
    echo "</div>";
}
// Statistiques
$total = count($taches);
$terminees = 0;

foreach ($taches as $tache) {
    if ($tache['statut'] === "terminée") {
        $terminees++;
    }
}

$pourcentage = $total > 0 ? round(($terminees / $total) * 100, 2) : 0;
$enRetard = count(tachesEnRetard($taches));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une tâche</title>
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>

<div class="card col-6 offset-3 mt-5">
    <div class="card-header h2 ">Ajouter une tâche</div>
    <div class="card-body">

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input  type="text" name="titre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required></textarea>

            </div>

            <div class="mb-3">
                <label class="form-label">Priorité</label>
                <select name="priorite" class="form-select">
                    <option value="basse" >Basse</option>
                    <option value ="moyenne" >Moyenne</option>
                    <option value="haute" >Haute</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Date limite</label>
                <input   type="date" name="date_limite" class="form-control" required>
            </div>

            <button type="submit" name="btnAjout" class="btn btn-primary">
                Ajouter la tâche
            </button>
           

           
        </form>

    </div>
</div>

<h1 class="text-center mt-5">Liste des tâches</h1>
<?php foreach ($taches as $key => $tache) : ?>
    <div class="card col-6 offset-3 mt-3">
        <div class="card-header">
            <h2><?= htmlspecialchars($tache['titre']) ?></h2>
        </div>
        <div class="card-body">
            <p><strong>Description:</strong> <?= htmlspecialchars($tache['description']) ?></p>
            <p><strong>Priorité:</strong> <?= htmlspecialchars($tache['priorite']) ?></p>
            
            <p><strong>Statut:</strong> 
                <?php 
                if ($tache['statut'] == 'à faire') {
                    echo '<span class="badge bg-warning text-dark">À faire</span>';
                } elseif ($tache['statut'] == 'en cours') {
                    echo '<span class="badge bg-primary">En cours</span>';
                } else {
                    echo '<span class="badge bg-success">Terminé</span>';
                }
                ?>
            <p><strong>Date de création:</strong> <?= htmlspecialchars($tache['date_creation']) ?></p>
            <p><strong>Date limite:</strong> <?= htmlspecialchars($tache['date_limite']) ?></p>
    <a onclick="return confirm('voulez vous supprimmer cette tache ?')" class="btn btn-danger" href="?indice=<?= $key ?>">Supprimer</a>
    <a class="btn btn-info" href="?action=changerStatut&id=<?= $tache['id'] ?>">Changer de statut</a>      
        </div>
    </div>
<?php endforeach;?>



  <h1 class="mb-4">Rechercher une tache</h1>

  <!-- Formulaire de recherche et filtres -->
  <form class="row g-3 mb-3" method="get" action="projet1.php">
    <!-- Recherche -->
    <div class="col-md-4">
      <input type="search" class="form-control" name="mot" placeholder="Mot-clé..." value="<?= htmlspecialchars($mot) ?>">
    </div>

    <!-- Filtre statut -->
    <div class="col-md-3">
      <select class="form-select" name="statut">
        <option value="">-- Statut --</option>
        <option value="à faire" <?= $statut=="à faire"?"selected":"" ?>>À faire</option>
        <option value="en cours" <?= $statut=="en cours"?"selected":"" ?>>En cours</option>
        <option value="terminée" <?= $statut=="terminée"?"selected":"" ?>>Terminée</option>
      </select>
    </div>

    <!-- Filtre priorité -->
    <div class="col-md-3">
      <select class="form-select" name="priorite">
        <option value="">-- Priorité --</option>
        <option value="basse" <?= $priorite=="basse"?"selected":"" ?>>Basse</option>
        <option value="moyenne" <?= $priorite=="moyenne"?"selected":"" ?>>Moyenne</option>
        <option value="haute" <?= $priorite=="haute"?"selected":"" ?>>Haute</option>
      </select>
    </div>

    <!-- Bouton -->
    <div class="col-md-2">
      <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-search"></i> Filtrer
      </button>
    </div>
  </form>

  <!-- Résultats -->
  <?php if (empty($resultats)): ?>
    <div class="alert alert-warning">Aucune tâche trouvée.</div>
  <?php else: ?>
    <?php foreach ($resultats as $key => $tache): ?>
      <div class="card mb-2">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($tache['titre']) ?></h5>
          <p class="card-text"><?= htmlspecialchars($tache['description']) ?></p>
          <span class="badge bg-info"><?= $tache['statut'] ?></span>
          <span class="badge bg-warning"><?= $tache['priorite'] ?></span>
          <small class="text-muted">Date limite : <?= $tache['date_limite'] ?? "N/A" ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <!-- Section Statistiques -->
<div class="card mt-4">
  <div class="card-header bg-primary text-white">
    Statistiques
  </div>
  <div class="card-body">
    <ul class="list-group">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Nombre total de tâches
        <span class="badge bg-secondary"><?= $total ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Nombre de tâches terminées
        <span class="badge bg-success"><?= $terminees ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Pourcentage de tâches terminées
        <span class="badge bg-info"><?= $pourcentage ?>%</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Nombre de tâches en retard
        <span class="badge bg-danger"><?= $enRetard ?></span>
      </li>
    </ul>
  </div>
</div>

</body>
</html>
