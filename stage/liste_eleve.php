<?php
session_start();
include "_conf.php";

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// Vérification si l'utilisateur est professeur ou admin
if ($_SESSION['type'] != 1 && $_SESSION['type'] != 2) {
    die("Accès refusé.");
}

// Connexion BDD
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

// Déconnexion si demandé
if (isset($_POST['byebye'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Gestion de la recherche
$search = "";
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
    // Garder uniquement les lettres et espaces
    $search = preg_replace("/[^a-zA-ZÀ-ÿ\s]/u", "", $search);
}

// Requête SQL pour récupérer les élèves et le nombre de comptes rendus
$sql = "SELECT u.nom, u.prenom, u.email, u.tel, 
               (SELECT COUNT(*) FROM cr WHERE cr.num_utilisateur = u.num) AS nb_cr
        FROM utilisateur u 
        WHERE u.type = 0";

if (!empty($search)) {
    $search_sql = mysqli_real_escape_string($bdd, $search);
    $sql .= " AND (u.nom LIKE '%$search_sql%' OR u.prenom LIKE '%$search_sql%')";
}

$sql .= " ORDER BY u.nom ASC";

$result = mysqli_query($bdd, $sql);
if (!$result) {
    die("Erreur SQL : " . mysqli_error($bdd));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élèves</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <!-- Déconnexion -->
    <form method="post" style="text-align:right; margin-bottom:20px;">
        <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
    </form>

    <h1>Liste des élèves</h1>

    <!-- Formulaire de recherche -->
    <form method="post" style="margin-bottom:20px;">
        <input type="text" name="search" placeholder="Rechercher par nom ou prénom" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-small">Rechercher</button>
    </form>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Nb comptes rendus</th>
                </tr>
            </thead>
            <tbody>
                <?php while($eleve = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($eleve['nom']) ?></td>
                        <td><?= htmlspecialchars($eleve['prenom']) ?></td>
                        <td><?= htmlspecialchars($eleve['email']) ?></td>
                        <td><?= htmlspecialchars($eleve['tel']) ?></td>
                        <td><?= $eleve['nb_cr'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="margin-top:20px;">
            <a href="accueuil.php" class="btn-secondary">Retour accueil</a>
        </div>

    <?php else: ?>
        <p>Aucun élève trouvé.</p>
        <div style="margin-top:20px;">
            <a href="accueuil.php" class="btn-secondary">Retour accueil</a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
