<?php
session_start();
include '_conf.php';

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) die("Erreur de connexion");

// Déconnexion
if(isset($_POST['byebye'])){
    session_destroy();
    header("Location: index.php");
    exit;
}

// Vérification session
if(!isset($_SESSION['id'])){
    die("Veuillez vous connecter pour voir les comptes rendus.");
}

$id = $_SESSION['id'];
$type = $_SESSION['type'];
$error = $message = "";

// Suppression du compte rendu
if(isset($_POST['supprimer']) && isset($_POST['cr_id'])){
    $cr_id = intval($_POST['cr_id']);
    if($type == 1 || $id == $_POST['num_utilisateur']){
        $delete_sql = "DELETE FROM cr WHERE num=$cr_id";
        mysqli_query($bdd, $delete_sql);
        $message = "Compte rendu supprimé avec succès.";
    } else {
        $error = "Vous n'avez pas les droits pour supprimer ce compte rendu.";
    }
}

// Recherche
$search = "";
if(isset($_GET['search'])){
    $search = preg_replace("/[^a-zA-ZÀ-ÿ\s]/u", "", $_GET['search']);
}

// Tri dynamique
$tri_options = ['date_desc','date_asc','nom_asc','nom_desc','vu','non_vu'];
$tri = $_GET['tri'] ?? 'date_desc';
if(!in_array($tri, $tri_options)) $tri = 'date_desc';

$order_sql = " ORDER BY date DESC";
switch($tri){
    case 'date_asc': $order_sql = " ORDER BY date ASC"; break;
    case 'nom_asc': $order_sql = " ORDER BY u.nom ASC, u.prenom ASC"; break;
    case 'nom_desc': $order_sql = " ORDER BY u.nom DESC, u.prenom DESC"; break;
    case 'vu': $order_sql = " ORDER BY vu DESC"; break;
    case 'non_vu': $order_sql = " ORDER BY vu ASC"; break;
}

// Requête selon type
if ($type == 0) { 
    $sql = "SELECT * FROM cr WHERE num_utilisateur = $id" . $order_sql;
} else { 
    $sql = "SELECT cr.*, u.nom, u.prenom FROM cr 
            INNER JOIN utilisateur u ON cr.num_utilisateur = u.num 
            WHERE 1";

    if(!empty($search)){
        $sql .= " AND (u.nom LIKE '%$search%' OR u.prenom LIKE '%$search%')";
    }

    $sql .= $order_sql;
}

$result = mysqli_query($bdd, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des comptes rendus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <!-- Déconnexion -->
    <form method="post" style="text-align:right; margin-bottom:20px;">
        <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
    </form>

    <h2>Liste des comptes rendus</h2>

    <?php if($error) echo '<div class="error">'.$error.'</div>'; ?>
    <?php if($message) echo '<div class="success">'.$message.'</div>'; ?>

    <!-- Recherche et tri (uniquement pour prof/admin) -->
    <?php if($type != 0): ?>
    <form method="get" style="margin-bottom:20px;">
        <input type="text" name="search" placeholder="Rechercher par nom ou prénom" value="<?= htmlspecialchars($search) ?>">
        <select name="tri">
            <option value="date_desc" <?= $tri=='date_desc'?'selected':'' ?>>Date décroissante</option>
            <option value="date_asc" <?= $tri=='date_asc'?'selected':'' ?>>Date croissante</option>
            <option value="nom_asc" <?= $tri=='nom_asc'?'selected':'' ?>>Nom A→Z</option>
            <option value="nom_desc" <?= $tri=='nom_desc'?'selected':'' ?>>Nom Z→A</option>
            <option value="vu" <?= $tri=='vu'?'selected':'' ?>>Vu</option>
            <option value="non_vu" <?= $tri=='non_vu'?'selected':'' ?>>Non Vu</option>
        </select>
        <button type="submit" class="btn-small">Filtrer</button>
    </form>
    <?php endif; ?>

    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <div class="cr-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="cr-card <?= $row['vu'] ? 'vu' : 'non-vu'; ?>">
                    <div class="cr-header">
                        <?php if($type != 0): ?>
                            <strong><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></strong>
                        <?php endif; ?>
                        <span class="cr-date"><?= $row['date'] ?></span>
                    </div>
                    <p class="cr-description"><?= htmlspecialchars($row['description']) ?></p>
                    <div class="cr-status">
                        Statut : <span><?= $row['vu'] ? '✅ Vu' : '❌ Non Vu'; ?></span>
                    </div>
                    <a href="commentaire.php?cr=<?= $row['num'] ?>" class="btn-small">Voir / Ajouter commentaire</a>

                    <?php if($row['num_utilisateur'] == $id): ?>
                        <a href="modifier_compte_rendu.php?cr=<?= $row['num'] ?>" class="btn-small btn-edit">Modifier</a>
                        <form method="post" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte rendu ?');">
                            <input type="hidden" name="cr_id" value="<?= $row['num'] ?>">
                            <input type="hidden" name="num_utilisateur" value="<?= $row['num_utilisateur'] ?>">
                            <button type="submit" name="supprimer" class="btn-small btn-delete">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="info">Aucun compte rendu trouvé.</div>
    <?php endif; ?>

    <a href="creer_compte_rendu.php" class="btn">Créer un compte rendu</a>
    <a href="accueuil.php" class="btn-secondary">Retour accueil</a>

</div>
</body>
</html>
