<?php
// error.php
$error_code = isset($_GET['code']) ? $_GET['code'] : 404;

$messages = [
    403 => "Accès refusé. Vous n'avez pas les droits nécessaires pour voir cette page.",
    404 => "Page introuvable.",
    500 => "Erreur interne du serveur."
];

$message_a_afficher = isset($messages[$error_code]) ? $messages[$error_code] : "Une erreur inconnue s'est produite.";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur <?php echo htmlspecialchars($error_code); ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f4f4f4; }
        .error-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        h1 { color: #d9534f; }
        a { text-decoration: none; color: #0275d8; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Erreur <?php echo htmlspecialchars($error_code); ?></h1>
        <p><?php echo htmlspecialchars($message_a_afficher); ?></p>
        <br>
        <a href="public space/index.php">Retour à l'accueil</a>
    </div>
</body>
</html>
