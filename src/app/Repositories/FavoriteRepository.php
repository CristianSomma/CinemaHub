<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\Favorite;
use mysqli;

/** Contiene le query SQL relative ai film preferiti dagli utenti. */
final class FavoriteRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function create(Favorite $favorite): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)'
        );

        try {
            $userId = $favorite->getUserId();
            $movieId = $favorite->getMovieId();
            $statement->bind_param('ii', $userId, $movieId);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }

    public function delete(int $userId, int $movieId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM favorites WHERE user_id = ? AND movie_id = ?'
        );

        try {
            $statement->bind_param('ii', $userId, $movieId);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }

    public function exists(int $userId, int $movieId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT user_id FROM favorites WHERE user_id = ? AND movie_id = ?'
        );

        try {
            $statement->bind_param('ii', $userId, $movieId);
            $statement->execute();
            $statement->store_result();

            return $statement->num_rows === 1;
        } finally {
            $statement->close();
        }
    }
}
