<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(1);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lottery_id'], $_POST['action'])) {
    $lotteryId = (int) $_POST['lottery_id'];
    $action = $_POST['action'];
    if ($action === 'open') {
        set_lottery_state($lotteryId, 3);
        set_flash('success', 'Les inscriptions ont été ouvertes.');
    } elseif ($action === 'close') {
        set_lottery_state($lotteryId, 4);
        set_flash('success', 'Les inscriptions ont été fermées.');
    } elseif ($action === 'draw') {
        if (!draw_winners($lotteryId, $errors)) {
            $_SESSION['lottery_errors'] = $errors;
        } else {
            set_flash('success', 'Le tirage a été effectué.');
        }
    }
    redirect('manage-lotteries.php');
}
$flash = get_flash();
$errors = $_SESSION['lottery_errors'] ?? [];
unset($_SESSION['lottery_errors']);
$lotteries = get_all_lotteries();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les tirages - Admin Hajj Platform</title>
    <link rel="stylesheet" href="../public%20space/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">Hajj Platform</a>
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
            <h1>Gérer les tirages</h1>
            <p>Ouvrez ou fermez les inscriptions et effectuez les tirages.</p>
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
            <div class="card" style="overflow-x:auto;">
                <h2>Liste des tirages</h2>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ouverture</th>
                            <th>Clôture</th>
                            <th>Tirage</th>
                            <th>État</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotteries as $lottery): ?>
                            <tr>
                                <td><?= $lottery['id_tirage']; ?></td>
                                <td><?= safe($lottery['date_ouverture_insc']); ?></td>
                                <td><?= safe($lottery['date_cloture_insc']); ?></td>
                                <td><?= safe($lottery['date_tirage']); ?></td>
                                <td>
                                    <?php
                                    $states = [1 => 'Planifié', 2 => 'Effectué', 3 => 'Inscriptions ouvertes', 4 => 'Inscriptions fermées'];
                                    echo $states[$lottery['etat_tirage']] ?? 'Inconnu';
                                    ?>
                                </td>
                                <td><?= get_registrations_count($lottery['id_tirage']); ?></td>
                                <td style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                    <?php if ($lottery['etat_tirage'] !== 3): ?>
                                        <form method="post" style="margin:0;">
                                            <input type="hidden" name="lottery_id" value="<?= $lottery['id_tirage']; ?>">
                                            <input type="hidden" name="action" value="open">
                                            <button class="btn btn-primary">Ouvrir</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($lottery['etat_tirage'] === 3): ?>
                                        <form method="post" style="margin:0;">
                                            <input type="hidden" name="lottery_id" value="<?= $lottery['id_tirage']; ?>">
                                            <input type="hidden" name="action" value="close">
                                            <button class="btn btn-secondary">Fermer</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($lottery['etat_tirage'] === 4): ?>
                                        <form method="post" style="margin:0;">
                                            <input type="hidden" name="lottery_id" value="<?= $lottery['id_tirage']; ?>">
                                            <input type="hidden" name="action" value="draw">
                                            <button class="btn btn-primary">Effectuer le tirage</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
