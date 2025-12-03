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

// Vérification si admin
if(!isset($_SESSION['id']) || $_SESSION['type'] != 2){
    die("Accès interdit. Seul l'admin peut accéder à cette page.");
}

// Ajouter un utilisateur
if(isset($_POST['add_user'])){
    $login = mysqli_real_escape_string($bdd, $_POST['login']);
    $mdp = mysqli_real_escape_string($bdd, $_POST['mdp']);
    $email = mysqli_real_escape_string($bdd, $_POST['email']);
    $type = intval($_POST['type']);

    // Hash du mot de passe (md5 pour l'instant comme le reste)
    $mdphash = md5($mdp);

    $sql = "INSERT INTO utilisateur (login, motdepasse, email, type) 
            VALUES ('$login', '$mdphash', '$email', $type)";

    if(mysqli_query($bdd, $sql)){
        $success = "Utilisateur ajouté avec succès !";
    } else {
        $error = "Erreur ajout utilisateur : " . mysqli_error($bdd);
    }
}

// Supprimer un utilisateur
if(isset($_GET['delete'])){
    $num = intval($_GET['delete']);
    $sql = "DELETE FROM utilisateur WHERE num = $num";

    if(mysqli_query($bdd, $sql)){
        $success = "Utilisateur supprimé avec succès !";
    } else {
        $error = "Erreur suppression : " . mysqli_error($bdd);
    }
}

// Récupérer tous les utilisateurs
$result_users = mysqli_query($bdd, "SELECT * FROM utilisateur ORDER BY login ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <form method="post">
        <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
    </form>

    <h2>Gestion des utilisateurs</h2>

    <?php if(isset($error)) echo '<div class="error">'.$error.'</div>'; ?>
    <?php if(isset($success)) echo '<div class="success">'.$success.'</div>'; ?>

    <h3>Ajouter un utilisateur</h3>
    <form method="post" class="user-form">
        <input type="text" name="login" placeholder="Login" required>
        <input type="password" name="mdp" placeholder="Mot de passe" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="type" required>
            <option value="0">Élève</option>
            <option value="1">Professeur</option>
            <option value="2">Admin</option>
        </select>
        <input type="submit" name="add_user" value="Ajouter">
    </form>

    <h3>Liste des utilisateurs</h3>
    <table class="user-table">
        <tr>
            <th>Num</th>
            <th>Login</th>
            <th>Email</th>
            <th>Type</th>
            <th>Action</th>
        </tr>
        <?php while($user = mysqli_fetch_assoc($result_users)): ?>
        <tr>
            <td><?php echo $user['num']; ?></td>
            <td><?php echo $user['login']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td>
                <?php 
                    echo $user['type']==0?'Élève':($user['type']==1?'Prof':'Admin'); 
                ?>
            </td>
            <td>
                <?php if($user['num'] != $_SESSION['id']): // Ne pas permettre à l’admin de se supprimer ?>
                <a href="?delete=<?php echo $user['num']; ?>" class="btn-small delete-btn" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                <?php else: ?>
                -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="accueuil.php" class="btn-secondary">Retour accueil</a>
</div>
</body>
</html>
