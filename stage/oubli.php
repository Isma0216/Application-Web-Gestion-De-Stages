<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

$mail = new PHPMailer(true);
include '_conf.php';

function generatePassword($length = 12) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers   = '0123456789';
    $special   = '!@#$%^&*()-_=+[]{};:,.<>?';
    $all = $lowercase.$uppercase.$numbers.$special;
    $password  = $lowercase[random_int(0, strlen($lowercase)-1)];
    $password .= $uppercase[random_int(0, strlen($uppercase)-1)];
    $password .= $numbers[random_int(0, strlen($numbers)-1)];
    $password .= $special[random_int(0, strlen($special)-1)];
    for ($i=strlen($password); $i<$length; $i++) $password .= $all[random_int(0, strlen($all)-1)];
    return str_shuffle($password);
}

if (isset($_POST['email'])) {
    $lemail = $_POST['email'];
    $bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    if (!$bdd) die("Erreur connexion BDD : " . mysqli_connect_error());

    $requete = "SELECT * FROM utilisateur WHERE email = '$lemail'";
    $resultat = mysqli_query($bdd, $requete);
    
    if ($ligne = mysqli_fetch_assoc($resultat)) {
        $newmdp = generatePassword(12);
        $mdphashe = md5($newmdp);
        $requete2 = "UPDATE utilisateur SET motdepasse = '$mdphashe' WHERE email = '$lemail'";
        if (!mysqli_query($bdd, $requete2)) {
            $error = "Erreur BDD : " . mysqli_error($bdd);
        } else {
            $success = "Nouveau mot de passe généré et mis à jour.";
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'contact@sioslam.fr';
                $mail->Password   = '&5&Y@*QHb';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                $mail->Port       = 587;
                $mail->setFrom('contact@sioslam.fr', 'CONTACT SIOSLAM');
                $mail->addAddress($lemail);
                $mail->isHTML(true);
                $mail->Subject = 'Mot de passe perdu';
                $mail->Body = "Voici votre nouveau mot de passe : <b>$newmdp</b>";
                $mail->send();
                $success .= " Email envoyé !";
            } catch (Exception $e) {
                $error = "Erreur envoi email : {$mail->ErrorInfo}";
            }
        }
    } else {
        $error = "Aucun utilisateur trouvé avec cet email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Retrouver votre mot de passe</h1>

    <?php if(isset($error)) echo '<div class="error">'.$error.'</div>'; ?>
    <?php if(isset($success)) echo '<div class="success">'.$success.'</div>'; ?>

    <form method="post">
        <label>Saisir votre email :</label>
        <input name="email" type="email" required>
        <input type="submit" value="Confirmer">
    </form>

    <a href="index.php">Retour à la connexion</a>
</div>
</body>
</html>
