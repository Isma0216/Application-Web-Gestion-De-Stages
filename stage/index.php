<?php
session_start();
include "_conf.php";

// Connexion à la base de données
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) die("Erreur de connexion MySQL : " . mysqli_connect_error());

$error = "";

// Déconnexion si demandé
if (isset($_POST['byebye'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Traitement du formulaire de connexion
if (isset($_POST['send_con'])) {
    $login = trim($_POST['login']);
    $mdp = trim($_POST['mdp']);
    $mdpHash = md5($mdp);

    if ($login === "" || $mdp === "") {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Requête sécurisée pour éviter injection SQL
        $sql = "SELECT num, login, type FROM utilisateur WHERE login=? AND motdepasse=? LIMIT 1";
        $stmt = mysqli_prepare($bdd, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $login, $mdpHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $num, $loginBDD, $typeBDD);
            mysqli_stmt_fetch($stmt);

            // Stockage des infos dans la session
            $_SESSION['id'] = $num;
            $_SESSION['login'] = $loginBDD;
            $_SESSION['type'] = $typeBDD;

            // Redirection vers l'accueil
            header("Location: accueuil.php");
            exit;
        } else {
            $error = "Login ou mot de passe incorrect.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Connexion</h1>

    <?php if (!empty($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Login :</label>
        <input type="text" name="login" required>

        <label>Mot de passe :</label>
        <input type="password" name="mdp" required>

        <input type="submit" name="send_con" value="Se connecter">
    </form>
</div>
</body>
</html>
