<?php 
include '_conf.php';

if($bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD))
{
    echo"connexion BDD réussie !";
}
else
{
    echo"Erreur";
}



?>

<!DOCTYPE html>
<h1>Page de connexion</h1>
<form action="_conf.php" method="post">
   <label>Login :</label>
   <input name="logine" id="logine" type="text" />

   <label>Mot de passe :</label>
   <input name="mdp" id="mdp" type="text" />

   <button type="submit">Confirmer</button>

   <a href="oubli.php">Mdp oublié</a>
</form>
</html>


