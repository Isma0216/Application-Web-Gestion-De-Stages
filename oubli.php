<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

$mail = new PHPMailer(true);
?>

<!DOCTYPE html>
<h1>Retrouver MDP</h1>
<form method="post">
    <label>Saisir @ mail:</label>
    <input name="email" type="email" />
    <button type="submit">Confirmer</button>
</form>

<?php 
include '_conf.php';

function generatePassword($length = 12) {
    // Character sets
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers   = '0123456789';
    $special   = '!@#$%^&*()-_=+[]{};:,.<>?';

    // Combine all sets
    $all = $lowercase . $uppercase . $numbers . $special;

    // Ensure password has at least 1 of each type
    $password  = $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];

    // Fill the rest with random characters
    for ($i = strlen($password); $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }   

    // Shuffle to avoid predictable order
    return str_shuffle($password);
}

if (isset($_POST['email'])) {
    $lemail = $_POST['email'];
    echo "Le formulaire a été envoyé avec comme email la valeur : $lemail";

    $bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);

    if (!$bdd) {
        die("Erreur connexion BDD : " . mysqli_connect_error());
    }

    $requete = "SELECT * FROM utilisateur WHERE email = '$lemail'";
    $resultat = mysqli_query($bdd, $requete);
    
    if ($ligne = mysqli_fetch_assoc($resultat)) {
        // Générer le nouveau mdp aléatoire 
        $newmdp = generatePassword(12);
        echo "<br>Nouveau mot de passe généré : $newmdp";

        $mdphashe = md5($newmdp); // ⚠️ À remplacer par password_hash() plus tard !
        $requete2 = "UPDATE utilisateur SET motdepasse = '$mdphashe' WHERE email = '$lemail'";

        if (!mysqli_query($bdd, $requete2)) {
            echo "❌ Khata ! : " . mysqli_error($bdd);
        } else {
            echo "<br>✅ Mot de passe mis à jour en BDD.";
        }

    } else {
        echo "<br>❌ Aucun utilisateur trouvé avec cet email.";
        exit;
    }

    try {
        // Config SMTP Hostinger
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contact@sioslam.fr';  // Ton email Hostinger
        $mail->Password   = '&5&Y@*QHb';            // Ton mot de passe
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        // Expéditeur
        $mail->setFrom('contact@sioslam.fr', 'CONTACT SIOSLAM');
        // Destinataire
        $mail->addAddress($lemail);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Mot de passe perdu';
        $mail->Body = "Voici votre nouveau mot de passe : <b>$newmdp</b>";

        $mail->send();
        echo "<br>✅ Email envoyé avec succès !";
    } catch (Exception $e) {
        echo "<br>❌ Erreur d'envoi : {$mail->ErrorInfo}";
    }
}
?>
