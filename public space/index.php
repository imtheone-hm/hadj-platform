<?php
require_once __DIR__ . '/../php/functions.php';
$flash = get_flash();
$user = get_current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hajj Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="results.php">Résultats</a></li>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 1): ?>
                        <li><a href="../admin-space/index.php">Admin</a></li>
                    <?php else: ?>
                        <li><a href="../user-space/index.php">Mon espace</a></li>
                    <?php endif; ?>
                    <li><a href="../php/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="signup.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Bienvenue sur Hajj Platform</h1>
            <p>Simulation de plateforme de tirage Hajj sans base de données.</p>
            <?php if ($user): ?>
                <p>Bonjour <?= safe($user['prenom'] . ' ' . $user['nom']); ?>, vous êtes connecté(e).</p>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?= safe($flash['message']); ?>
                </div>
            <?php endif; ?>

            <section class="card">
                <h2>Projet d'évaluation</h2>
                <ul>
                    <li>Traitement des formulaires en PHP</li>
                    <li>Gestion des sessions</li>
                    <li>Logique métier simulée sans MySQL</li>
                    <li>Validation serveur avec regex</li>
                    <li>Gestion des pages protégées et erreurs</li>
                </ul>
            </section>

            <section class="card">
                <h2>Accès rapide</h2>
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <a class="btn btn-primary" href="signup.php">Inscription</a>
                    <a class="btn btn-secondary" href="login.php">Connexion</a>
                    <a class="btn btn-secondary" href="results.php">Résultats</a>
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
