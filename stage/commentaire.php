<?php
session_start();
include '_conf.php';

// Connexion BDD
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) die("Erreur de connexion");

// Déconnexion
if(isset($_POST['byebye'])){
    session_destroy();
    header("Location: index.php");
    exit;
}

// Vérification de session
if (!isset($_SESSION['id'])) {
    die("Veuillez vous connecter pour accéder aux commentaires. <a href='index.php'>Se connecter</a>");
}

$type_utilisateur = $_SESSION['type']; // 0=élève,1=prof,2=admin
$id_user = $_SESSION['id'];

// Ajouter un commentaire (prof ou admin)
if(isset($_POST['envoyer']) && ($type_utilisateur==1 || $type_utilisateur==2)) {
    $texte = mysqli_real_escape_string($bdd, $_POST['texte']);
    $num_cr = intval($_POST['num_cr']);
    $datetime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO commentaire (num_cr,num_utilisateur,texte,datetime)
            VALUES ($num_cr,$id_user,'$texte','$datetime')";
    if(mysqli_query($bdd,$sql)){
        $success = "Commentaire ajouté !";
    } else {
        $error = "Erreur : ".mysqli_error($bdd);
    }
}

// Modifier un commentaire (prof ou admin)
if(isset($_POST['modifier_commentaire']) && ($type_utilisateur==1 || $type_utilisateur==2)){
    $id_commentaire = intval($_POST['id_commentaire']);
    $texte_modifie = mysqli_real_escape_string($bdd, $_POST['texte_modifie']);

    // Vérifier que le commentaire appartient bien à l'utilisateur connecté
    $check = mysqli_query($bdd, "SELECT num_utilisateur FROM commentaire WHERE num=$id_commentaire");
    $row_check = mysqli_fetch_assoc($check);
    if($row_check && $row_check['num_utilisateur']==$id_user){
        $update = mysqli_query($bdd, "UPDATE commentaire SET texte='$texte_modifie', datetime='".date("Y-m-d H:i:s")."' WHERE num=$id_commentaire");
        if($update){
            $success = "Commentaire modifié !";
        } else {
            $error = "Erreur : ".mysqli_error($bdd);
        }
    } else {
        $error = "Vous ne pouvez pas modifier ce commentaire.";
    }
}

// Supprimer un commentaire (prof ou admin)
if(isset($_POST['supprimer_commentaire']) && ($type_utilisateur==1 || $type_utilisateur==2)){
    $id_commentaire = intval($_POST['id_commentaire']);

    // Vérifier que le commentaire appartient bien à l'utilisateur connecté
    $check = mysqli_query($bdd, "SELECT num_utilisateur FROM commentaire WHERE num=$id_commentaire");
    $row_check = mysqli_fetch_assoc($check);
    if($row_check && $row_check['num_utilisateur']==$id_user){
        $delete = mysqli_query($bdd, "DELETE FROM commentaire WHERE num=$id_commentaire");
        if($delete){
            $success = "Commentaire supprimé !";
        } else {
            $error = "Erreur : ".mysqli_error($bdd);
        }
    } else {
        $error = "Vous ne pouvez pas supprimer ce commentaire.";
    }
}

// Récupérer comptes rendus
$result_cr = mysqli_query($bdd,
    "SELECT cr.*, u.nom, u.prenom 
     FROM cr 
     INNER JOIN utilisateur u ON cr.num_utilisateur=u.num
     ORDER BY date DESC");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commentaires</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

<form method="post">
    <button type="submit" name="byebye" class="logout-btn">Déconnexion</button>
</form>

<h2>Commentaires</h2>

<?php if(isset($error)) echo '<div class="error">'.$error.'</div>'; ?>
<?php if(isset($success)) echo '<div class="success">'.$success.'</div>'; ?>

<?php while($row=mysqli_fetch_assoc($result_cr)): 
    $cr_id = intval($row['num']); ?>
<div class="cr-card">
    <div class="cr-header">
        <strong><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></strong>
        <span class="cr-date"><?= $row['date'] ?></span>
    </div>
    <p class="cr-description"><?= nl2br(htmlspecialchars($row['description'])) ?></p>

    <div class="comment-section">
        <h4>Commentaires :</h4>
        <?php
        $res_com = mysqli_query($bdd,
            "SELECT c.*, u.nom, u.prenom 
             FROM commentaire c 
             INNER JOIN utilisateur u ON c.num_utilisateur=u.num 
             WHERE c.num_cr=$cr_id 
             ORDER BY datetime ASC");
        
        if(mysqli_num_rows($res_com)>0){
            while($c=mysqli_fetch_assoc($res_com)){ ?>
                <div class="comment-box">
                    <strong><?= $c['nom'].' '.$c['prenom'] ?> :</strong>
                    <p><?= nl2br(htmlspecialchars($c['texte'])) ?></p>

                    <?php if($c['num_utilisateur']==$id_user && ($type_utilisateur==1 || $type_utilisateur==2)): ?>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="id_commentaire" value="<?= $c['num'] ?>">
                            <textarea name="texte_modifie"><?= htmlspecialchars($c['texte']) ?></textarea>
                            <button type="submit" name="modifier_commentaire">Modifier</button>
                            <button type="submit" name="supprimer_commentaire" onclick="return confirm('Supprimer ce commentaire ?');">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
        <?php } 
        } else {
            echo '<p class="info">Aucun commentaire.</p>';
        } ?>

        <?php if($type_utilisateur==1 || $type_utilisateur==2): ?>
            <form method="post" class="comment-form">
                <input type="hidden" name="num_cr" value="<?= $cr_id ?>">
                <textarea name="texte" rows="2" placeholder="Ajouter un commentaire..." required></textarea>
                <input type="submit" name="envoyer" value="Commenter">
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>

<a href="accueuil.php" class="btn-secondary">Retour accueil</a>
</div>
</body>
</html>
