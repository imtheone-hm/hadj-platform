<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(2);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_id'])) {
    $lotteryId = (int) $_POST['join_id'];
    if (join_lottery($user['nin'], $lotteryId, $errors)) {
        set_flash('success', 'Votre inscription a bien été enregistrée.');
        redirect('join-lottery.php');
    }
}
$flash = get_flash();
$lotteries = get_all_lotteries();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre un tirage - Hajj Platform</title>
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
            <h1>Rejoindre un tirage</h1>
            <p>Inscrivez-vous aux tirages ouverts.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?= safe($flash['message']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= safe($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="grid">
                <?php foreach ($lotteries as $lottery): ?>
                    <?php $joined = is_user_registered($user['nin'], $lottery['id_tirage']); ?>
                    <div class="card">
                        <h3>Tirage #<?= $lottery['id_tirage']; ?></h3>
                        <p><strong>Date du tirage :</strong> <?= safe($lottery['date_tirage']); ?></p>
                        <p><strong>Inscriptions :</strong> <?= safe($lottery['date_ouverture_insc']); ?> → <?= safe($lottery['date_cloture_insc']); ?></p>
                        <p><strong>Participants :</strong> <?= get_registrations_count($lottery['id_tirage']); ?></p>
                        <p><strong>Statut :</strong> <?= $joined ? '<span class="badge badge-success">Inscrit</span>' : '<span class="badge badge-secondary">Non inscrit</span>'; ?></p>
                        <?php if ($lottery['etat_tirage'] === 3 && !$joined): ?>
                            <form method="post">
                                <input type="hidden" name="join_id" value="<?= $lottery['id_tirage']; ?>">
                                <button class="btn btn-primary" type="submit">M'inscrire</button>
                            </form>
                        <?php elseif ($joined): ?>
                            <button class="btn btn-secondary" disabled>Déjà inscrit</button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Non disponible</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
