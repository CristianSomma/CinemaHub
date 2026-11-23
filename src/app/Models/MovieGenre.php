<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class MovieGenre
{
    public function __construct(private int $movieId, private int $genreId)
    {
    }

    public function getMovieId(): int { return $this->movieId; }

    public function getGenreId(): int { return $this->genreId; }
}
