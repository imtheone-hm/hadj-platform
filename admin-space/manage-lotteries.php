<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('admin');

$message = '';

// Handle form submission to create or update a lottery
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $date_ouverture = $_POST['date_ouverture'] ?? '';
        $date_cloture = $_POST['date_cloture'] ?? '';
        $date_tirage = $_POST['date_tirage'] ?? '';
        $nbr_gagnants = $_POST['nbr_gagnants'] ?? 0;
        $etat_tirage = $_POST['etat_tirage'] ?? 1;

        if (!empty($date_ouverture) && !empty($date_cloture) && !empty($date_tirage) && $nbr_gagnants > 0) {
            $stmt = $pdo->prepare("INSERT INTO Tirage (date_ouverture_insc, date_cloture_insc, date_tirage, nbr_gagnants, etat_tirage) VALUES (:do, :dc, :dt, :ng, :et)");
            $stmt->execute([
                'do' => $date_ouverture,
                'dc' => $date_cloture,
                'dt' => $date_tirage,
                'ng' => $nbr_gagnants,
                'et' => $etat_tirage
            ]);
            $message = "La nouvelle campagne de Hadj a été créée avec succès.";
        } else {
            $message = "Veuillez remplir tous les champs correctement.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'change_state') {
        $id_tirage = $_POST['id_tirage'] ?? 0;
        $new_state = $_POST['new_state'] ?? 0;
        if ($id_tirage && $new_state) {
            $stmt = $pdo->prepare("UPDATE Tirage SET etat_tirage = :et WHERE id_tirage = :id");
            $stmt->execute(['et' => $new_state, 'id' => $id_tirage]);
            $message = "L'état du tirage a été mis à jour.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id_tirage = $_POST['id_tirage'] ?? 0;
        if ($id_tirage) {
            $stmt = $pdo->prepare("DELETE FROM Tirage WHERE id_tirage = :id");
            $stmt->execute(['id' => $id_tirage]);
            $message = "Le tirage a été supprimé definitivement.";
        }
    }
}

// Fetch all lotteries
$tiragesStmt = $pdo->query("SELECT * FROM Tirage ORDER BY id_tirage DESC");
$tirages = $tiragesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lotteries - Hajj Platform Admin</title>
    <link rel="stylesheet" href="../public space/styles.css">
    <style>
        .badge { padding: 3px 8px; border-radius: 4px; color: white; font-size: 13px; }
        .badge-1 { background-color: #007bff; } /* planifié */
        .badge-2 { background-color: #28a745; } /* effectué */
        .badge-3 { background-color: #ffc107; color: black; } /* ouvert */
        .badge-4 { background-color: #dc3545; } /* fermé */
        .action-btn { padding: 5px 10px; font-size: 12px; cursor: pointer; border: none; border-radius: 3px; color: white; margin-right: 2px; }
        .btn-green { background-color: #28a745; }
        .btn-orange { background-color: #ffc107; color: black; }
        .btn-red { background-color: #dc3545; }
        .btn-blue { background-color: #007bff; }
    </style>
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
            <h1>Manage Lotteries</h1>
            <p>Create and control the status of Hajj campaigns</p>
        </div>
    </header>

    <main>
        <div class="container">

            <?php if (!empty($message)): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align:center;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Create a New Lottery</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ouverture des Inscriptions *</label>
                            <input type="date" name="date_ouverture" required>
                        </div>
                        <div class="form-group">
                            <label>Clôture des Inscriptions *</label>
                            <input type="date" name="date_cloture" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date du Tirage au sort *</label>
                            <input type="date" name="date_tirage" required>
                        </div>
                        <div class="form-group">
                            <label>Nombre de gagnants *</label>
                            <input type="number" name="nbr_gagnants" min="1" placeholder="Ex: 1000" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Statut initial</label>
                        <select name="etat_tirage" class="form-control" style="width: 100%; padding: 10px;">
                            <option value="1">Planifié</option>
                            <option value="3">Inscriptions Ouvertes</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Create Campaign</button>
                </form>
            </div>

            <div class="card" style="margin-top:2rem;">
                <h2>All Lottery Campaigns</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Inscriptions (Début -> Fin)</th>
                            <th>Date du Tirage</th>
                            <th>Gagnants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tirages)): ?>
                            <tr><td colspan="6" style="text-align:center;">Aucune loterie trouvée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tirages as $t): ?>
                            <tr>
                                <td><code>#<?php echo $t['id_tirage']; ?></code></td>
                                <td><?php echo htmlspecialchars($t['date_ouverture_insc'] . '  au  ' . $t['date_cloture_insc']); ?></td>
                                <td><strong><?php echo htmlspecialchars($t['date_tirage']); ?></strong></td>
                                <td><?php echo htmlspecialchars($t['nbr_gagnants']); ?> pers.</td>
                                <td>
                                    <?php 
                                        $etat = $t['etat_tirage'];
                                        if ($etat == 1) echo "<span class='badge badge-1'>Planifié</span>";
                                        elseif ($etat == 2) echo "<span class='badge badge-2'>Effectué</span>";
                                        elseif ($etat == 3) echo "<span class='badge badge-3'>Ins. Ouvertes</span>";
                                        elseif ($etat == 4) echo "<span class='badge badge-4'>Ins. Fermées</span>";
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline-block; margin:0; padding:0;">
                                        <input type="hidden" name="id_tirage" value="<?php echo $t['id_tirage']; ?>">
                                        <input type="hidden" name="action" value="change_state">
                                        <select name="new_state" onchange="this.form.submit()" style="padding: 4px; font-size: 13px;">
                                            <option value="">-- Changer Etat --</option>
                                            <option value="1">Planifié</option>
                                            <option value="3">Ouvrir Inscriptions</option>
                                            <option value="4">Fermer Inscriptions</option>
                                            <option value="2">Marquer Effectué</option>
                                        </select>
                                    </form>
                                    <form method="POST" style="display:inline-block; margin:0; padding:0;">
                                        <input type="hidden" name="id_tirage" value="<?php echo $t['id_tirage']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn btn-red" onclick="return confirm('Voulez-vous vraiment supprimer ce tirage ?');">🗑</button>
                                    </form>
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
