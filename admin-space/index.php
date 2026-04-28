<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(1);
$pending = user_pending_count();
$openLotteries = 0;
foreach (get_all_lotteries() as $tirage) {
    if ($tirage['etat_tirage'] === 3) {
        $openLotteries++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hajj Platform</title>
    <link rel="stylesheet" href="../public%20space/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="../public%20space/index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage-users.php">Utilisateurs</a></li>
                <li><a href="manage-lotteries.php">Tirages</a></li>
                <li><a href="../php/logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Panel administrateur</h1>
            <p>Bienvenue <?= safe($user['prenom'] . ' ' . $user['nom']); ?>.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="grid">
                <div class="card">
                    <h3>Comptes en attente</h3>
                    <p><?= $pending; ?> utilisateur(s)</p>
                </div>
                <div class="card">
                    <h3>Tirages ouverts</h3>
                    <p><?= $openLotteries; ?> tirage(s)</p>
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
