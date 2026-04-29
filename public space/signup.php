<?php
require_once __DIR__ . '/../php/functions.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_signup($_POST, $errors)) {
        add_user($_POST);
        set_flash('success', 'Votre demande d’inscription a été enregistrée. Un administrateur doit la valider.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Hajj Platform</title>
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
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Créer un compte</h1>
            <p>Inscription sans base de données, avec vérification serveur.</p>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <p>Veuillez corriger les erreurs suivantes :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= safe($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" class="form-container">
                <fieldset>
                    <legend>Type de compte</legend>
                    <div class="form-row">
                        <label><input type="radio" name="role" value="user" <?= ($_POST['role'] ?? 'user') === 'user' ? 'checked' : ''; ?>> Simple user</label>
                        <label><input type="radio" name="role" value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'checked' : ''; ?>> Admin</label>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Informations personnelles</legend>
                    <div class="form-group">
                        <label for="nin">NIN *</label>
                        <input id="nin" name="nin" value="<?= old('nin'); ?>" maxlength="18" placeholder="18 chiffres">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">Prénom *</label>
                            <input id="firstName" name="firstName" value="<?= old('firstName'); ?>" placeholder="Prénom">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Nom *</label>
                            <input id="lastName" name="lastName" value="<?= old('lastName'); ?>" placeholder="Nom">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherName">Prénom du père *</label>
                            <input id="fatherName" name="fatherName" value="<?= old('fatherName'); ?>" placeholder="Prénom du père">
                        </div>
                        <div class="form-group">
                            <label for="grandfatherName">Prénom du grand-père *</label>
                            <input id="grandfatherName" name="grandfatherName" value="<?= old('grandfatherName'); ?>" placeholder="Prénom du grand-père">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherName">Prénom de la mère *</label>
                            <input id="motherName" name="motherName" value="<?= old('motherName'); ?>" placeholder="Prénom de la mère">
                        </div>
                        <div class="form-group">
                            <label for="motherLastName">Nom de la mère *</label>
                            <input id="motherLastName" name="motherLastName" value="<?= old('motherLastName'); ?>" placeholder="Nom de la mère">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date de naissance *</label>
                        <input id="dob" type="date" name="dob" value="<?= old('dob'); ?>">
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Coordonnées</legend>
                    <div class="form-group">
                        <label for="address">Adresse *</label>
                        <textarea id="address" name="address" rows="3"><?= old('address'); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input id="email" type="email" name="email" value="<?= old('email'); ?>" placeholder="nom@exemple.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone *</label>
                            <input id="phone" name="phone" value="<?= old('phone'); ?>" placeholder="+213 555 123 456">
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Mot de passe</legend>
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input id="password" type="password" name="password" placeholder="8 caractères minimum">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmation *</label>
                        <input id="confirmPassword" type="password" name="confirmPassword" placeholder="Confirmez le mot de passe">
                    </div>
                </fieldset>
                <div class="form-group">
                    <label><input type="checkbox" name="terms" <?= isset($_POST['terms']) ? 'checked' : ''; ?>> J'accepte les conditions générales.</label>
                </div>
                <button class="btn btn-primary" type="submit">S'inscrire</button>
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
