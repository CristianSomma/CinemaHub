<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\Director;
use mysqli;

/** Contiene le query SQL relative ai registi. */
final class DirectorRepository
{
    /** Riceve la connessione al database usata dalle query del repository. */
    public function __construct(private mysqli $connection)
    {
    }

    /** Verifica l'esistenza di un regista tramite il suo identificativo. */
    public function existsById(int $directorId): bool
    {
        $statement = $this->connection->prepare('SELECT id FROM directors WHERE id = ?');

        try {
            $statement->bind_param('i', $directorId);
            $statement->execute();
            $statement->store_result();

            return $statement->num_rows === 1;
        } finally {
            $statement->close();
        }
    }

    /** Recupera un regista completo oppure null se l'identificativo non esiste. */
    public function findById(int $directorId): ?Director
    {
        $sql = 'SELECT id, first_name, last_name, birth_date, death_date,
                       nationality, biography, photo_url
                FROM directors
                WHERE id = ?
                LIMIT 1';
        $statement = $this->connection->prepare($sql);

        try {
            $statement->bind_param('i', $directorId);
            $statement->execute();
            $result = $statement->get_result();
            $directorData = $result->fetch_assoc();
            $result->free();

            return $directorData === null ? null : Director::fromDatabaseRow($directorData);
        } finally {
            $statement->close();
        }
    }
}
