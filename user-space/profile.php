<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('user');

$nin = $_SESSION['user_nin'] ?? '';

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM User WHERE nin = :nin");
$stmt->execute(['nin' => $nin]);
$user = $stmt->fetch();

if (!$user) {
    die("Erreur : Utilisateur introuvable.");
}

$success = '';
$erreur = '';

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $stmtUpdate = $pdo->prepare("UPDATE User SET nom = :nom, prenom = :prenom, date_naiss = :dob, prenom_pere = :father, nom_mere = :motherLast, prenom_mere = :mother, adresse = :address, email = :email, tel = :phone WHERE nin = :nin");
    
    try {
        $stmtUpdate->execute([
            'nom' => $_POST['lastName'],
            'prenom' => $_POST['firstName'],
            'dob' => $_POST['dob'],
            'father' => $_POST['fatherName'],
            'motherLast' => $_POST['motherLastName'],
            'mother' => $_POST['motherName'],
            'address' => $_POST['address'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'nin' => $nin
        ]);
        $success = "Profil mis à jour avec succès.";
        // Refresh User Data
        $stmt->execute(['nin' => $nin]);
        $user = $stmt->fetch();
    } catch(PDOException $e) {
        $erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hajj Platform</title>
    <link rel="stylesheet" href="../public space/styles.css">
    <script src="../validation.js" defer></script>
</head>
<body>

    <nav>
        <div class="container">
            <a href="../public space/index.php" class="logo"> Hajj Platform</a>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="join-lottery.php">Join Lottery</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="notification.php">Notification</a></li>
                <li><a href="../public space/index.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>My Profile</h1>
            <p>View and manage your account information</p>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-container" style="max-width: 900px;">
                
                <?php if (!empty($erreur)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form id="profileForm" novalidate method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">

                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="nin">Numéro d'identification national (NIN)</label>
                            <input type="text" id="nin" value="<?php echo htmlspecialchars($user['nin']); ?>" readonly>
                            <small>Ce champ ne peut pas être modifié.</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name (Prénom)</label>
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name (Nom)</label>
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['date_naiss']); ?>" required>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Family Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fatherName">Father's Name</label>
                                <input type="text" id="fatherName" name="fatherName" value="<?php echo htmlspecialchars($user['prenom_pere']); ?>" required>
                            </div>
                            <div class="form-group">
                                <!-- Adapting form field if needed -->
                                <label for="grandfatherName">Grandfather's Name (Info not in DB)</label>
                                <input type="text" id="grandfatherName" value="" placeholder="Optionnel">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motherName">Mother's First Name</label>
                                <input type="text" id="motherName" name="motherName" value="<?php echo htmlspecialchars($user['prenom_mere']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="motherLastName">Mother's Last Name</label>
                                <input type="text" id="motherLastName" name="motherLastName" value="<?php echo htmlspecialchars($user['nom_mere']); ?>" required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Contact Information</legend>

                        <div class="form-group">
                            <label for="address">Physical Address</label>
                            <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['adresse']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['tel']); ?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Account Status</legend>
                        <p><strong>Status:</strong> 
                        <?php 
                            if ($user['etat_compte'] == 1) echo '<span class="badge badge-success" style="background:green;color:white;padding:3px 6px;border-radius:4px;">Actif</span>';
                            elseif ($user['etat_compte'] == 2) echo '<span class="badge badge-danger" style="background:red;color:white;padding:3px 6px;border-radius:4px;">Bloqué</span>';
                            elseif ($user['etat_compte'] == 3) echo '<span class="badge badge-warning" style="background:orange;color:black;padding:3px 6px;border-radius:4px;">En attente</span>';
                        ?>
                        </p>
                    </fieldset>

                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>

                <div style="margin-top:2.5rem; padding:1.5rem; border:2px solid #c0392b; border-radius:12px; background:#fff8f8;">
                    <h3 style="color:#7b1c1c; margin-bottom:.5rem;">⚠ Danger Zone</h3>
                    <p style="color:var(--text-secondary); margin-bottom:1rem;">
                        Supprimer définitivement votre compte et toutes les données associées. Action irréversible.
                    </p>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Account</button>
                </div>
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
