<?php
require_once __DIR__ . '/../php/functions.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = validate_login($_POST, $errors);
    if ($user) {
        $_SESSION['user_nin'] = $user['nin'];
        set_flash('success', 'Connexion réussie.');
        if ($user['role'] === 1) {
            redirect('../admin-space/index.php');
        }
        redirect('../user-space/index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Hajj Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="signup.php">Inscription</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Connexion</h1>
            <p>Accédez à votre espace utilisateur ou administrateur.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= safe($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="post" class="form-container">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" value="<?= old('email'); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input id="password" type="password" name="password">
                </div>
                <button class="btn btn-primary" type="submit">Se connecter</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Pour support : hajj@gmail.com</p>
        </div>
    </footer>
</body>
</html>
