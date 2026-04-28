<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(1);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_nin'], $_POST['action'])) {
    $target = trim($_POST['target_nin']);
    $action = $_POST['action'];
    if ($action === 'validate') {
        if (change_user_status($target, 1)) {
            set_flash('success', 'Utilisateur validé.');
        } else {
            $errors[] = 'Impossible de valider l’utilisateur.';
        }
    } elseif ($action === 'block') {
        if (change_user_status($target, 2)) {
            set_flash('success', 'Utilisateur bloqué.');
        } else {
            $errors[] = 'Impossible de bloquer l’utilisateur.';
        }
    } elseif ($action === 'delete') {
        if (delete_user($target)) {
            set_flash('success', 'Utilisateur supprimé.');
        } else {
            $errors[] = 'Impossible de supprimer l’utilisateur.';
        }
    }
    redirect('manage-users.php');
}
$flash = get_flash();
$filters = [
    'nin' => trim($_GET['searchNIN'] ?? ''),
    'email' => trim($_GET['searchEmail'] ?? ''),
];
$users = get_users($filters);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les utilisateurs - Admin Hajj Platform</title>
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
            <h1>Gérer les utilisateurs</h1>
            <p>Rechercher, valider, bloquer ou supprimer des comptes.</p>
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
            <div class="card">
                <h2>Recherche</h2>
                <form method="get" class="form-row">
                    <div class="form-group">
                        <label for="searchNIN">Recherche par NIN</label>
                        <input id="searchNIN" name="searchNIN" value="<?= safe($filters['nin']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="searchEmail">Recherche par email</label>
                        <input id="searchEmail" name="searchEmail" value="<?= safe($filters['email']); ?>">
                    </div>
                    <div class="form-group" style="align-self:flex-end;">
                        <button class="btn btn-primary" type="submit">Rechercher</button>
                    </div>
                </form>
            </div>
            <div class="card" style="margin-top:1.5rem; overflow-x:auto;">
                <h2>Liste des utilisateurs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>NIN</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><code><?= safe($u['nin']); ?></code></td>
                                <td><?= safe($u['prenom'] . ' ' . $u['nom']); ?></td>
                                <td><?= safe($u['email']); ?></td>
                                <td>
                                    <?php
                                    $states = [1 => 'Actif', 2 => 'Bloqué', 3 => 'En attente', 4 => 'Supprimé'];
                                    echo $states[$u['etat_compte']] ?? 'Inconnu';
                                    ?>
                                </td>
                                <td style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                    <?php if ($u['etat_compte'] === 3): ?>
                                        <form method="post" style="margin:0;">
                                            <input type="hidden" name="target_nin" value="<?= safe($u['nin']); ?>">
                                            <input type="hidden" name="action" value="validate">
                                            <button class="btn btn-primary">Valider</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="target_nin" value="<?= safe($u['nin']); ?>">
                                        <input type="hidden" name="action" value="block">
                                        <button class="btn btn-secondary">Bloquer</button>
                                    </form>
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="target_nin" value="<?= safe($u['nin']); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-danger" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</button>
                                    </form>
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
