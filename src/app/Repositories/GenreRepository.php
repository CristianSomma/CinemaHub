<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\Genre;
use mysqli;

/** Contiene le query SQL relative ai generi cinematografici. */
final class GenreRepository
{
    /** Riceve la connessione al database usata dalle query del repository. */
    public function __construct(private mysqli $connection)
    {
    }

    /** Recupera tutti i generi in ordine alfabetico. */
    public function findAll(): array
    {
        $result = $this->connection->query('SELECT id, name FROM genres ORDER BY name');

        try {
            $genres = [];
            while ($genreData = $result->fetch_assoc()) {
                $genres[] = Genre::fromDatabaseRow($genreData);
            }

            return $genres;
        } finally {
            $result->free();
        }
    }

    /** Recupera un genere tramite identificativo oppure null se non esiste. */
    public function findById(int $genreId): ?Genre
    {
        $statement = $this->connection->prepare('SELECT id, name FROM genres WHERE id = ? LIMIT 1');

        try {
            $statement->bind_param('i', $genreId);
            $statement->execute();
            $result = $statement->get_result();
            $genreData = $result->fetch_assoc();
            $result->free();

            return $genreData === null ? null : Genre::fromDatabaseRow($genreData);
        } finally {
            $statement->close();
        }
    }
}
