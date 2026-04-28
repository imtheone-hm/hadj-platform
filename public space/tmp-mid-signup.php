<?php
// public space/signup.php
require_once '../includes/session.php';
require_once '../includes/db_connect.php'; // Remplacement avec la vraie base
require_once '../includes/validations.php';

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
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

    if (empty($nin) || empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($dob) || empty($address)) {
        $erreur = "Tous les champs obligatoires doivent Ãªtre remplis.";
    } elseif (!$terms) {
        $erreur = "Vous devez accepter les conditions gÃ©nÃ©rales.";
    } elseif ($password !== $confirmPassword) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!validerNIN($nin)) {
        $erreur = "Le NIN doit comporter exactement 18 chiffres.";
    } elseif (!validerEmail($email)) {
        $erreur = "L'adresse email format est invalide.";
    } elseif (!validerMotDePasse($password)) {
        $erreur = "Le mot de passe (min 8 caractÃ¨res, 1 lettre, 1 chiffre) est trop faible.";
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
                    $erreur = "Ce NIN (NumÃ©ro d'Identification National) est dÃ©jÃ  utilisÃ©.";
                } else {
                    $erreur = "Cette adresse e-mail est dÃ©jÃ  utilisÃ©e.";
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
                    ':prenom_pere'  => $fatherName, // prenom grand pere ou pere
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
                
                $success = "Inscription rÃ©ussie ! Votre compte a Ã©tÃ© crÃ©Ã©.";
                 
            }
        } catch(PDOException $e) {
            $erreur = "Erreur fatale de base de donnÃ©es : " . $e->getMessage();
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
                    <button type="button" class="btn btn-secondary" onclick="backToRoleSelection()">â† Back to Role Selection</button>
                </div>

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
<form id="signupForm" novalidate method="POST" action="signup.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(<?php
// public space/signup.php
require_once '../includes/session.php';
require_once '../includes/db_connect.php'; // Remplacement avec la vraie base
require_once '../includes/validations.php';

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
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

    if (empty($nin) || empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($dob) || empty($address)) {
        $erreur = "Tous les champs obligatoires doivent Ãªtre remplis.";
    } elseif (!$terms) {
        $erreur = "Vous devez accepter les conditions gÃ©nÃ©rales.";
    } elseif ($password !== $confirmPassword) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!validerNIN($nin)) {
        $erreur = "Le NIN doit comporter exactement 18 chiffres.";
    } elseif (!validerEmail($email)) {
        $erreur = "L'adresse email format est invalide.";
    } elseif (!validerMotDePasse($password)) {
        $erreur = "Le mot de passe (min 8 caractÃ¨res, 1 lettre, 1 chiffre) est trop faible.";
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
                    $erreur = "Ce NIN (NumÃ©ro d'Identification National) est dÃ©jÃ  utilisÃ©.";
                } else {
                    $erreur = "Cette adresse e-mail est dÃ©jÃ  utilisÃ©e.";
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
                    ':prenom_pere'  => $fatherName, // prenom grand pere ou pere
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
                
                $success = "Inscription rÃ©ussie ! Votre compte a Ã©tÃ© crÃ©Ã©.";
                 
            }
        } catch(PDOException $e) {
            $erreur = "Erreur fatale de base de donnÃ©es : " . $e->getMessage();
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
                    <button type="button" class="btn btn-secondary" onclick="backToRoleSelection()">â† Back to Role Selection</button>
                </div>

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
<form id="signupForm" novalidate method="POST" action="signup.php">

                  
                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="nin">NumÃ©ro d'identification national (NIN) *</label>
                            <input type="text" id="nin" name="nin" required
                                   placeholder="18 chiffres â€” ex : 123456789012345678"
                                   maxlength="18" inputmode="numeric">
                            <small>18 chiffres, sans espaces</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name (PrÃ©nom) *</label>
                                <input type="text" id="firstName" name="firstName" required
                                       placeholder="Votre prÃ©nom">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name (Nom) *</label>
                                <input type="text" id="lastName" name="lastName" required
                                       placeholder="Votre nom">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" required>
                            <small>Vous devez avoir au moins 18 ans</small>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Family Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fatherName">Father's Name *</label>
                                <input type="text" id="fatherName" name="fatherName" required
                                       placeholder="PrÃ©nom du pÃ¨re">
                            </div>
                            <div class="form-group">
                                <label for="grandfatherName">Grandfather's Name *</label>
                                <input type="text" id="grandfatherName" name="grandfatherName" required
                                       placeholder="PrÃ©nom du grand-pÃ¨re">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motherName">Mother's Name *</label>
                                <input type="text" id="motherName" name="motherName" required
                                       placeholder="PrÃ©nom de la mÃ¨re">
                            </div>
                            <div class="form-group">
                                <label for="motherLastName">Mother's Last Name *</label>
                                <input type="text" id="motherLastName" name="motherLastName" required
                                       placeholder="Nom de la mÃ¨re">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Contact Information</legend>

                        <div class="form-group">
                            <label for="address">Physical Address *</label>
                            <textarea id="address" name="address" required rows="3"
                                      placeholder="Adresse complÃ¨te (au moins 10 caractÃ¨res)"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       placeholder="nom@exemple.com">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required
                                       placeholder="+213 555 123 456">
                            </div>
                        </div>
                    </fieldset>

          
                    <fieldset>
                        <legend>Account Security</legend>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required
                                   placeholder="CrÃ©er un mot de passe fort" minlength="8">
                            <small>Min 8 caractÃ¨res, une lettre et un chiffre</small>

                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password *</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required
                                   placeholder="Confirmez votre mot de passe">
                        </div>
                    </fieldset>

                    <!-- â”€â”€ Conditions gÃ©nÃ©rales â”€â”€ -->
                    <div class="form-group">
                        <label style="display:flex; gap:.6rem; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" id="terms" name="terms" required style="margin-top:3px;">
                            <span>
                                J'accepte les Conditions GÃ©nÃ©rales et confirme que toutes
                                les informations fournies sont exactes et vÃ©ridiques.
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                </form>

                <div style="text-align: center; margin-top: 2rem;">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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

            attachBlurValidation(document.getElementById('nin'),              validateNIN);
            attachBlurValidation(document.getElementById('firstName'),        validateName, 'Le prÃ©nom');
            attachBlurValidation(document.getElementById('lastName'),         validateName, 'Le nom');
            attachBlurValidation(document.getElementById('dob'),              validateDOB);
            attachBlurValidation(document.getElementById('fatherName'),       validateName, 'Le prÃ©nom du pÃ¨re');
            attachBlurValidation(document.getElementById('grandfatherName'),  validateName, 'Le prÃ©nom du grand-pÃ¨re');
            attachBlurValidation(document.getElementById('motherName'),       validateName, 'Le prÃ©nom de la mÃ¨re');
            attachBlurValidation(document.getElementById('motherLastName'),   validateName, 'Le nom de la mÃ¨re');
            attachBlurValidation(document.getElementById('address'),          validateAddress);
            attachBlurValidation(document.getElementById('email'),            validateEmail);
            attachBlurValidation(document.getElementById('phone'),            validatePhone);
            attachBlurValidation(document.getElementById('password'),         validatePassword);

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
            // e.preventDefault() retirÃ© pour permettre le POST serveur
            
            // Si vous avez besoin d'ajouter le rÃ´le sÃ©lectionnÃ© dans le POST
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
        });
    </script>
