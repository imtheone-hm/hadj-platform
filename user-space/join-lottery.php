<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('user');

$nin = $_SESSION['user_nin'];

// Get User info (to check if they are active and eligible)
$stmtUser = $pdo->prepare("SELECT * FROM User WHERE nin = ?");
$stmtUser->execute([$nin]);
$user = $stmtUser->fetch();
$is_eligible = ($user['etat_compte'] == 1);

$message = '';
$error = '';

// Handle Join Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_lottery'])) {
    if (!$is_eligible) {
        $error = "Your account is not active. You cannot join lotteries at this time.";
    } else {
        $id_tirage = intval($_POST['id_tirage']);
        
        // Verify lottery is open
        $stmtTirage = $pdo->prepare("SELECT * FROM Tirage WHERE id_tirage = ?");
        $stmtTirage->execute([$id_tirage]);
        $tirage = $stmtTirage->fetch();
        
        if ($tirage && $tirage['etat_tirage'] == 3) {
            // Check if already registered
            $stmtCheck = $pdo->prepare("SELECT * FROM Inscrits WHERE nin = ? AND id_tirage = ?");
            $stmtCheck->execute([$nin, $id_tirage]);
            
            if ($stmtCheck->rowCount() > 0) {
                $error = "You are already registered for this lottery.";
            } else {
                // Register
                $stmtInsert = $pdo->prepare("INSERT INTO Inscrits (nin, id_tirage, date_inscription) VALUES (?, ?, CURDATE())");
                if ($stmtInsert->execute([$nin, $id_tirage])) {
                    $message = "You have successfully joined Lottery N°" . $id_tirage . "!";
                } else {
                    $error = "An error occurred while processing your registration.";
                }
            }
        } else {
            $error = "This lottery is not open for registration.";
        }
    }
}

// Fetch all lotteries (except maybe those already done, or descending order)
$stmtAll = $pdo->query("SELECT * FROM Tirage ORDER BY id_tirage DESC");
$lotteries = $stmtAll->fetchAll();

// Fetch lotteries this user has already joined
$stmtMyJoined = $pdo->prepare("SELECT id_tirage FROM Inscrits WHERE nin = ?");
$stmtMyJoined->execute([$nin]);
$joinedTirages = [];
while ($row = $stmtMyJoined->fetch()) {
    $joinedTirages[] = $row['id_tirage'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Lottery - Hajj Platform</title>
    <link rel="stylesheet" href="../public space/styles.css">
    <style>
        .badge-1 { background-color: #007bff; color: white; padding: 3px 8px; border-radius: 4px; font-size: 13px; }
        .badge-2 { background-color: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-size: 13px; }
        .badge-3 { background-color: #ffc107; color: black; padding: 3px 8px; border-radius: 4px; font-size: 13px; }
        .badge-4 { background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-size: 13px; }
    </style>
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
            <h1>Join an Active Lottery</h1>
            <p>Participate in Hajj lotteries and get your chance</p>
        </div>
    </header>

    <main>
        <div class="container">
            
            <?php if ($message): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    ✅ <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($is_eligible): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Important:</strong> Your account is active. You meet all eligibility requirements to participate in active lotteries.
                </div>
            <?php else: ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong>⚠️ Attention:</strong> Your account is currently not active (Blocked or Pending). You cannot join any lotteries until an administrator approves your account.
                </div>
            <?php endif; ?>

            <div class="grid">
                <?php if (empty($lotteries)): ?>
                    <p>Aucune loterie n'est disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($lotteries as $t): 
                        $has_joined = in_array($t['id_tirage'], $joinedTirages);
                        
                        // Count total participants in this lottery
                        $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM Inscrits WHERE id_tirage = ?");
                        $stmtCount->execute([$t['id_tirage']]);
                        $totalInscrits = $stmtCount->fetch()['total'];
                    ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0;">Lottery N°<?php echo $t['id_tirage']; ?></h3>
                            <?php 
                                $etat = $t['etat_tirage'];
                                if ($etat == 1) echo "<span class='badge-1'>Upcoming</span>";
                                elseif ($etat == 2) echo "<span class='badge-2'>Finished</span>";
                                elseif ($etat == 3) echo "<span class='badge-3'>Active</span>";
                                elseif ($etat == 4) echo "<span class='badge-4'>Closed</span>";
                            ?>
                        </div>
                        
                        <p><strong>Registration Opens:</strong> <?php echo htmlspecialchars($t['date_ouverture_insc']); ?></p>
                        <p><strong>Registration Closes:</strong> <?php echo htmlspecialchars($t['date_cloture_insc']); ?></p>
                        <p><strong>Draw Date:</strong> <span style="font-weight:bold;"><?php echo htmlspecialchars($t['date_tirage']); ?></span></p>
                        
                        <table style="width: 100%; margin: 1rem 0; font-size: 0.9rem;">
                            <tr>
                                <td><strong>Total Spots:</strong></td>
                                <td><?php echo htmlspecialchars($t['nbr_gagnants']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Current Registrations:</strong></td>
                                <td><?php echo $totalInscrits; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Your Participation:</strong></td>
                                <td>
                                    <?php if ($has_joined): ?>
                                        <span style="color:green; font-weight:bold;">Already Joined</span>
                                    <?php else: ?>
                                        <span style="color:red; font-weight:bold;">Not Joined</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                        
                        <?php if ($has_joined): ?>
                            <button class="btn btn-secondary btn-block" disabled style="opacity: 0.7; cursor: not-allowed; width: 100%; padding: 10px; background:grey; color:white; border:none;">Already Registered</button>
                        <?php elseif ($etat == 3 && $is_eligible): ?>
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to join this lottery? By confirming, you accept all terms and conditions.');">
                                <input type="hidden" name="id_tirage" value="<?php echo $t['id_tirage']; ?>">
                                <button type="submit" name="join_lottery" class="btn btn-primary btn-block" style="width: 100%; padding: 10px; background:#007bff; color:white; border:none; cursor:pointer;">Join This Lottery</button>
                            </form>
                        <?php elseif ($etat == 3 && !$is_eligible): ?>
                            <button class="btn btn-danger btn-block" disabled style="opacity: 0.7; cursor: not-allowed; width: 100%; padding: 10px; background:#dc3545; color:white; border:none;">Account Ineligible</button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-block" disabled style="opacity: 0.7; cursor: not-allowed; width: 100%; padding: 10px; background:grey; color:white; border:none;">Registration Not Open</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <h3>Lottery Participation Terms</h3>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li>By joining a lottery, you confirm that all your registration information is accurate and current.</li>
                    <li>You must notify the platform of any changes to your information during the registration period.</li>
                    <li>Multiple registrations under different identities are strictly prohibited and will result in disqualification.</li>
                    <li>If selected as a winner, you will have 30 days to confirm your participation and complete the Hajj requirements.</li>
                    <li>Non-confirmation within the specified period may result in your spot being offered to the next candidate.</li>
                    <li>You accept that the lottery results are final and binding.</li>
                </ul>
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
