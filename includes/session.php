<?php
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['user_nin']);
}

// Fonction pour vérifier si l'utilisateur est Admin
function estAdmin() {
    return estConnecte() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
}

// Fonction pour vérifier si l'utilisateur est un simple citoyen (User)
function estSimpleUser() {
    return estConnecte() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2;
}

// Rediriger vers la page d'erreur si accès refusé
function redirigerSiNonAutorise($role_requis = null) {
    if (!estConnecte()) {
        header('Location: ../public space/login.php');
        exit();
    }
    
    if ($role_requis === 'admin' && !estAdmin()) {
        header('Location: ../error.php?code=403');
        exit();
    }
    
    if ($role_requis === 'user' && !estSimpleUser()) {
        header('Location: ../error.php?code=403');
        exit();
    }
}
?>
