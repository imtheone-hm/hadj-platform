<?php
require_once __DIR__ . '/functions.php';
unset($_SESSION['user_nin']);
set_flash('success', 'Vous êtes déconnecté.');
redirect('../public%20space/login.php');
