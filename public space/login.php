<?php
// public space/login.php
require_once '../includes/session.php';
require_once '../includes/db_connect.php'; // On utilise la vraie base de données
require_once '../includes/validations.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Contrôles de validation côté serveur avec Regex 
    if (empty($email) || empty($password)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!validerEmail($email)) {
        $erreur = "Format d'email invalide.";
    } else {
        // Recherche dans la base de données MySQL
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['psswd'])) {
            if ($user['etat_compte'] == 2) {
                $erreur = "Votre compte est bloqué.";
            } elseif ($user['etat_compte'] == 3) {
                $erreur = "Votre compte est en attente de validation.";
            } elseif ($user['etat_compte'] == 4) {
                $erreur = "Ce compte a été supprimé.";
            } else { // etat_compte == 1 (actif)
                // Création de la session
                $_SESSION['user_nin'] = $user['nin'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom'];
                
                // Redirection basée sur le rôle
                if ($user['role'] == 1) { // 1 = admin
                    header('Location: ../admin-space/index.php');
                } else { // 2 = simple user
                    header('Location: ../user-space/index.php');
                }
                exit();
            }
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hajj Platform</title>
    <link rel="stylesheet" href="styles.css">
    <script src="../validation.js" defer></script>
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo"> Hajj Platform</a>
            <h1 style="text-align: center;">Login Page</h1>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="results.php">Results</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <header>
        <div class="container">
            <h1>Login to Your Account</h1>
            <p>Access your Hajj Platform profile</p>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-container">
                <?php if (!empty($erreur)): ?>
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                        <?php echo htmlspecialchars($erreur); ?>
                    </div>
                <?php endif; ?>
                <form id="loginForm" novalidate method="POST" action="login.php">

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               placeholder="Entrez votre adresse email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Entrez votre mot de passe">
                    </div>

                    <div class="form-group" style="display:flex; justify-content:space-between; align-items:center;">
                        <label style="display:flex; gap:.5rem; align-items:center; cursor:pointer;">
                            <input type="checkbox" id="remember" name="remember">
                            Remember me
                        </label>
                        <a href="#">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>

                <div>
                    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>For support, contact us at: hajj@gmail.com</p>
        </div>
    </footer>

    <script src="validation.js"></script>
    <script>
       

        document.addEventListener('DOMContentLoaded', function () {
            attachBlurValidation(document.getElementById('email'),    validateEmail);
            attachBlurValidation(document.getElementById('password'), validateRequired, 'Le mot de passe');
        });

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            if (!valid) {
                e.preventDefault();
                showFormAlert(this, 'Veuillez corriger les erreurs avant de vous connecter.', 'error');
            }
        });
    </script>
</body>
</html>

