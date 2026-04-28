<?php
require_once __DIR__ . '/../php/functions.php';
$user = require_login(2);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if ($email === '' || !validate_email($email)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($existing = find_user_by_email($email)) {
            if ($existing['nin'] !== $user['nin']) {
                $errors['email'] = 'Cette adresse email est déjà utilisée.';
            }
        }
        if ($phone === '' || !validate_phone($phone)) {
            $errors['phone'] = 'Numéro de téléphone invalide.';
        }
        if ($address === '' || !validate_address($address)) {
            $errors['address'] = 'L’adresse doit contenir au moins 10 caractères.';
        }
        if (empty($errors)) {
            update_user($user['nin'], ['email' => $email, 'tel' => $phone, 'adresse' => $address]);
            set_flash('success', 'Votre profil a été mis à jour.');
            redirect('profile.php');
        }
    }
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($current === '' || !password_verify($current, $user['psswd'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        }
        if ($new === '' || !validate_password($new)) {
            $errors['new_password'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre.';
        }
        if ($confirm === '' || $confirm !== $new) {
            $errors['confirm_password'] = 'La confirmation ne correspond pas.';
        }
        if (empty($errors)) {
            update_user($user['nin'], ['psswd' => $new]);
            set_flash('success', 'Le mot de passe a été modifié.');
            redirect('profile.php');
        }
    }
}
$flash = get_flash();
$user = find_user_by_nin($user['nin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Hajj Platform</title>
    <link rel="stylesheet" href="../public%20space/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="../public%20space/index.php" class="logo">Hajj Platform</a>
            <ul>
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="join-lottery.php">Rejoindre un tirage</a></li>
                <li><a href="profile.php">Mon profil</a></li>
                <li><a href="notification.php">Notifications</a></li>
                <li><a href="../php/logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Mon profil</h1>
            <p>Gérez vos informations et changez votre mot de passe.</p>
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
            <div class="grid">
                <div class="card">
                    <h2>Informations</h2>
                    <form method="post" class="form-container">
                        <input type="hidden" name="save_profile" value="1">
                        <div class="form-group">
                            <label>NIN</label>
                            <input value="<?= safe($user['nin']); ?>" disabled>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input value="<?= safe($user['prenom']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Nom</label>
                                <input value="<?= safe($user['nom']); ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" value="<?= isset($_POST['email']) ? safe($_POST['email']) : safe($user['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input id="phone" name="phone" value="<?= isset($_POST['phone']) ? safe($_POST['phone']) : safe($user['tel']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Adresse</label>
                            <textarea id="address" name="address" rows="3"><?= isset($_POST['address']) ? safe($_POST['address']) : safe($user['adresse']); ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </form>
                </div>
                <div class="card">
                    <h2>Changer le mot de passe</h2>
                    <form method="post" class="form-container">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input id="current_password" type="password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input id="new_password" type="password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmation</label>
                            <input id="confirm_password" type="password" name="confirm_password">
                        </div>
                        <button class="btn btn-primary" type="submit">Modifier le mot de passe</button>
                    </form>
                </div>
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
