<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Exceptions\AuthenticationException;
use CinemaHub\Repositories\UserRepository;
use CinemaHub\Services\AuthService;
use CinemaHub\Support\InputSanitizer;
use CinemaHub\Support\SessionManager;
use CinemaHub\Support\Validator;

// verifica che il metodo HTTP usato per l'invio del form sia POST
// altrimenti lancia un errore.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

// sanifica i valori ottenuti dal form
$identifier = InputSanitizer::sanitize($_POST['identifier'] ?? null);
$password = InputSanitizer::sanitize($_POST['password'] ?? null, true);

// controlla se l'identificatore dell'utente è uno username o una mail valida
$isIdentifierValid = Validator::isUsernameValid($identifier)
    || Validator::isEmailValid($identifier);

// controlla se l'identificatore per il login non è valido e se la password
// non rispetta il formato richiesto, in tal caso salva un messaggio e ricarica la pagina
if (!$isIdentifierValid || !Validator::isPasswordValid($password)) {
    SessionManager::setFlashMessage('error', 'Invalid input. Please check your credentials.');
    header('Location: /login.php');
    exit;
}

try {

    // recupera la connessione al database
    $connection = require __DIR__ . '/../config/database.php';
    // inizializza gli oggetti che si occupano delle query e dell'autenticazione
    $userRepository = new UserRepository($connection);
    $authenticationService = new AuthService($userRepository);

    // effettua il login
    $user = $authenticationService->login($identifier, $password);

    SessionManager::setFlashMessage(
        'success',
        'Welcome back, ' . $user->getUsername() . '!'
    );

    header('Location: /');
    exit;
} catch (\InvalidArgumentException $exception) {
    SessionManager::setFlashMessage('error', $exception->getMessage());
    header('Location: /login.php');
    exit;
} catch (AuthenticationException $exception) {
    SessionManager::setFlashMessage('error', $exception->getMessage());
    header('Location: /login.php');
    exit;
} catch (\Throwable $exception) {
    error_log('Errore durante il login: ' . $exception->getMessage());
    SessionManager::setFlashMessage('error', 'An unexpected error occurred during login.');
    header('Location: /login.php');
    exit;
}
