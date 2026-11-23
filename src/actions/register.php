<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Repositories\UserRepository;
use CinemaHub\Services\AuthService;
use CinemaHub\Support\InputSanitizer;
use CinemaHub\Support\SessionManager;
use CinemaHub\Support\Validator;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$username = InputSanitizer::sanitize($_POST['username'] ?? null);
$email = InputSanitizer::sanitize($_POST['email'] ?? null);
$password = InputSanitizer::sanitize($_POST['password'] ?? null, true);
$firstName = InputSanitizer::nullableString($_POST['first_name'] ?? null);
$lastName = InputSanitizer::nullableString($_POST['last_name'] ?? null);
$role = InputSanitizer::sanitize($_POST['role'] ?? null);

$isRegistrationValid = Validator::isUsernameValid($username)
    && Validator::isEmailValid($email)
    && Validator::isPasswordValid($password)
    && Validator::isFirstNameValid($firstName)
    && Validator::isLastNameValid($lastName)
    && Validator::isRoleValid($role);

// se la registrazione non è valida ricarica la pagina con un messaggio di errore
if (!$isRegistrationValid) {
    SessionManager::setFlashMessage('error', 'Invalid registration data. Please check all the fields.');
    header('Location: /register.php');
    exit;
}

try {
    $connection = require __DIR__ . '/../config/database.php';
    $userRepository = new UserRepository($connection);
    $authenticationService = new AuthService($userRepository);

    // effettua la registrazione dell'utente
    $authenticationService->register(
        $username,
        $email,
        $password,
        $firstName,
        $lastName,
        $role
    );

    SessionManager::setFlashMessage(
        'success',
        'Account created successfully. You can now log in.'
    );
    header('Location: /login.php');
    exit;
} catch (\InvalidArgumentException $exception) {
    SessionManager::setFlashMessage('error', $exception->getMessage());
    header('Location: /register.php');
    exit;
} catch (\mysqli_sql_exception $exception) {
    error_log('Errore SQL durante la registrazione: ' . $exception->getMessage());

    if ((int) $exception->getCode() === 1062) {
        $message = 'Username or email already in use.';
    } else {
        $message = 'An unexpected database error occurred during registration.';
    }

    SessionManager::setFlashMessage('error', $message);
    header('Location: /register.php');
    exit;
} catch (\Throwable $exception) {
    error_log('Errore durante la registrazione: ' . $exception->getMessage());
    SessionManager::setFlashMessage('error', 'An unexpected error occurred during registration.');
    header('Location: /register.php');
    exit;
}
