<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class Director
{
    public function __construct(
        private ?int $id,
        private string $firstName,
        private string $lastName,
        private string $birthDate,
        private ?string $deathDate,
        private ?string $nationality,
        private ?string $biography,
        private ?string $photoUrl
    ) {
    }

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): string { return $this->firstName; }

    public function getLastName(): string { return $this->lastName; }

    public function getBirthDate(): string { return $this->birthDate; }

    public function getDeathDate(): ?string { return $this->deathDate; }

    public function getNationality(): ?string { return $this->nationality; }

    public function getBiography(): ?string { return $this->biography; }

    public function getPhotoUrl(): ?string { return $this->photoUrl; }

    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['first_name'],
            (string) $row['last_name'],
            (string) $row['birth_date'],
            isset($row['death_date']) ? (string) $row['death_date'] : null,
            isset($row['nationality']) ? (string) $row['nationality'] : null,
            isset($row['biography']) ? (string) $row['biography'] : null,
            isset($row['photo_url']) ? (string) $row['photo_url'] : null
        );
    }
}
