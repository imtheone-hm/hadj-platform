<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(2);
$notifications = get_user_notifications($user['nin']);
$inscriptions = 0;
$wins = 0;
foreach ($_SESSION['data']['inscrits'] as $inscrit) {
    if ($inscrit['nin'] === $user['nin']) {
        $inscriptions++;
    }
}
foreach ($_SESSION['data']['results'] as $result) {
    if ($result['nin'] === $user['nin']) {
        $wins++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord utilisateur - Hajj Platform</title>
    <link rel="stylesheet" href="../public%20space/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="../public%20space/index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="join-lottery.php">Rejoindre un tirage</a></li>
                <li><a href="profile.php">Mon profil</a></li>
                <li><a href="notification.php">Notifications</a></li>
                <li><a href="../php/logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Bonjour, <?= safe($user['prenom'] . ' ' . $user['nom']); ?> !</h1>
            <p>Bienvenue dans votre espace sécurisé.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="grid">
                <div class="card">
                    <h3>Statut du compte</h3>
                    <p><strong>État :</strong>
                        <?php
                        $states = [1 => 'Actif', 2 => 'Bloqué', 3 => 'En attente', 4 => 'Supprimé'];
                        echo $states[$user['etat_compte']] ?? 'Inconnu';
                        ?>
                    </p>
                    <p><strong>Tirages rejoins :</strong> <?= $inscriptions; ?></p>
                    <p><strong>Notifications :</strong> <?= count($notifications); ?></p>
                </div>
                <div class="card">
                    <h3>Prochain tirage</h3>
                    <p><?= $inscriptions > 0 ? 'Vous êtes inscrit à au moins un tirage.' : 'Vous n’êtes pas encore inscrit.'; ?></p>
                    <p><strong>Gains obtenus :</strong> <?= $wins; ?></p>
                </div>
                <div class="card">
                    <h3>Actions rapides</h3>
                    <ul>
                        <li><a href="join-lottery.php">Rejoindre un tirage</a></li>
                        <li><a href="profile.php">Mettre à jour mon profil</a></li>
                        <li><a href="notification.php">Voir mes notifications</a></li>
                    </ul>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
