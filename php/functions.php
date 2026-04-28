<?php
require_once __DIR__ . '/config.php';

function safe(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function old(string $key): string
{
    return safe($_POST[$key] ?? '');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function find_user_by_email(string $email): ?array
{
    foreach ($_SESSION['data']['users'] as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

function find_user_by_nin(string $nin): ?array
{
    foreach ($_SESSION['data']['users'] as $user) {
        if ($user['nin'] === $nin) {
            return $user;
        }
    }
    return null;
}

function get_current_user(): ?array
{
    if (empty($_SESSION['user_nin'])) {
        return null;
    }
    return find_user_by_nin($_SESSION['user_nin']);
}

function require_login(int $role = null): array
{
    $user = get_current_user();
    if (!$user) {
        set_flash('error', 'Vous devez vous connecter pour accéder à cette page.');
        redirect('../public%20space/login.php');
    }
    if ($role !== null && (int) $user['role'] !== $role) {
        set_flash('error', 'Accès non autorisé.');
        redirect('../php/error.php?msg=' . urlencode('Accès interdit'));
    }
    return $user;
}

function validate_email(string $email): bool
{
    return preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/', $email) === 1;
}

function validate_phone(string $phone): bool
{
    return preg_match('/^(\+?\d[\d\s\-]{7,15}\d)$/', $phone) === 1;
}

function validate_nin(string $nin): bool
{
    return preg_match('/^\d{18}$/', $nin) === 1;
}

function validate_name(string $value): bool
{
    return preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\' \-]{2,50}$/u', $value) === 1;
}

function validate_password(string $password): bool
{
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password) === 1;
}

function validate_date_of_birth(string $value): bool
{
    $dob = DateTime::createFromFormat('Y-m-d', $value);
    if (!$dob) {
        return false;
    }
    $now = new DateTime();
    $age = $now->diff($dob)->y;
    return $age >= 18 && $age <= 120;
}

function validate_address(string $address): bool
{
    return mb_strlen(trim($address)) >= 10;
}

function validate_signup(array $input, array &$errors): bool
{
    $nin = trim($input['nin'] ?? '');
    $role = trim($input['role'] ?? 'user');
    $firstName = trim($input['firstName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    $fatherName = trim($input['fatherName'] ?? '');
    $grandfatherName = trim($input['grandfatherName'] ?? '');
    $motherName = trim($input['motherName'] ?? '');
    $motherLastName = trim($input['motherLastName'] ?? '');
    $dob = trim($input['dob'] ?? '');
    $address = trim($input['address'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $confirm = $input['confirmPassword'] ?? '';
    $terms = isset($input['terms']);

    if ($nin === '') {
        $errors['nin'] = 'Le NIN est obligatoire.';
    } elseif (!validate_nin($nin)) {
        $errors['nin'] = 'Le NIN doit contenir exactement 18 chiffres.';
    } elseif (find_user_by_nin($nin)) {
        $errors['nin'] = 'Ce NIN est déjà utilisé.';
    }

    if ($firstName === '' || !validate_name($firstName)) {
        $errors['firstName'] = 'Le prénom est invalide ou trop court.';
    }
    if ($lastName === '' || !validate_name($lastName)) {
        $errors['lastName'] = 'Le nom est invalide ou trop court.';
    }
    if ($fatherName === '' || !validate_name($fatherName)) {
        $errors['fatherName'] = 'Le prénom du père est invalide.';
    }
    if ($grandfatherName === '' || !validate_name($grandfatherName)) {
        $errors['grandfatherName'] = 'Le prénom du grand-père est invalide.';
    }
    if ($motherName === '' || !validate_name($motherName)) {
        $errors['motherName'] = 'Le prénom de la mère est invalide.';
    }
    if ($motherLastName === '' || !validate_name($motherLastName)) {
        $errors['motherLastName'] = 'Le nom de la mère est invalide.';
    }
    if ($dob === '' || !validate_date_of_birth($dob)) {
        $errors['dob'] = 'Vous devez avoir au moins 18 ans et fournir une date valide.';
    }
    if ($address === '' || !validate_address($address)) {
        $errors['address'] = 'L’adresse doit contenir au moins 10 caractères.';
    }
    if ($email === '' || !validate_email($email)) {
        $errors['email'] = 'L’adresse email est invalide.';
    } elseif (find_user_by_email($email)) {
        $errors['email'] = 'Cette adresse email est déjà enregistrée.';
    }
    if ($phone === '' || !validate_phone($phone)) {
        $errors['phone'] = 'Le numéro de téléphone est invalide.';
    }
    if ($password === '' || !validate_password($password)) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre.';
    }
    if ($confirm === '' || $confirm !== $password) {
        $errors['confirmPassword'] = 'La confirmation du mot de passe ne correspond pas.';
    }
    if (!$terms) {
        $errors['terms'] = 'Vous devez accepter les conditions générales.';
    }

    if (!in_array($role, ['user', 'admin'], true)) {
        $errors['role'] = 'Rôle invalide.';
    }

    return empty($errors);
}

function map_role(string $role): int
{
    return $role === 'admin' ? 1 : 2;
}

function add_user(array $data): void
{
    $_SESSION['data']['users'][] = [
        'nin' => $data['nin'],
        'nom' => $data['lastName'],
        'prenom' => $data['firstName'],
        'prenom_pere' => $data['fatherName'],
        'nom_mere' => $data['motherLastName'],
        'prenom_mere' => $data['motherName'],
        'date_naiss' => $data['dob'],
        'adresse' => $data['address'],
        'email' => $data['email'],
        'tel' => $data['phone'],
        'psswd' => password_hash($data['password'], PASSWORD_DEFAULT),
        'etat_compte' => 3,
        'role' => map_role($data['role']),
    ];
}

function validate_login(array $input, array &$errors): ?array
{
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if ($email === '' || !validate_email($email)) {
        $errors['email'] = 'Veuillez saisir un email valide.';
    }
    if ($password === '') {
        $errors['password'] = 'Le mot de passe est obligatoire.';
    }
    if (!empty($errors)) {
        return null;
    }

    $user = find_user_by_email($email);
    if (!$user || !password_verify($password, $user['psswd'])) {
        $errors['general'] = 'Identifiants incorrects.';
        return null;
    }
    if ($user['etat_compte'] === 2) {
        $errors['general'] = 'Votre compte est bloqué.';
        return null;
    }
    if ($user['etat_compte'] === 4) {
        $errors['general'] = 'Ce compte a été supprimé.';
        return null;
    }
    if ($user['etat_compte'] === 3) {
        $errors['general'] = 'Votre compte est en attente de validation.';
        return null;
    }

    return $user;
}

function update_user(string $nin, array $updated): void
{
    foreach ($_SESSION['data']['users'] as &$user) {
        if ($user['nin'] === $nin) {
            foreach ($updated as $key => $value) {
                if ($key === 'psswd') {
                    $user['psswd'] = password_hash($value, PASSWORD_DEFAULT);
                } elseif (array_key_exists($key, $user)) {
                    $user[$key] = $value;
                }
            }
            break;
        }
    }
    unset($user);
}

function get_all_lotteries(): array
{
    return $_SESSION['data']['tirages'];
}

function get_lottery_by_id(int $id): ?array
{
    foreach ($_SESSION['data']['tirages'] as $tirage) {
        if ($tirage['id_tirage'] === $id) {
            return $tirage;
        }
    }
    return null;
}

function get_registrations_count(int $id_tirage): int
{
    $count = 0;
    foreach ($_SESSION['data']['inscrits'] as $inscription) {
        if ($inscription['id_tirage'] === $id_tirage) {
            $count++;
        }
    }
    return $count;
}

function is_user_registered(string $nin, int $id_tirage): bool
{
    foreach ($_SESSION['data']['inscrits'] as $inscription) {
        if ($inscription['nin'] === $nin && $inscription['id_tirage'] === $id_tirage) {
            return true;
        }
    }
    return false;
}

function can_join_lottery(array $tirage): bool
{
    return $tirage['etat_tirage'] === 3;
}

function join_lottery(string $nin, int $id_tirage, array &$errors): bool
{
    $tirage = get_lottery_by_id($id_tirage);
    if (!$tirage) {
        $errors[] = 'Tirage introuvable.';
        return false;
    }
    if (!can_join_lottery($tirage)) {
        $errors[] = 'Les inscriptions pour ce tirage ne sont pas ouvertes.';
        return false;
    }
    if (is_user_registered($nin, $id_tirage)) {
        $errors[] = 'Vous êtes déjà inscrit à ce tirage.';
        return false;
    }
    $next = count($_SESSION['data']['inscrits']) + 1;
    $_SESSION['data']['inscrits'][] = [
        'id_inscription' => $next,
        'nin' => $nin,
        'id_tirage' => $id_tirage,
        'date_inscription' => (new DateTime())->format('Y-m-d'),
    ];
    add_notification($nin, "Vous êtes inscrit au tirage #{$id_tirage}.");
    return true;
}

function add_notification(string $nin, string $message): void
{
    $_SESSION['data']['notifications'][] = [
        'id_notification' => count($_SESSION['data']['notifications']) + 1,
        'nin' => $nin,
        'message' => $message,
        'etat_notification' => 1,
    ];
}

function get_user_notifications(string $nin): array
{
    $result = [];
    foreach ($_SESSION['data']['notifications'] as $notification) {
        if ($notification['nin'] === $nin) {
            $result[] = $notification;
        }
    }
    return $result;
}

function mark_notification_read(int $id): void
{
    foreach ($_SESSION['data']['notifications'] as &$notification) {
        if ($notification['id_notification'] === $id) {
            $notification['etat_notification'] = 2;
            break;
        }
    }
    unset($notification);
}

function user_pending_count(): int
{
    $count = 0;
    foreach ($_SESSION['data']['users'] as $user) {
        if ($user['etat_compte'] === 3 && $user['role'] === 2) {
            $count++;
        }
    }
    return $count;
}

function get_users(array $filters = []): array
{
    $results = [];
    foreach ($_SESSION['data']['users'] as $user) {
        if ($user['role'] !== 2) {
            continue;
        }
        if (!empty($filters['nin']) && strpos($user['nin'], $filters['nin']) === false) {
            continue;
        }
        if (!empty($filters['email']) && stripos($user['email'], $filters['email']) === false) {
            continue;
        }
        $results[] = $user;
    }
    return $results;
}

function change_user_status(string $nin, int $etat): bool
{
    foreach ($_SESSION['data']['users'] as &$user) {
        if ($user['nin'] === $nin) {
            $user['etat_compte'] = $etat;
            unset($user);
            return true;
        }
    }
    return false;
}

function delete_user(string $nin): bool
{
    foreach ($_SESSION['data']['users'] as $index => $user) {
        if ($user['nin'] === $nin) {
            array_splice($_SESSION['data']['users'], $index, 1);
            return true;
        }
    }
    return false;
}

function get_results(): array
{
    return $_SESSION['data']['results'];
}

function add_result(string $nin, int $id_tirage): void
{
    foreach ($_SESSION['data']['results'] as $result) {
        if ($result['nin'] === $nin && $result['id_tirage'] === $id_tirage) {
            return;
        }
    }
    $_SESSION['data']['results'][] = [
        'id_resultat' => count($_SESSION['data']['results']) + 1,
        'nin' => $nin,
        'id_tirage' => $id_tirage,
    ];
}

function draw_winners(int $id_tirage, array &$errors): bool
{
    $tirage = get_lottery_by_id($id_tirage);
    if (!$tirage) {
        $errors[] = 'Tirage introuvable.';
        return false;
    }
    if ($tirage['etat_tirage'] !== 4) {
        $errors[] = 'Le tirage doit être fermé pour pouvoir effectuer le tirage.';
        return false;
    }
    $candidates = [];
    foreach ($_SESSION['data']['inscrits'] as $inscription) {
        if ($inscription['id_tirage'] === $id_tirage) {
            $candidates[] = $inscription['nin'];
        }
    }
    if (count($candidates) === 0) {
        $errors[] = 'Aucun participant pour ce tirage.';
        return false;
    }
    $winners = [];
    if (count($candidates) <= $tirage['nbr_gagnants']) {
        $winners = $candidates;
    } else {
        $keys = array_rand($candidates, $tirage['nbr_gagnants']);
        if (is_int($keys)) {
            $winners[] = $candidates[$keys];
        } else {
            foreach ($keys as $key) {
                $winners[] = $candidates[$key];
            }
        }
    }
    foreach ($winners as $winnerNin) {
        add_result($winnerNin, $id_tirage);
        add_notification($winnerNin, "Félicitations ! Vous avez été sélectionné(e) pour le tirage #{$id_tirage}.");
    }
    set_lottery_state($id_tirage, 2);
    return true;
}

function set_lottery_state(int $id_tirage, int $etat): void
{
    foreach ($_SESSION['data']['tirages'] as &$tirage) {
        if ($tirage['id_tirage'] === $id_tirage) {
            $tirage['etat_tirage'] = $etat;
            break;
        }
    }
    unset($tirage);
}
