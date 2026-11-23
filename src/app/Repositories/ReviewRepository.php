<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\Review;
use mysqli;

/** Contiene le query SQL relative alle recensioni. */
final class ReviewRepository
{
    /** Riceve la connessione al database usata dalle query del repository. */
    public function __construct(private mysqli $connection)
    {
    }

    /** Inserisce una recensione; i vincoli SQL applicano unicità e intervallo del voto. */
    public function create(Review $review): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reviews (user_id, movie_id, rating, content) VALUES (?, ?, ?, ?)'
        );

        try {
            $userId = $review->getUserId();
            $movieId = $review->getMovieId();
            $rating = $review->getRating();
            $content = $review->getContent();
            $statement->bind_param('iiis', $userId, $movieId, $rating, $content);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }

    /** Recupera la recensione di un utente per un film, se presente. */
    public function findByUserAndMovie(int $userId, int $movieId): ?Review
    {
        $statement = $this->connection->prepare(
            'SELECT id, user_id, movie_id, rating, content
             FROM reviews
             WHERE user_id = ? AND movie_id = ?
             LIMIT 1'
        );

        try {
            $statement->bind_param('ii', $userId, $movieId);
            $statement->execute();
            $result = $statement->get_result();
            $reviewData = $result->fetch_assoc();
            $result->free();

            return $reviewData === null ? null : Review::fromDatabaseRow($reviewData);
        } finally {
            $statement->close();
        }
    }

    /** Elimina una recensione tramite il suo identificativo. */
    public function delete(int $reviewId): bool
    {
        $statement = $this->connection->prepare('DELETE FROM reviews WHERE id = ?');

        try {
            $statement->bind_param('i', $reviewId);
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }
}
