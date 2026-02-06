<?php

// FONCTION

function lireTaches() {
    if (!file_exists('tache.json')) {
        return [];
    }
    
    $contenu = file_get_contents('tache.json');
    $taches = json_decode($contenu, true);
    
    return $taches ?? [];
}

function sauvegarderTaches($taches) {
    $json = json_encode($taches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('tache.json', $json);
}

function genererID() {
    return uniqid('tache_', true);
}

function ajouterTache($titre, $description, $priorite, $dateLimite) {
    $taches = lireTaches();
    
    $nouvelleTache = [
        'id' => genererID(),
        'titre' => $titre,
        'description' => $description,
        'priorite' => $priorite,
        'statut' => 'à faire',
        'date_creation' => date('Y-m-d H:i:s'),
        'date_limite' => $dateLimite
    ];
    
    $taches[] = $nouvelleTache;
    sauvegarderTaches($taches);
}

function changerStatut($id) {
    $taches = lireTaches();
    
    foreach ($taches as $index => $tache) {
        if ($tache['id'] === $id) {
            if ($tache['statut'] === 'à faire') {
                $taches[$index]['statut'] = 'en cours';
            } elseif ($tache['statut'] === 'en cours') {
                $taches[$index]['statut'] = 'terminée';
            }
            break;
        }
    }
    
    sauvegarderTaches($taches);
}

function supprimerTache($id) {
    $taches = lireTaches();
    
    $taches = array_filter($taches, function($tache) use ($id) {
        return $tache['id'] !== $id;
    });
    
    $taches = array_values($taches);
    sauvegarderTaches($taches);
}

function estEnRetard($tache) {
    if ($tache['statut'] === 'terminée') {
        return false;
    }
    
    $dateAujourdhui = date('Y-m-d');
    return $tache['date_limite'] < $dateAujourdhui;
}

function calculerStatistiques($taches) {
    $total = count($taches);
    $terminees = 0;
    $enRetard = 0;
    
    foreach ($taches as $tache) {
        if ($tache['statut'] === 'terminée') {
            $terminees++;
        }
        if (estEnRetard($tache)) {
            $enRetard++;
        }
    }
    
    $pourcentage = $total > 0 ? round(($terminees / $total) * 100, 1) : 0;
    
    return [
        'total' => $total,
        'terminees' => $terminees,
        'pourcentage' => $pourcentage,
        'en_retard' => $enRetard
    ];
}

// TRAITEMENT DES ACTION

if (isset($_POST['ajouter'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $priorite = $_POST['priorite'];
    $dateLimite = $_POST['date_limite'];
    
    ajouterTache($titre, $description, $priorite, $dateLimite);
    
    header('Location: mini_projet.php');
    exit;
}

if (isset($_GET['changer_statut'])) {
    $id = $_GET['changer_statut'];
    changerStatut($id);
    
    header('Location: mini_projet.php');
    exit;
}

if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    supprimerTache($id);
    
    header('Location: mini_projet.php');
    exit;
}

// FILTRES


$filtreStatut = isset($_GET['statut']) ? $_GET['statut'] : '';
$filtrePriorite = isset($_GET['priorite']) ? $_GET['priorite'] : '';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

$taches = lireTaches();
$tachesFiltrees = $taches;

if ($filtreStatut !== '') {
    $tachesFiltrees = array_filter($tachesFiltrees, function($tache) use ($filtreStatut) {
        return $tache['statut'] === $filtreStatut;
    });
}

if ($filtrePriorite !== '') {
    $tachesFiltrees = array_filter($tachesFiltrees, function($tache) use ($filtrePriorite) {
        return $tache['priorite'] === $filtrePriorite;
    });
}

if ($recherche !== '') {
    $tachesFiltrees = array_filter($tachesFiltrees, function($tache) use ($recherche) {
        $rechercheLower = strtolower($recherche);
        $titreLower = strtolower($tache['titre']);
        $descLower = strtolower($tache['description']);
        
        return strpos($titreLower, $rechercheLower) !== false || 
               strpos($descLower, $rechercheLower) !== false;
    });
}

$stats = calculerStatistiques($taches);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Tâches</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 30px;
        }
        .tache-card {
            transition: transform 0.2s;
        }
        .tache-card:hover {
            transform: translateY(-5px);
        }
        .badge-priorite-haute {
            background-color: #dc3545;
        }
        .badge-priorite-moyenne {
            background-color: #ffc107;
            color: #000;
        }
        .badge-priorite-basse {
            background-color: #28a745;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .alerte-retard {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <!-- TITRE -->
            <h1 class="text-center mb-4">
                <i class="bi bi-clipboard-check"></i> Gestionnaire de Tâches
            </h1>
            
            <!-- STATISTIQUES -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-bar-chart-fill"></i> Statistiques
                    </h5>
                    <div class="row text-center mt-3">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h2 class="text-primary mb-0"><?php echo $stats['total']; ?></h2>
                                <small class="text-muted">Total de tâches</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h2 class="text-success mb-0"><?php echo $stats['terminees']; ?></h2>
                                <small class="text-muted">Tâches terminées</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h2 class="text-info mb-0"><?php echo $stats['pourcentage']; ?>%</h2>
                                <small class="text-muted">Pourcentage</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h2 class="text-danger mb-0"><?php echo $stats['en_retard']; ?></h2>
                                <small class="text-muted">En retard</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FORMULAIRE D'AJOUT -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-plus-circle-fill"></i> Ajouter une tâche
                    </h5>
                    <form method="POST" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priorité</label>
                                <select name="priorite" class="form-select" required>
                                    <option value="basse">Basse</option>
                                    <option value="moyenne">Moyenne</option>
                                    <option value="haute">Haute</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date limite</label>
                                <input type="date" name="date_limite" class="form-control" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="ajouter" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Ajouter la tâche
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- FILTRES ET RECHERCHE -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-funnel-fill"></i> Recherche et Filtres
                    </h5>
                    <form method="GET" class="row g-2 mt-2">
                        <div class="col-md-4">
                            <input type="text" name="recherche" class="form-control" 
                                   placeholder="Rechercher..." value="<?php echo htmlspecialchars($recherche); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <select name="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="à faire" <?php echo $filtreStatut === 'à faire' ? 'selected' : ''; ?>>À faire</option>
                                <option value="en cours" <?php echo $filtreStatut === 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="terminée" <?php echo $filtreStatut === 'terminée' ? 'selected' : ''; ?>>Terminée</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="priorite" class="form-select">
                                <option value="">Toutes les priorités</option>
                                <option value="basse" <?php echo $filtrePriorite === 'basse' ? 'selected' : ''; ?>>Basse</option>
                                <option value="moyenne" <?php echo $filtrePriorite === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                                <option value="haute" <?php echo $filtrePriorite === 'haute' ? 'selected' : ''; ?>>Haute</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrer
                            </button>
                        </div>
                        
                        <div class="col-12">
                            <a href="mini_projet.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- LISTE DES TÂCHES -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-list-task"></i> Liste des tâches (<?php echo count($tachesFiltrees); ?>)
                    </h5>
                    
                    <?php if (empty($tachesFiltrees)): ?>
                        <div class="alert alert-info mt-3" role="alert">
                            <i class="bi bi-info-circle"></i> Aucune tâche trouvée.
                        </div>
                    <?php else: ?>
                        <div class="row mt-3">
                            <?php foreach ($tachesFiltrees as $tache): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card tache-card h-100 
                                        <?php 
                                        if ($tache['statut'] === 'à faire') echo 'border-warning';
                                        elseif ($tache['statut'] === 'en cours') echo 'border-info';
                                        else echo 'border-success';
                                        ?>
                                        <?php echo estEnRetard($tache) ? 'border-danger' : ''; ?>"
                                        style="border-width: 3px;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($tache['titre']); ?></h6>
                                                <span class="badge badge-priorite-<?php echo $tache['priorite']; ?>">
                                                    <?php echo ucfirst($tache['priorite']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="card-text text-muted small">
                                                <?php echo htmlspecialchars($tache['description']); ?>
                                            </p>
                                            
                                            <div class="mb-2">
                                                <span class="badge 
                                                    <?php 
                                                    if ($tache['statut'] === 'à faire') echo 'bg-warning text-dark';
                                                    elseif ($tache['statut'] === 'en cours') echo 'bg-info';
                                                    else echo 'bg-success';
                                                    ?>">
                                                    <?php echo ucfirst($tache['statut']); ?>
                                                </span>
                                                
                                                <small class="text-muted ms-2">
                                                    <i class="bi bi-calendar-event"></i>
                                                    <?php echo date('d/m/Y', strtotime($tache['date_limite'])); ?>
                                                </small>
                                                
                                                <?php if (estEnRetard($tache)): ?>
                                                    <span class="badge bg-danger alerte-retard ms-2">
                                                        <i class="bi bi-exclamation-triangle"></i> EN RETARD
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="btn-group w-100" role="group">
                                                <?php if ($tache['statut'] !== 'terminée'): ?>
                                                    <a href="?changer_statut=<?php echo $tache['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <?php 
                                                        if ($tache['statut'] === 'à faire') {
                                                            echo '<i class="bi bi-play-fill"></i> Commencer';
                                                        } else {
                                                            echo '<i class="bi bi-check-circle-fill"></i> Terminer';
                                                        }
                                                        ?>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="?supprimer=<?php echo $tache['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Voulez-vous vraiment supprimer cette tâche ?')">
                                                    <i class="bi bi-trash-fill"></i> Supprimer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
