<?php
declare(strict_types=1);

namespace CinemaHub\Support;

final class Validator
{
    // Verifica che lo username contenga da 3 a 30 caratteri 
    // alfanumerici o underscore con una regex
    public static function isUsernameValid(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) === 1;
    }

    // Verifica il formato dell'email e il limite massimo di 254 caratteri.
    public static function isEmailValid(string $email): bool
    {
        return mb_strlen($email, 'UTF-8') <= 254
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Verifica che la password contenga da 8 a 72 caratteri, come previsto dal form originale.
    public static function isPasswordValid(string $password): bool
    {
        $passwordLength = mb_strlen($password, 'UTF-8');
        return $passwordLength >= 8 && $passwordLength <= 72;
    }

    // Verifica la lunghezza massima del nome facoltativo
    public static function isFirstNameValid(?string $firstName): bool
    {
        return $firstName === null || mb_strlen($firstName, 'UTF-8') <= 100;
    }

    // Verifica la lunghezza massima del cognome facoltativo.
    public static function isLastNameValid(?string $lastName): bool
    {
        return $lastName === null || mb_strlen($lastName, 'UTF-8') <= 100;
    }

    // Accetta USER e CRITIC durante la registrazione, senza consentire la scelta di ADMIN
    public static function isRoleValid(string $role): bool
    {
        return in_array($role, ['USER', 'CRITIC'], true);
    }

    // Converte un valore in intero oppure solleva un'eccezione se il formato non è valido.
    public static function parseInteger(mixed $value): int
    {
        $parsedValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($parsedValue === false) {
            throw new \InvalidArgumentException('The provided value is not a valid integer.');
        }

        return $parsedValue;
    }

    // Verifica che la classificazione statunitense sia tra i valori previsti dal database.
    public static function isUsaRatingValid(string $rating): bool
    {
        return in_array($rating, ['G', 'PG', 'PG-13', 'R', 'NC-17'], true);
    }

    // Verifica che la classificazione italiana sia tra i valori previsti dal database.
    public static function isItalianRatingValid(string $rating): bool
    {
        return in_array($rating, ['T', '6', '10', '14', '18'], true);
    }
}