</body>
</html>


SESSION['csrf_token'] ?? ''); ?>">

                  
                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="nin">NumÃ©ro d'identification national (NIN) *</label>
                            <input type="text" id="nin" name="nin" required
                                   placeholder="18 chiffres â€” ex : 123456789012345678"
                                   maxlength="18" inputmode="numeric">
                            <small>18 chiffres, sans espaces</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name (PrÃ©nom) *</label>
                                <input type="text" id="firstName" name="firstName" required
                                       placeholder="Votre prÃ©nom">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name (Nom) *</label>
                                <input type="text" id="lastName" name="lastName" required
                                       placeholder="Votre nom">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" required>
                            <small>Vous devez avoir au moins 18 ans</small>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Family Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fatherName">Father's Name *</label>
                                <input type="text" id="fatherName" name="fatherName" required
                                       placeholder="PrÃ©nom du pÃ¨re">
                            </div>
                            <div class="form-group">
                                <label for="grandfatherName">Grandfather's Name *</label>
                                <input type="text" id="grandfatherName" name="grandfatherName" required
                                       placeholder="PrÃ©nom du grand-pÃ¨re">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motherName">Mother's Name *</label>
                                <input type="text" id="motherName" name="motherName" required
                                       placeholder="PrÃ©nom de la mÃ¨re">
                            </div>
                            <div class="form-group">
                                <label for="motherLastName">Mother's Last Name *</label>
                                <input type="text" id="motherLastName" name="motherLastName" required
                                       placeholder="Nom de la mÃ¨re">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Contact Information</legend>

                        <div class="form-group">
                            <label for="address">Physical Address *</label>
                            <textarea id="address" name="address" required rows="3"
                                      placeholder="Adresse complÃ¨te (au moins 10 caractÃ¨res)"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       placeholder="nom@exemple.com">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required
                                       placeholder="+213 555 123 456">
                            </div>
                        </div>
                    </fieldset>

          
                    <fieldset>
                        <legend>Account Security</legend>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required
                                   placeholder="CrÃ©er un mot de passe fort" minlength="8">
                            <small>Min 8 caractÃ¨res, une lettre et un chiffre</small>

                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password *</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required
                                   placeholder="Confirmez votre mot de passe">
                        </div>
                    </fieldset>

                    <!-- â”€â”€ Conditions gÃ©nÃ©rales â”€â”€ -->
                    <div class="form-group">
                        <label style="display:flex; gap:.6rem; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" id="terms" name="terms" required style="margin-top:3px;">
                            <span>
                                J'accepte les Conditions GÃ©nÃ©rales et confirme que toutes
                                les informations fournies sont exactes et vÃ©ridiques.
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                </form>

                <div style="text-align: center; margin-top: 2rem;">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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

            attachBlurValidation(document.getElementById('nin'),              validateNIN);
            attachBlurValidation(document.getElementById('firstName'),        validateName, 'Le prÃ©nom');
            attachBlurValidation(document.getElementById('lastName'),         validateName, 'Le nom');
            attachBlurValidation(document.getElementById('dob'),              validateDOB);
            attachBlurValidation(document.getElementById('fatherName'),       validateName, 'Le prÃ©nom du pÃ¨re');
            attachBlurValidation(document.getElementById('grandfatherName'),  validateName, 'Le prÃ©nom du grand-pÃ¨re');
            attachBlurValidation(document.getElementById('motherName'),       validateName, 'Le prÃ©nom de la mÃ¨re');
            attachBlurValidation(document.getElementById('motherLastName'),   validateName, 'Le nom de la mÃ¨re');
            attachBlurValidation(document.getElementById('address'),          validateAddress);
            attachBlurValidation(document.getElementById('email'),            validateEmail);
            attachBlurValidation(document.getElementById('phone'),            validatePhone);
            attachBlurValidation(document.getElementById('password'),         validatePassword);

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
            // e.preventDefault() retirÃ© pour permettre le POST serveur
            
            // Si vous avez besoin d'ajouter le rÃ´le sÃ©lectionnÃ© dans le POST
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
        });
    </script>
</body>
</html>



