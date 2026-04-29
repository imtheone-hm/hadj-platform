<?php
require_once __DIR__ . '/functions.php';
$message = safe($_GET['msg'] ?? 'Une erreur est survenue.');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - Hajj Platform</title>
    <link rel="stylesheet" href="../public%20space/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="../public%20space/index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="../public%20space/index.php">Accueil</a></li>
                <li><a href="../public%20space/login.php">Connexion</a></li>
                <li><a href="../public%20space/signup.php">Inscription</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Erreur</h1>
            <p>Accès ou action impossible.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="alert alert-error">
                <p><?= $message; ?></p>
            </div>
            <a class="btn btn-primary" href="../public%20space/index.php">Retour à l'accueil</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
