<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class Genre
{
    public function __construct(private ?int $id, private string $name)
    {
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }

    public static function fromDatabaseRow(array $row): self
    {
        return new self(isset($row['id']) ? (int) $row['id'] : null, (string) $row['name']);
    }
}
