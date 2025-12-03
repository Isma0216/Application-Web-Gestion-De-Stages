<?php
// debug_auth.php — fichier temporaire pour diagnostiquer la connexion

// 1) démarrage session
if (session_status() == PHP_SESSION_NONE) session_start();

// 2) inclure conf (assure-toi que le chemin est correct)
include "_conf.php";

// 3) connexion BDD (afficher erreur si pb)
$bdd = @mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
$mysqli_connected = $bdd && !mysqli_connect_errno();

header('Content-Type: text/plain; charset=utf-8');

// Affichage de base
echo "=== DEBUG AUTH START ===\n\n";

// 4) Afficher POST
echo "--- \$_POST ---\n";
print_r($_POST);

// 5) Afficher SESSION
echo "\n--- \$_SESSION ---\n";
print_r($_SESSION);

// 6) Afficher cookies envoyés par le navigateur
echo "\n--- \$_COOKIE ---\n";
print_r($_COOKIE);

// 7) Afficher si connexion MySQL ok
echo "\n--- MySQL connection ---\n";
if ($mysqli_connected) {
    echo "MySQL: connecté OK\n";
    echo "Host info: " . mysqli_get_host_info($bdd) . "\n";
} else {
    echo "MySQL: ERREUR de connexion -> " . mysqli_connect_error() . "\n";
    echo "--> Vérifie tes variables dans _conf.php (serveurBDD, userBDD, mdpBDD, nomBDD)\n";
    exit;
}

// 8) Afficher quelques lignes de la table utilisateur (limité)
echo "\n--- Exemple de lignes dans table `utilisateur` (limit 10) ---\n";
$res = mysqli_query($bdd, "SELECT num, login, email, type, motdepasse FROM utilisateur LIMIT 10");
if (!$res) {
    echo "Erreur requête: " . mysqli_error($bdd) . "\n";
} else {
    while ($r = mysqli_fetch_assoc($res)) {
        // masquer partiellement motdepasse pour sécurité, mais afficher quelques caractères
        $mp = isset($r['motdepasse']) ? substr($r['motdepasse'],0,10).'...' : '(nul)';
        echo "num={$r['num']} login={$r['login']} type={$r['type']} motdepasse={$mp}\n";
    }
}

// 9) Si POST contient login/mdp, tester la requête préparée et afficher tout
if (!empty($_POST['login']) || !empty($_POST['mdp'])) {
    $login_raw = $_POST['login'] ?? '';
    $mdp_raw   = $_POST['mdp'] ?? '';

    echo "\n--- Test d'authentification envoyé ---\n";
    echo "login raw: [" . $login_raw . "]\n";
    echo "mdp raw (affiché entre crochets): [" . $mdp_raw . "]\n";

    $login = trim($login_raw);
    $mdp   = trim($mdp_raw);
    $md5   = md5($mdp);

    echo "login trim: [$login]\n";
    echo "md5(trim(mdp)): $md5\n";

    // Requête préparée
    $sql = "SELECT num, login, type, motdepasse FROM utilisateur WHERE login = ? AND motdepasse = ? LIMIT 1";
    $stmt = mysqli_prepare($bdd, $sql);
    if (!$stmt) {
        echo "Erreur prepare: " . mysqli_error($bdd) . "\n";
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $login, $md5);
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            echo "Erreur execute: " . mysqli_error($bdd) . "\n";
        } else {
            mysqli_stmt_store_result($stmt);
            $num_rows = mysqli_stmt_num_rows($stmt);
            echo "Nombre de lignes retournées par la requête préparée: $num_rows\n";
            if ($num_rows > 0) {
                mysqli_stmt_bind_result($stmt, $num, $loginDB, $typeDB, $mpDB);
                mysqli_stmt_fetch($stmt);
                echo "Ligne trouvée -> num=$num loginDB=$loginDB typeDB=$typeDB motdepasseDB=" . substr($mpDB,0,10) . "...\n";
            } else {
                // Afficher la requête brute pour vérification et la version non préparée (debug only)
                $raw_query = "SELECT * FROM utilisateur WHERE login='" . mysqli_real_escape_string($bdd,$login) .
                             "' AND motdepasse='" . mysqli_real_escape_string($bdd,$md5) . "'";
                echo "Aucune ligne trouvée. Requête brute (debug) :\n$raw_query\n";
                $res2 = mysqli_query($bdd, $raw_query);
                if ($res2 === false) {
                    echo "Erreur sur requête brute: " . mysqli_error($bdd) . "\n";
                } else {
                    echo "Nombre de lignes (requête brute) : " . mysqli_num_rows($res2) . "\n";
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// 10) Vérifier header/session cookie (PHPSESSID)
echo "\n--- Vérifier cookie session (PHPSESSID) ---\n";
if (isset($_COOKIE[session_name()])) {
    echo "Cookie session présent: " . session_name() . " = " . $_COOKIE[session_name()] . "\n";
} else {
    echo "Cookie session absent ! Le navigateur n'envoie pas le cookie de session.\n";
    echo "-> Vérifie que les cookies ne sont pas bloqués et que tu utilises le même domaine/port (localhost vs 127.0.0.1).\n";
}

echo "\n=== DEBUG AUTH END ===\n";
