<?php
declare(strict_types=1);

namespace CinemaHub\Models;

final class User
{

    public function __construct(
        private ?int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
        private ?string $firstName,
        private ?string $lastName,
        private string $role
    ) {
    }

    public function getId(): ?int { return $this->id; }

    public function getUsername(): string { return $this->username; }

    public function getEmail(): string { return $this->email; }

    public function getPasswordHash(): string { return $this->passwordHash; }

    public function getFirstName(): ?string { return $this->firstName; }

    public function getLastName(): ?string { return $this->lastName; }

    public function getRole(): string { return $this->role; }

    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['username'],
            (string) $row['email'],
            (string) $row['password_hash'],
            isset($row['first_name']) ? (string) $row['first_name'] : null,
            isset($row['last_name']) ? (string) $row['last_name'] : null,
            (string) $row['role']
        );
    }
}
