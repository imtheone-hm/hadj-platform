<?php
require_once '../includes/session.php';
require_once '../includes/db_connect.php';
redirigerSiNonAutorise('admin');

$totalUsers = 0;
$activeLotteries = 0;
$pendingApplications = 0;
$blockedAccounts = 0;
$recentLotteries = [];

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM User");
    $totalUsers = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Tirage WHERE etat_tirage = 3");
    $activeLotteries = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM User WHERE etat_compte = 3");
    $pendingApplications = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM User WHERE etat_compte = 2");
    $blockedAccounts = (int) $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT * FROM Tirage ORDER BY id_tirage DESC LIMIT 5");
    $recentLotteries = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Erreur de base de données : ' . $e->getMessage());
}

function statutTirageLabel($etat) {
    switch ($etat) {
        case 1: return ['label' => 'Planned', 'class' => 'badge-1'];
        case 2: return ['label' => 'Finished', 'class' => 'badge-2'];
        case 3: return ['label' => 'Active', 'class' => 'badge-3'];
        case 4: return ['label' => 'Closed', 'class' => 'badge-4'];
        default: return ['label' => 'Unknown', 'class' => 'badge-4'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Hajj Platform</title>
    <link rel="stylesheet" href="../public space/styles.css" />
    <script src="../validation.js" defer></script>
    <style>
      .badge { padding: 3px 8px; border-radius: 4px; color: white; font-size: 13px; }
      .badge-1 { background-color: #007bff; }
      .badge-2 { background-color: #28a745; }
      .badge-3 { background-color: #ffc107; color: black; }
      .badge-4 { background-color: #dc3545; }
      .stats-value { font-size: 2rem; font-weight: bold; margin-top: 0.5rem; }
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
        <h1>Admin Dashboard</h1>
        <p>Platform Administration & Control Center</p>
      </div>
    </header>

    <main>
      <div class="container">
        <div class="grid">
          <div class="card">
            <h3>Total Users</h3>
            <p class="stats-value"><?php echo $totalUsers; ?></p>
            <p><span> user account</span></p>
          </div>

          <div class="card">
            <h3>Active Lotteries</h3>
            <p class="stats-value"><?php echo $activeLotteries; ?></p>
            <p>Counting lotteries where registration is open</p>
          </div>

          <div class="card">
            <h3>Pending Applications</h3>
            <p class="stats-value"><?php echo $pendingApplications; ?></p>
            <p>Users waiting validation by admin</p>
          </div>

          <div class="card">
            <h3>Blocked Accounts</h3>
            <p class="stats-value"><?php echo $blockedAccounts; ?></p>
            <p>Accounts blocked for security or policy reasons</p>
          </div>
        </div>

        <div class="card">
          <h2>Quick Administrative Actions</h2>
          <div>
            <a href="manage-lotteries.php" class="btn btn-primary">Create New Lottery</a>
            <a href="manage-lotteries.php" class="btn btn-primary">View All Lotteries</a>
            <a href="manage-users.php" class="btn btn-primary">View All Users</a>
            <button class="btn btn-secondary" onclick="alert('Generate reports functionality')">Generate Reports</button>
            <button class="btn btn-secondary" onclick="alert('System settings functionality')">System Settings</button>
            <button style="margin-top: 1rem" class="btn btn-danger" onclick="alert('This would show system logs')">View System Logs</button>
          </div>
        </div>

        <div class="card" style="margin-top: 2rem">
          <h3>Recent Lottery Activities</h3>
          <table>
            <thead>
              <tr>
                <th>Lottery</th>
                <th>Status</th>
                <th>Participants</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentLotteries)): ?>
                <tr><td colspan="4" style="text-align:center;">No lotteries found.</td></tr>
              <?php else: ?>
                <?php foreach ($recentLotteries as $lottery): ?>
                  <?php
                    $etatInfo = statutTirageLabel($lottery['etat_tirage']);
                    $participantsStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM Inscrits WHERE id_tirage = ?');
                    $participantsStmt->execute([$lottery['id_tirage']]);
                    $participants = (int) $participantsStmt->fetch()['total'];
                  ?>
                  <tr>
                    <td>Lottery N°<?php echo htmlspecialchars($lottery['id_tirage']); ?></td>
                    <td><span class="badge <?php echo $etatInfo['class']; ?>"><?php echo $etatInfo['label']; ?></span></td>
                    <td><?php echo $participants; ?></td>
                    <td><a href="manage-lotteries.php">View Details</a></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h3>System Health Status</h3>
          <div>
            <p><strong>Database:</strong> <span class="badge badge-success">Operational</span></p>
            <p><strong>API Server:</strong> <span class="badge badge-success">Operational</span></p>
            <p><strong>Email Service:</strong> <span class="badge badge-success">Operational</span></p>
            <p><strong>Payment Gateway:</strong> <span class="badge badge-success">Operational</span></p>
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
