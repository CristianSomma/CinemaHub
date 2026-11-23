<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class Favorite
{
    public function __construct(private int $userId, private int $movieId)
    {
    }

    public function getUserId(): int { return $this->userId; }

    public function getMovieId(): int { return $this->movieId; }
}
