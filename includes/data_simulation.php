<?php
// includes/data_simulation.php

// Simulation de la table User
// etat_compte: 1 (actif), 2 (bloqué), 3 (en attente), 4 (supprimé)
// role: 1 (admin), 2 (simple user)
$users_db = [
    [
        'nin' => '123456789012345678',
        'nom' => 'Admin',
        'prenom' => 'Super',
        'email' => 'admin@hadj.dz',
        'psswd' => password_hash('Admin123!', PASSWORD_DEFAULT),
        'etat_compte' => 1,
        'role' => 1
    ],
    [
        'nin' => '987654321098765432',
        'nom' => 'User',
        'prenom' => 'Simple',
        'email' => 'user@hadj.dz',
        'psswd' => password_hash('User123!', PASSWORD_DEFAULT),
        'etat_compte' => 1,
        'role' => 2
    ]
];

// Simulation de la table Tirage
// etat_tirage: 1 (planifié), 2 (effectué), 3 (inscriptions ouvertes), 4 (inscriptions fermées)
$tirages_db = [
    [
        'id_tirage' => 1,
        'date_ouverture_insc' => '2024-01-01',
        'date_cloture_insc' => '2024-03-01',
        'date_tirage' => '2024-04-01',
        'nbr_gagnants' => 100,
        'etat_tirage' => 3
    ]
];

// Inscrits (id_inscription, nin, id_tirage, date_inscription)
$inscrits_db = [];

// Resultats (id_resultat, nin, id_tirage)
$resultats_db = [];

// Notifications (id_notification, nin, message, etat_notification)
// etat_notification: 1 (non lue), 2 (lue)
$notifications_db = [];

// Fonction pour simuler la recherche d'un utilisateur
function getUserByEmail($email) {
    global $users_db;
    foreach ($users_db as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}
?>
