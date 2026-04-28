<?php
// includes/validations.php

/**
 * Valide un NIN (Numéro d'Identification National)
 * Supposons que c'est une chaîne de 18 chiffres.
 */
function validerNIN($nin) {
    return preg_match('/^[0-9]{18}$/', $nin);
}

/**
 * Valide un email
 */
function validerEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

/**
 * Valide un mot de passe (Au moins 8 caractères, 1 majuscule, 1 lettre, 1 chiffre)
 */
function validerMotDePasse($password) {
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/', $password);
}

/**
 * Valide un nom ou un prénom (lettres, espaces, tirets uniquement)
 */
function validerNomPrenom($chaine) {
    return preg_match('/^[A-Za-zÀ-ÿ\s\-]{2,50}$/', $chaine);
}
?>
