<?php
declare(strict_types=1);

/*
 * Punto di avvio condiviso dell'applicazione.
 * Carica automaticamente le classi del progetto e avvia la sessione.
 * GENERATO DA CHATGPT
 */

spl_autoload_register(static function (string $className): void {
    $namespacePrefix = 'CinemaHub\\';

    if (!str_starts_with($className, $namespacePrefix)) {
        return;
    }

    $relativeClassName = substr($className, strlen($namespacePrefix));
    $classFile = __DIR__ . '/app/' . str_replace('\\', '/', $relativeClassName) . '.php';

    if (is_file($classFile)) {
        require_once $classFile;
    }
});

\CinemaHub\Support\SessionManager::start();
