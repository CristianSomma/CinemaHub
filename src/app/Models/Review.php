<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class Review
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $movieId,
        private int $rating,
        private ?string $content
    ) {
    }

    public function getId(): ?int { return $this->id; }

    public function getUserId(): int { return $this->userId; }

    public function getMovieId(): int { return $this->movieId; }

    public function getRating(): int { return $this->rating; }

    public function getContent(): ?string { return $this->content; }

    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['user_id'],
            (int) $row['movie_id'],
            (int) $row['rating'],
            isset($row['content']) ? (string) $row['content'] : null
        );
    }
}
