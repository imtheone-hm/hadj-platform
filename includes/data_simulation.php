<?php

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

$inscrits_db = [];

$resultats_db = [];

$notifications_db = [];

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
