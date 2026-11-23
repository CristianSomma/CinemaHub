<?php
declare(strict_types=1);

namespace CinemaHub\Models;

/** Rappresenta un film del catalogo CinemaHub. */
final class Movie
{
    // costruttore, compreso dei campi nullable come definito
    // dal database
    public function __construct(
        private ?int $id,
        private string $title,
        private int $directorId,
        private int $releaseYear,
        private int $durationMinutes,
        private ?string $usaRating,
        private ?string $italianRating,
        private ?string $description,
        private ?string $posterPath
    ) {
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }

    public function getDirectorId(): int { return $this->directorId; }

    public function getReleaseYear(): int { return $this->releaseYear; }

    public function getDurationMinutes(): int { return $this->durationMinutes; }

    public function getUsaRating(): ?string { return $this->usaRating; }

    public function getItalianRating(): ?string { return $this->italianRating; }

    public function getDescription(): ?string { return $this->description; }

    public function getPosterPath(): ?string { return $this->posterPath; }

    public static function fromDatabaseRow(array $row): self
    {
        // metodo statico che ritorna un nuovo film ottenuto dai valori
        // recuperati dal database

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['title'],
            (int) $row['director_id'],
            (int) $row['release_year'],
            (int) $row['duration_minutes'],
            isset($row['usa_rating']) ? (string) $row['usa_rating'] : null,
            isset($row['it_rating']) ? (string) $row['it_rating'] : null,
            isset($row['description']) ? (string) $row['description'] : null,
            isset($row['poster_url']) ? (string) $row['poster_url'] : null
        );
    }
}
