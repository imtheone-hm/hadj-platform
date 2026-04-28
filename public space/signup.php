<?php
// public space/signup.php
require_once '../includes/session.php';
require_once '../includes/db_connect.php'; 
require_once '../includes/validations.php';

// Generate CSRF Token and store in session if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF Validation
    $submitted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submitted_csrf)) {
        $erreur = "Erreur de sécurité (CSRF). Veuillez réessayer.";
    } else {
    
        $roleInput       = trim($_POST['role'] ?? 'user'); 
        
        $nin             = trim($_POST['nin'] ?? '');
        $firstName       = trim($_POST['firstName'] ?? '');
        $lastName        = trim($_POST['lastName'] ?? '');
        $dob             = trim($_POST['dob'] ?? '');
        
        $fatherName      = trim($_POST['fatherName'] ?? '');
        $motherName      = trim($_POST['motherName'] ?? '');
        $motherLastName  = trim($_POST['motherLastName'] ?? '');
        $grandfatherName = trim($_POST['grandfatherName'] ?? '');
        
        $address         = trim($_POST['address'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $terms           = isset($_POST['terms']);

        // Check age server-side
        $ageValid = false;
        if (!empty($dob)) {
            $birthDate = new DateTime($dob);
            $now = new DateTime();
            $diff = $now->diff($birthDate);
            if ($diff->y >= 18 && $diff->y <= 120) {
                $ageValid = true;
            }
        }

        if (empty($nin) || empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($dob) || empty($address)) {
            $erreur = "Tous les champs obligatoires doivent être remplis.";
        } elseif (!$terms) {
            $erreur = "Vous devez accepter les conditions générales.";
        } elseif ($password !== $confirmPassword) {
            $erreur = "Les mots de passe ne correspondent pas.";
        } elseif (!$ageValid) {
            $erreur = "Vous devez avoir au moins 18 ans et une date de naissance valide.";
        } elseif (!validerNIN($nin)) {
            $erreur = "Le NIN doit comporter exactement 18 chiffres.";
        } elseif (!validerEmail($email)) {
            $erreur = "Le format de l'adresse email est invalide.";
        } elseif (!validerMotDePasse($password)) {
            $erreur = "Le mot de passe doit contenir au moins 8 caractères, dont 1 lettre et 1 chiffre.";
        } elseif (!validerNomPrenom($firstName) || !validerNomPrenom($lastName)) {
            $erreur = "Le nom et prénom ne doivent contenir que des lettres, espaces ou tirets.";
        } else {
           
            $etat_compte = 3; 
            $role_num = ($roleInput === 'admin') ? 1 : 2; 
            
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $checkStmt = $pdo->prepare("SELECT nin, email FROM User WHERE nin = :nin OR email = :email");
                $checkStmt->execute(['nin' => $nin, 'email' => $email]);
                $existing = $checkStmt->fetch();
                
                if ($existing) {
                    if ($existing['nin'] === $nin) {
                        $erreur = "Ce NIN (Numéro d'Identification National) est déjà utilisé.";
                    } else {
                        $erreur = "Cette adresse email est déjà utilisée.";
                    }
                } else {
                    
                    $insertStmt = $pdo->prepare("
                        INSERT INTO User (nin, nom, prenom, prenom_pere, nom_mere, prenom_mere, date_naiss, adresse, email, tel, psswd, etat_compte, role)
                        VALUES (:nin, :nom, :prenom, :prenom_pere, :nom_mere, :prenom_mere, :date_naiss, :adresse, :email, :tel, :psswd, :etat_compte, :role)
                    ");
                    
                    $insertStmt->execute([
                        ':nin'          => $nin,
                        ':nom'          => $lastName,
                        ':prenom'       => $firstName,
                        ':prenom_pere'  => $fatherName,
                        ':nom_mere'     => $motherLastName,
                        ':prenom_mere'  => $motherName,
                        ':date_naiss'   => $dob,
                        ':adresse'      => $address,
                        ':email'        => $email,
                        ':tel'          => $phone,
                        ':psswd'        => $hashed_password,
                        ':etat_compte'  => $etat_compte,
                        ':role'         => $role_num
                    ]);
                    
                    $success = "Inscription réussie ! Votre compte a été créé.";
                     
                }
            } catch(PDOException $e) {
                $erreur = "Erreur fatale de base de données : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hajj Platform</title>
    <link rel="stylesheet" href="styles.css">
    <script src="../validation.js" defer></script> 
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">Hajj Platform</a>
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
            <h1>Create Your Account</h1>
            <p>Join the Hajj Platform community</p>
        </div>
    </header>

    <main>
        <div class="container">

            <div class="form-container" id="roleSelection" style="display: <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'none' : 'block'; ?>;">
                <h2>Choose Your Account Type</h2>
                <p>Select whether you want to sign up as a regular user or an administrator</p>
                <div style="display: flex; gap: 2rem; justify-content: center; margin-top: 2rem;">
                    <button type="button" class="btn btn-primary" onclick="selectRole('user')" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        Sign Up as User
                    </button>
                    <button type="button" class="btn btn-primary" onclick="selectRole('admin')" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        Sign Up as Admin
                    </button>
                </div>
            </div>

            <div class="form-container" id="formContainer" style="display: <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'block' : 'none'; ?>;">
                <div style="margin-bottom: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="backToRoleSelection()">← Back to Role Selection</button>
                </div>

                <?php if (!empty($erreur)): ?>
                <div class="form-alert error" style="display:block;">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="form-alert success" style="display:block;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <script>
                    setTimeout(() => { window.location.href = 'login.php'; }, 3000);
                </script>
                <?php endif; ?>

                <form id="signupForm" novalidate method="POST" action="signup.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="nin">Numéro d'identification national (NIN) *</label>
                            <input type="text" id="nin" name="nin" required
                                   value="<?php echo htmlspecialchars($_POST['nin'] ?? ''); ?>"
                                   placeholder="18 chiffres — ex : 123456789012345678"
                                   maxlength="18" inputmode="numeric">
                            <small>18 chiffres, sans espaces</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name (Prénom) *</label>
                                <input type="text" id="firstName" name="firstName" required
                                       value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>"
                                       placeholder="Votre prénom">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name (Nom) *</label>
                                <input type="text" id="lastName" name="lastName" required
                                       value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>"
                                       placeholder="Votre nom">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" required
                                   value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                            <small>Vous devez avoir au moins 18 ans</small>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Family Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fatherName">Father's Name *</label>
                                <input type="text" id="fatherName" name="fatherName" required
                                       value="<?php echo htmlspecialchars($_POST['fatherName'] ?? ''); ?>"
                                       placeholder="Prénom du père">
                            </div>
                            <div class="form-group">
                                <label for="motherName">Mother's First Name *</label>
                                <input type="text" id="motherName" name="motherName" required
                                       value="<?php echo htmlspecialchars($_POST['motherName'] ?? ''); ?>"
                                       placeholder="Prénom de la mère">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motherLastName">Mother's Maiden Name *</label>
                                <input type="text" id="motherLastName" name="motherLastName" required
                                       value="<?php echo htmlspecialchars($_POST['motherLastName'] ?? ''); ?>"
                                       placeholder="Nom de jeune fille de la mère">
                            </div>
                            <div class="form-group">
                                <label for="grandfatherName">Grandfather's Name *</label>
                                <input type="text" id="grandfatherName" name="grandfatherName" required
                                       value="<?php echo htmlspecialchars($_POST['grandfatherName'] ?? ''); ?>"
                                       placeholder="Prénom du grand-père maternel">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Contact Information</legend>

                        <div class="form-group">
                            <label for="address">Full Address *</label>
                            <input type="text" id="address" name="address" required
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                                   placeholder="Votre adresse complète">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="nom@exemple.com">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="+213 XXX XXX XXX">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Security</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required
                                       placeholder="Minimum 8 characters">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password *</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" required
                                       placeholder="Retype password">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group checkbox-group" style="display:flex; align-items:baseline; gap:10px; margin-top: 1rem;">
                        <input type="checkbox" id="terms" name="terms" required <?php if(isset($_POST['terms'])) echo 'checked'; ?>>
                        <label for="terms">I confirm that all provided information is accurate and I accept the terms and conditions.</label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Account</button>
                </form>
            </div>

        </div>
    </main>

    <footer>
        <div class="container">
            <p>For support, contact us at: hajj@gmail.com</p>
        </div>
    </footer>

    <script>
        let selectedRole = null;

        function selectRole(role) {
            selectedRole = role;
            document.getElementById('roleSelection').style.display = 'none';
            document.getElementById('formContainer').style.display = 'block';
            document.getElementById('formContainer').scrollIntoView({ behavior: 'smooth' });
        }

        function backToRoleSelection() {
            selectedRole = null;
            document.getElementById('roleSelection').style.display = 'block';
            document.getElementById('formContainer').style.display = 'none';
            document.getElementById('roleSelection').scrollIntoView({ behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', function () {
            attachPasswordStrength(document.getElementById('password'));

            attachBlurValidation(document.getElementById('nin'), validateNIN);
            attachBlurValidation(document.getElementById('firstName'), validateName, 'Le prénom');
            attachBlurValidation(document.getElementById('lastName'), validateName, 'Le nom');
            attachBlurValidation(document.getElementById('dob'), validateDOB);
            attachBlurValidation(document.getElementById('email'), validateEmail);
            attachBlurValidation(document.getElementById('phone'), validatePhone);
            attachBlurValidation(document.getElementById('password'), validatePassword);
            
            document.getElementById('password').addEventListener('input', () => {
                const confirm = document.getElementById('confirmPassword');
                if (confirm.value !== '') clearError(confirm);
            });
            attachBlurValidation(
                document.getElementById('confirmPassword'),
                validatePasswordConfirm,
                document.getElementById('password')
            );
        });

        document.getElementById('signupForm').addEventListener('submit', function (e) {
            const form = this;
            const alerts = form.querySelectorAll('.error-msg, .input-error');
            alerts.forEach(el => {
                if (el.classList.contains('error-msg')) el.remove();
                if (el.classList.contains('input-error')) el.classList.remove('input-error');
            });

            let isValid = true;

            isValid &= validateNIN(document.getElementById('nin'));
            isValid &= validateName(document.getElementById('firstName'), 'Le prénom');
            isValid &= validateName(document.getElementById('lastName'), 'Le nom');
            isValid &= validateDOB(document.getElementById('dob'));
            isValid &= validateAddress(document.getElementById('address'));
            isValid &= validateEmail(document.getElementById('email'));
            if(document.getElementById('phone').value.trim() !== '') {
                isValid &= validatePhone(document.getElementById('phone'));
            }
            isValid &= validatePassword(document.getElementById('password'));
            isValid &= validatePasswordConfirm(document.getElementById('confirmPassword'), document.getElementById('password'));
            isValid &= validateCheckbox(document.getElementById('terms'), 'les conditions');

            if (!isValid) {
                e.preventDefault();
                showFormAlert(form, 'Veuillez corriger les erreurs en rouge avant de soumettre.', 'error');
            } else {
                if (selectedRole) {
                    let roleInput = document.getElementById('selectedRoleInput');
                    if (!roleInput) {
                        roleInput = document.createElement('input');
                        roleInput.type = 'hidden';
                        roleInput.name = 'role';
                        roleInput.id = 'selectedRoleInput';
                        this.appendChild(roleInput);
                    }
                    roleInput.value = selectedRole;
                }
            }
        });
    </script>
</body>
</html>
