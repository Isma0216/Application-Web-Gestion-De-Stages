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

// Vérifier session
if(!isset($_SESSION['id'])){
    die("Veuillez vous connecter pour créer un compte rendu. <a href='index.php'>Se connecter</a>");
}

$id_user = $_SESSION['id'];
$type_utilisateur = $_SESSION['type']; // 0=élève,1=prof,2=admin

// Soumission
if(isset($_POST['envoyer'])){
    $description = mysqli_real_escape_string($bdd,$_POST['description']);
    $date = $_POST['date'] ?? date("Y-m-d");
    $datetime = date("Y-m-d H:i:s");

    // Vérification que l'élève peut créer un compte rendu
    if($type_utilisateur==0){
        // Vérifier qu'il n'a pas déjà un compte rendu à cette date
        $check_sql = "SELECT * FROM cr WHERE num_utilisateur = $id_user AND date = '$date'";
        $check_result = mysqli_query($bdd, $check_sql);

        if(mysqli_num_rows($check_result) > 0){
            $error = "Vous avez déjà un compte rendu pour cette date. Veuillez choisir une autre date.";
        } else {
            $sql = "INSERT INTO cr (date, description, vu, datetime, num_utilisateur)
                    VALUES ('$date','$description',0,'$datetime',$id_user)";
            if(mysqli_query($bdd,$sql)){
                $success = "Compte rendu créé avec succès !";
            } else {
                $error = "Erreur : ".mysqli_error($bdd);
            }
        }
    } else {
        $error = "Seuls les élèves peuvent créer un compte-rendu.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un compte rendu</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

<form method="post">
    <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
</form>

<h2>Créer un compte rendu</h2>

<?php if(isset($error)) echo '<div class="error">'.$error.'</div>'; ?>
<?php if(isset($success)) echo '<div class="success">'.$success.'</div>'; ?>

<form method="post">
    <label>Date :</label>
    <input type="date" name="date" value="<?= date("Y-m-d") ?>" required>

    <label>Description :</label>
    <textarea name="description" rows="5" placeholder="Entrez votre compte rendu..." required></textarea>

    <input type="submit" name="envoyer" value="Envoyer">
</form>

<a href="liste_compte_rendu.php" class="btn-secondary">Retour liste</a>
</div>
</body>
</html>
