<?php
session_start();

if (!isset($_SESSION['data_initialized'])) {
    $_SESSION['data'] = [
        'users' => [
            [
                'nin' => '000000000000000001',
                'nom' => 'Admin',
                'prenom' => 'Super',
                'prenom_pere' => 'Mohamed',
                'nom_mere' => 'Amina',
                'prenom_mere' => 'Fatima',
                'date_naiss' => '1980-01-01',
                'adresse' => 'Rue Admin, Alger',
                'email' => 'admin@hajj.local',
                'tel' => '+213600000001',
                'psswd' => password_hash('Admin1234', PASSWORD_DEFAULT),
                'etat_compte' => 1,
                'role' => 1,
            ],
            [
                'nin' => '123456789012345678',
                'nom' => 'Bensalem',
                'prenom' => 'Karim',
                'prenom_pere' => 'Ahmed',
                'nom_mere' => 'Yasmine',
                'prenom_mere' => 'Sofia',
                'date_naiss' => '1995-04-10',
                'adresse' => 'Boulevard de la Paix, Oran',
                'email' => 'karim.b@example.com',
                'tel' => '+213550123456',
                'psswd' => password_hash('User12345', PASSWORD_DEFAULT),
                'etat_compte' => 1,
                'role' => 2,
            ],
            [
                'nin' => '234567890123456789',
                'nom' => 'Khelil',
                'prenom' => 'Amina',
                'prenom_pere' => 'Rachid',
                'nom_mere' => 'Nadia',
                'prenom_mere' => 'Layla',
                'date_naiss' => '1993-08-22',
                'adresse' => 'Rue du Thé, Alger',
                'email' => 'amina.k@example.com',
                'tel' => '+213660987654',
                'psswd' => password_hash('Amina2024', PASSWORD_DEFAULT),
                'etat_compte' => 3,
                'role' => 2,
            ],
        ],
        'tirages' => [
            [
                'id_tirage' => 1,
                'date_ouverture_insc' => '2025-01-01',
                'date_cloture_insc' => '2025-12-31',
                'date_tirage' => '2026-08-15',
                'nbr_gagnants' => 5,
                'etat_tirage' => 3,
            ],
            [
                'id_tirage' => 2,
                'date_ouverture_insc' => '2026-01-01',
                'date_cloture_insc' => '2026-12-31',
                'date_tirage' => '2027-08-20',
                'nbr_gagnants' => 5,
                'etat_tirage' => 4,
            ],
            [
                'id_tirage' => 3,
                'date_ouverture_insc' => '2027-01-01',
                'date_cloture_insc' => '2027-12-31',
                'date_tirage' => '2028-08-25',
                'nbr_gagnants' => 5,
                'etat_tirage' => 1,
            ],
        ],
        'inscrits' => [
            [
                'id_inscription' => 1,
                'nin' => '123456789012345678',
                'id_tirage' => 1,
                'date_inscription' => '2025-04-10',
            ],
        ],
        'results' => [
            [
                'id_resultat' => 1,
                'nin' => '123456789012345678',
                'id_tirage' => 1,
            ],
        ],
        'notifications' => [
            [
                'id_notification' => 1,
                'nin' => '123456789012345678',
                'message' => 'Votre inscription au tirage Hajj 2026 a été validée.',
                'etat_notification' => 1,
            ],
        ],
    ];
    $_SESSION['data_initialized'] = true;
}
