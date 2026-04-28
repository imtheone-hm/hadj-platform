<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $target_nin = $_POST['target_nin'] ?? '';
    if ($_POST['action'] === 'validate') {
        $stmt = $pdo->prepare("UPDATE User SET etat_compte = 1 WHERE nin = :nin");
        $stmt->execute(['nin' => $target_nin]);
        $message = "Le compte a été validé avec succès.";
    } elseif ($_POST['action'] === 'block') {
        $stmt = $pdo->prepare("UPDATE User SET etat_compte = 2 WHERE nin = :nin");
        $stmt->execute(['nin' => $target_nin]);
        $message = "Le compte a été bloqué.";
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM User WHERE nin = :nin");
        $stmt->execute(['nin' => $target_nin]);
        $message = "Le compte a été supprimé.";
    }
}

// Fetch Real Users
$usersStmt = $pdo->query("SELECT * FROM User ORDER BY etat_compte DESC, nom ASC");
$users = $usersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Hajj Platform Admin</title>
    <link rel="stylesheet" href="../public space/styles.css">
    <script src="../validation.js" defer></script>
</head>
<body>

    <nav>
        <div class="container">
            <a href="../public space/index.php" class="logo"> Hajj Platform</a>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage-lotteries.php">Manage Lotteries</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="../public space/index.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Manage Users</h1>
            <p>Validate, block or delete user accounts</p>
        </div>
    </header>

    <main>
        <div class="container">

            <?php if (!empty($message)): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align:center;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-top:2rem;">
                <h2>All Users Registered</h2>
                <table>
                    <thead>
                        <tr>
                            <th>NIN</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status & Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr><td colspan="5" style="text-align:center;">Aucun utilisateur trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($u["nin"]); ?></code></td>
                                <td><?php echo htmlspecialchars($u["nom"] . " " . $u["prenom"]); ?></td>
                                <td><?php echo htmlspecialchars($u["email"]); ?></td>
                                <td>
                                    <?php 
                                        $etat = $u["etat_compte"];
                                        $role = ($u["role"] == 1) ? "<strong style='color:blue;'>(Admin)</strong>" : "(User)";
                                        if ($etat == 1)      echo "<span style='background:green;color:white;padding:3px 6px;border-radius:4px;'>Actif</span> $role";
                                        elseif ($etat == 2)  echo "<span style='background:red;color:white;padding:3px 6px;border-radius:4px;'>Bloqué</span> $role";
                                        elseif ($etat == 3)  echo "<span style='background:orange;color:black;padding:3px 6px;border-radius:4px;'>En attente</span> $role";
                                        elseif ($etat == 4)  echo "<span style='background:gray;color:white;padding:3px 6px;border-radius:4px;'>Supprimé</span> $role";
                                    ?>
                                </td>
                                <td>
                                    <?php if ($u["nin"] !== $_SESSION["user_nin"]): ?>
                                        <form method="POST" style="margin:0; display:flex; gap: 5px;">
                                            <input type="hidden" name="target_nin" value="<?php echo htmlspecialchars($u["nin"]); ?>">
                                            
                                            <?php if ($etat != 1): ?>
                                                <button type="submit" name="action" value="validate" class="btn" style="padding:6px 12px;font-size:13px; background:green; border:none; cursor:pointer; color:#fff; border-radius:4px;">✓ Valider</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($etat != 2): ?>
                                                <button type="submit" name="action" value="block" class="btn" style="padding:6px 12px;font-size:13px; background:#f0ad4e; border:none; cursor:pointer; color:#fff; border-radius:4px;" onclick="return confirm('Confirmer le blocage ?');">🔒 Bloquer</button>
                                            <?php endif; ?>

                                            <button type="submit" name="action" value="delete" class="btn" style="padding:6px 12px;font-size:13px; background:red; border:none; cursor:pointer; color:#fff; border-radius:4px;" onclick="return confirm('Supprimer définitivement ?');">🗑 Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-style:italic;">Votre compte</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <footer>
        <div class="container">
            <p>For support, contact us at: hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
