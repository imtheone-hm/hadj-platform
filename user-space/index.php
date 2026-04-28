<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('user');

$nin = $_SESSION['user_nin'];

// Get User
$stmtUser = $pdo->prepare("SELECT * FROM User WHERE nin = ?");
$stmtUser->execute([$nin]);
$user = $stmtUser->fetch();

// Stats
$stmtJoined = $pdo->prepare("SELECT COUNT(*) as total FROM Inscrits WHERE nin = ?");
$stmtJoined->execute([$nin]);
$totalJoined = $stmtJoined->fetch()['total'];

$stmtWins = $pdo->prepare("SELECT COUNT(*) as total FROM Resultats WHERE nin = ?");
$stmtWins->execute([$nin]);
$totalWins = $stmtWins->fetch()['total'];

// Active Lottery
$stmtActive = $pdo->query("SELECT * FROM Tirage WHERE etat_tirage = 3 ORDER BY id_tirage DESC LIMIT 1");
$activeTirage = $stmtActive->fetch();

// Recent Joined
$stmtRecent = $pdo->prepare("
    SELECT i.date_inscription, t.id_tirage, t.date_ouverture_insc 
    FROM Inscrits i 
    JOIN Tirage t ON i.id_tirage = t.id_tirage 
    WHERE i.nin = ? 
    ORDER BY i.date_inscription DESC LIMIT 5
");
$stmtRecent->execute([$nin]);
$recentActivities = $stmtRecent->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Hajj Platform</title>
    <link rel="stylesheet" href="../public space/styles.css">
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
            <h1>Welcome, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>! </h1>
            <p>User Dashboard - Your Hajj Platform Hub</p>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="grid">
                <div class="card">
                    <h3>Status</h3>
                    <p>
                        <strong>Account Status:</strong> 
                        <?php 
                            if ($user['etat_compte'] == 1) echo '<span class="badge badge-success" style="background:green;color:white;padding:3px 6px;border-radius:4px;">Actif</span>';
                            elseif ($user['etat_compte'] == 2) echo '<span class="badge badge-danger" style="background:red;color:white;padding:3px 6px;border-radius:4px;">Bloqué</span>';
                            elseif ($user['etat_compte'] == 3) echo '<span class="badge badge-warning" style="background:orange;color:black;padding:3px 6px;border-radius:4px;">En attente</span>';
                        ?>
                    </p>
                    <p>
                        <strong>Lotteries Joined:</strong> <?php echo $totalJoined; ?>
                    </p>
                </div>

                <div class="card">
                    <h3>Current Lottery</h3>
                    <p>
                        <?php if ($activeTirage): ?>
                            <strong>Tirage N°<?php echo htmlspecialchars($activeTirage['id_tirage']); ?></strong>
                        <?php else: ?>
                            <em>Aucun tirage en cours.</em>
                        <?php endif; ?>
                    </p>
                    <p>
                        <?php if ($activeTirage): ?>
                            Registration closes: <?php echo htmlspecialchars($activeTirage['date_cloture_insc']); ?>
                        <?php else: ?>
                            Revenez plus tard.
                        <?php endif; ?>
                    </p>
                </div>

                <div class="card">
                    <h3>Selection History</h3>
                    <p>
                        <strong>Times Selected:</strong> <span style="font-size:1.2rem;font-weight:bold;color:green;"><?php echo $totalWins; ?></span>
                    </p>
                    <p>
                        <?php echo ($totalWins > 0) ? 'Félicitations, vous avez été tiré au sort !' : 'Keep trying! Your chance will come.'; ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentActivities)): ?>
                            <tr><td colspan="3" style="text-align:center;">Aucune activité récente.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['date_inscription']); ?></td>
                                    <td>Joined Lottery N°<?php echo htmlspecialchars($activity['id_tirage']); ?></td>
                                    <td><span style="background:green;color:white;padding:3px 6px;border-radius:4px;font-size:0.8rem;">Joined</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <h2>Quick Actions</h2>
                <div>
                    <a href="join-lottery.php" class="btn btn-primary">Join Active Lottery</a>
                    <a href="profile.php" class="btn btn-primary">View My Profile</a>
                    <a href="notification.php" class="btn btn-primary">Latest News</a>
                    <a href="../public space/results.php" class="btn btn-secondary">View Results</a>
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
