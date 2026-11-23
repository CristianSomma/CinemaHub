<?php
declare(strict_types=1);

namespace CinemaHub\Support;

final class InputSanitizer
{

    public static function sanitize(mixed $formInput, bool $isPassword = false): string
    {
        // se l'input del form non è una stringa, allora viene sostituito il valore
        // da una stringa vuota.
        if (!is_string($formInput)) {
            return '';
        }

        // se è una password, non si tolgono gli spazi ad inizio e fine (altrimenti
        // si modificherebbe la password).
        return $isPassword ? $formInput : trim($formInput);
    }

    public static function nullableString(mixed $formInput): ?string
    {
        // sanifica l'input del form, e lo trasforma nel caso fosse stringa vuota
        // in null per renderlo coerente con il valore richiesto dal database.
        $normalizedValue = self::sanitize($formInput);
        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
