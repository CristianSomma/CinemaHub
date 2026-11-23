<?php
declare(strict_types=1);

namespace CinemaHub\Services;

use CinemaHub\Exceptions\AuthenticationException;
use CinemaHub\Models\User;
use CinemaHub\Repositories\UserRepository;
use CinemaHub\Support\SessionManager;
use CinemaHub\Support\Validator;

final class AuthService
{
    // richiede l'oggetto responsabile della comunicazione con il database
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function register(
        string $username,
        string $email,
        string $password,
        ?string $firstName,
        ?string $lastName,
        string $role
    ): bool {

        // controlla se i valori passati rispettano i formati validi per la registrazione
        $isRegistrationValid = Validator::isUsernameValid($username)
            && Validator::isEmailValid($email)
            && Validator::isPasswordValid($password)
            && Validator::isFirstNameValid($firstName)
            && Validator::isLastNameValid($lastName)
            && Validator::isRoleValid($role);

        // se i valori non rispettano i requisiti di registrazione
        // lancia un'eccezione
        if (!$isRegistrationValid) {
            throw new \InvalidArgumentException(
                'Invalid registration data. Please check all the fields.'
            );
        }

        // effettua l'hash della password per salvarla nel database
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // se l'hash della password fallisce lancia un'eccezione
        if (!is_string($passwordHash)) {
            throw new \RuntimeException('Unable to secure the password.');
        }

        // crea un nuovo user, con id null (è autogenerato dal db)
        $user = new User(
            null,
            $username,
            $email,
            $passwordHash,
            $firstName,
            $lastName,
            $role
        );

        // inserisce nel database l'utente creato, se fallisce allora
        // viene lanciata un'eccezione
        if (!$this->userRepository->create($user)) {
            throw new \RuntimeException('The user account could not be created.');
        }

        return true;
    }

    public function login(string $identifier, string $password): User
    {
        // verifica che l'identificatore per cui recuperare il record
        // dal database sia uno username o una mail valida
        $isIdentifierValid = Validator::isUsernameValid($identifier)
            || Validator::isEmailValid($identifier);

        // se l'identificatore non è valido o la password non è valida lancia un'
        // eccezione.
        if (!$isIdentifierValid || !Validator::isPasswordValid($password)) {
            throw new \InvalidArgumentException(
                'Invalid input. Please check your credentials.'
            );
        }

        // recupera l'utente dall'identificatore.
        $user = $this->userRepository->findByIdentifier($identifier);

        // se non trova un record (null) oppure la password non è la stessa
        // allora lancia un'eccezione personalizzata
        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            throw new AuthenticationException('Invalid username/email or password.');
        }

        // salva le informazioni dell'utente loggato nella sessione corrente.
        SessionManager::authenticate($user);

        return $user;
    }
}
