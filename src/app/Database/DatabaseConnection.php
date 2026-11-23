<?php
declare(strict_types=1);

namespace CinemaHub\Database;

use mysqli;
use mysqli_sql_exception;

final class DatabaseConnection
{
    // NOTE: crea la connessione al database usando i valori presenti nel
    // file .env

    public static function create(): mysqli
    {
        // rendo tutti gli errori causati da sql come eccezioni
        // cosi da poterle gestire come eccezioni
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            $databaseHost = (string) getenv('DB_HOST');
            $databaseUser = (string) getenv('DB_USER');
            $databasePassword = (string) getenv('DB_PASSWORD');
            $databaseName = (string) getenv('DB_NAME');
            $databasePort = (int) getenv('DB_PORT');

            // crea la connesione
            $connection = new mysqli(
                $databaseHost,
                $databaseUser,
                $databasePassword,
                $databaseName,
                $databasePort
            );

            $connection->set_charset('utf8mb4');

            return $connection;

        } catch (mysqli_sql_exception $exception) {
            error_log('Connessione al database non riuscita: ' . $exception->getMessage());

            http_response_code(503);

            // risale di due livelli dalla posizione del file corrente ed esegue
            // il codice nel file 503.php
            require dirname(__DIR__, 2) . '/errors/503.php';
            exit;
        }
    }
}
