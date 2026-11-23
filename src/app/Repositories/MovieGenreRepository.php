<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\MovieGenre;
use mysqli;

/** Gestisce la relazione molti-a-molti tra film e generi. */
final class MovieGenreRepository
{
    /** Riceve la connessione al database usata dalle query del repository. */
    public function __construct(private mysqli $connection)
    {
    }

    /** Associa un genere a un film. I vincoli del database impediscono duplicati o riferimenti inesistenti. */
    public function create(MovieGenre $movieGenre): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO movie_genre (movie_id, genre_id) VALUES (?, ?)'
        );

        try {
            $movieId = $movieGenre->getMovieId();
            $genreId = $movieGenre->getGenreId();
            $statement->bind_param('ii', $movieId, $genreId);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }

    /** Rimuove l'associazione tra uno specifico film e uno specifico genere. */
    public function delete(int $movieId, int $genreId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM movie_genre WHERE movie_id = ? AND genre_id = ?'
        );

        try {
            $statement->bind_param('ii', $movieId, $genreId);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }

    /** Restituisce gli identificativi dei generi associati a un film. */
    public function findGenreIdsByMovie(int $movieId): array
    {
        $statement = $this->connection->prepare(
            'SELECT genre_id FROM movie_genre WHERE movie_id = ? ORDER BY genre_id'
        );

        try {
            $statement->bind_param('i', $movieId);
            $statement->execute();
            $result = $statement->get_result();
            $genreIds = [];

            while ($row = $result->fetch_assoc()) {
                $genreIds[] = (int) $row['genre_id'];
            }

            $result->free();
            return $genreIds;
        } finally {
            $statement->close();
        }
    }
}
