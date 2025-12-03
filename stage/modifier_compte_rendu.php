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

if (!isset($_SESSION['id'])) {
    die("Veuillez vous connecter pour modifier un compte rendu.");
}

$id = $_SESSION['id'];
$type = $_SESSION['type'];

// Vérifier que l'ID du compte rendu est passé en GET
if(!isset($_GET['cr'])) {
    die("Compte rendu non spécifié.");
}

$cr_id = intval($_GET['cr']);

// Récupérer le compte rendu
if($type == 0) { 
    // Utilisateur normal, vérifier que c'est son compte rendu
    $sql = "SELECT * FROM cr WHERE num = $cr_id AND num_utilisateur = $id";
} else {
    // Admin, peut modifier n'importe quel compte rendu
    $sql = "SELECT * FROM cr WHERE num = $cr_id";
}

$result = mysqli_query($bdd, $sql);
if(mysqli_num_rows($result) == 0){
    die("Compte rendu introuvable ou accès refusé.");
}

$cr = mysqli_fetch_assoc($result);

// Traitement du formulaire
if(isset($_POST['modifier'])){
    $description = mysqli_real_escape_string($bdd, $_POST['description']);
    $date = mysqli_real_escape_string($bdd, $_POST['date']);

    // Vérifier qu'aucun autre compte rendu n'existe pour cette date pour cet utilisateur
    $check_sql = "SELECT * FROM cr WHERE num_utilisateur = $id AND date = '$date' AND num != $cr_id";
    $check_result = mysqli_query($bdd, $check_sql);

    if(mysqli_num_rows($check_result) > 0){
        $error = "Vous avez déjà un compte rendu pour cette date. Veuillez choisir une autre date.";
    } else {
        $update_sql = "UPDATE cr SET description='$description', date='$date' WHERE num=$cr_id";
        if(mysqli_query($bdd, $update_sql)){
            $message = "Compte rendu modifié avec succès.";
            // Recharger les nouvelles valeurs
            $cr['description'] = $description;
            $cr['date'] = $date;
        } else {
            $error = "Erreur lors de la modification : ".mysqli_error($bdd);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le compte rendu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <form method="post">
        <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
    </form>

    <h2>Modifier le compte rendu</h2>

    <?php if(isset($error)) echo '<div class="error">'.$error.'</div>'; ?>
    <?php if(isset($message)) echo '<div class="success">'.$message.'</div>'; ?>

    <form method="post" class="form-cr">
        <label for="description">Description :</label><br>
        <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($cr['description']); ?></textarea><br><br>

        <label for="date">Date :</label><br>
        <input type="date" name="date" id="date" value="<?php echo $cr['date']; ?>" required><br><br>

        <button type="submit" name="modifier" class="btn">Modifier</button>
        <a href="liste_compte_rendu.php" class="btn-secondary">Retour liste</a>
    </form>

</div>
</body>
</html>
