<?php
session_start();
include "_conf.php";

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$login_session = htmlspecialchars($_SESSION['login']);
$type = $_SESSION['type'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <!-- Déconnexion -->
    <form method="post" action="index.php" style="text-align:right; margin-bottom:20px;">
        <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
    </form>

    <!-- Message de bienvenue -->
    <h2>Bienvenue <?= $login_session ?> !</h2>

    <!-- Menu selon le type utilisateur -->
    <?php if ($type == 0): ?>
        <p>Vous êtes connecté en tant qu'élève.</p>
        <ul>
            <li><a href="liste_compte_rendu.php">Mes comptes rendus</a></li>
            <li><a href="creer_compte_rendu.php">Créer un compte rendu</a></li>
            <li><a href="commentaire.php">Voir les commentaires</a></li>
        </ul>
    <?php elseif ($type == 1): ?>
        <p>Vous êtes connecté en tant que professeur.</p>
        <ul>
            <li><a href="liste_eleve.php">Liste des élèves</a></li>
            <li><a href="liste_compte_rendu.php">Tous les comptes rendus</a></li>
            <li><a href="commentaire.php">Ajouter ou voir les commentaires</a></li>
        </ul>
    <?php elseif ($type == 2): ?>
        <p>Vous êtes connecté en tant qu'admin.</p>
        <ul>
            <li><a href="admin_utilisateur.php">Gestion des utilisateurs</a></li>
            <li><a href="liste_compte_rendu.php">Tous les comptes rendus</a></li>
            <li><a href="commentaire.php">Ajouter ou voir les commentaires</a></li>
        </ul>
    <?php else: ?>
        <p>Type utilisateur inconnu.</p>
    <?php endif; ?>

</div>
</body>
</html>
