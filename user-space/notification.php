<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(2);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_id'])) {
        mark_notification_read((int) $_POST['mark_id']);
        set_flash('success', 'Notification marquée comme lue.');
        redirect('notification.php');
    }
    if (isset($_POST['mark_all'])) {
        foreach (get_user_notifications($user['nin']) as $notification) {
            mark_notification_read($notification['id_notification']);
        }
        set_flash('success', 'Toutes les notifications ont été marquées comme lues.');
        redirect('notification.php');
    }
}
$flash = get_flash();
$notifications = get_user_notifications($user['nin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Hajj Platform</title>
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
            <h1>Notifications</h1>
            <p>Suivez vos messages internes.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?= safe($flash['message']); ?>
                </div>
            <?php endif; ?>
            <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
                <h2>Mes notifications</h2>
                <form method="post">
                    <button class="btn btn-secondary" type="submit" name="mark_all">Tout marquer lu</button>
                </form>
            </div>
            <?php if (empty($notifications)): ?>
                <div class="card"><p>Aucune notification disponible.</p></div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="card" style="margin-bottom:1rem;">
                        <p><?= safe($notification['message']); ?></p>
                        <p><strong>État :</strong> <?= $notification['etat_notification'] === 1 ? 'Non lue' : 'Lue'; ?></p>
                        <?php if ($notification['etat_notification'] === 1): ?>
                            <form method="post">
                                <input type="hidden" name="mark_id" value="<?= $notification['id_notification']; ?>">
                                <button class="btn btn-primary" type="submit">Marquer comme lue</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
