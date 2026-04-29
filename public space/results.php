<?php
require_once __DIR__ . '/../php/functions.php';
$flash = get_flash();
$results = get_results();
$lotteries = get_all_lotteries();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - Hajj Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="signup.php">Inscription</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Résultats des tirages</h1>
            <p>Liste des gagnants et états des loteries.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?= safe($flash['message']); ?>
                </div>
            <?php endif; ?>

            <section class="card">
                <h2>Loteries</h2>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ouverture</th>
                            <th>Clôture</th>
                            <th>Tirage</th>
                            <th>État</th>
                            <th>Participants</th>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="card" style="margin-top:1.5rem;">
                <h2>Gagnants annoncés</h2>
                <?php if (empty($results)): ?>
                    <p>Aucun résultat n'a encore été publié.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Gagnant</th>
                                <th>NIN</th>
                                <th>Tirage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result):
                                $winner = find_user_by_nin($result['nin']);
                                $tirage = get_lottery_by_id($result['id_tirage']);
                            ?>
                                <tr>
                                    <td><?= $winner ? safe($winner['prenom'] . ' ' . $winner['nom']) : 'Utilisateur supprimé'; ?></td>
                                    <td><code><?= safe($result['nin']); ?></code></td>
                                    <td><?= $tirage ? safe($tirage['date_tirage']) : 'Tirage inconnu'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
