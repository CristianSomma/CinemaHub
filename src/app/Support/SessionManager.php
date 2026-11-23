<?php
declare(strict_types=1);

namespace CinemaHub\Support;

use CinemaHub\Models\User;

/** Centralizza l'avvio della sessione, i dati di autenticazione e i messaggi temporanei. */
final class SessionManager
{
    
    // funzione che avvia in modo centralizzato (avviare solo da qui!)
    // la sessione nel caso in cui non sia già attiva
    public static function start(): void
    {
        // se non è ancora stata attivata la sessione la avvia.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function authenticate(User $user): void
    {
        // si avvia la sessione se non era già avviata
        self::start();

        // si rigenera l'id della sessione
        session_regenerate_id(true);

        // si salvano le informazioni principali dell'utente in sessione
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['role'] = $user->getRole();
    }

    public static function isAuthenticated(): bool
    {
        self::start();
        // ritorna true se user_id non è null, ed è quindi stato autenticato.
        return isset($_SESSION['user_id']);
    }

    public static function getUserId(): ?int
    {
        self::start();
        // restituisce nel caso in cui ci sia un utente autenticato l'id altrimenti
        // ritorna null
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /** Memorizza un messaggio temporaneo da mostrare nella pagina successiva. */
    public static function setFlashMessage(string $type, string $message): void
    {
        self::start();
        $_SESSION[$type . '_message'] = $message;
    }

    /** Legge e rimuove un messaggio temporaneo dalla sessione. */
    public static function pullFlashMessage(string $type): ?string
    {
        self::start();
        $message = $_SESSION[$type . '_message'] ?? null;
        unset($_SESSION[$type . '_message']);

        return is_string($message) ? $message : null;
    }

    public static function logout(): void
    {
        self::start();
        // rende $_SESSION un oggetto vuoto per resettare la sessione.
        $_SESSION = [];
        // la sessione corrente viene distrutta completamente
        session_destroy();
    }
}
