<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\User;
use mysqli;

/** Contiene le query di lettura e scrittura relative agli utenti. */
final class UserRepository
{

    public function __construct(private mysqli $connection)
    {
    }

    public function findByIdentifier(string $identifier): ?User
    {
        // query che cerca l'utente dato un username o mail
        $sql = 'SELECT id, username, email, password_hash, role
                FROM users
                WHERE username = ? OR email = ?
                LIMIT 1';
        $statement = $this->connection->prepare($sql);

        try {
            $statement->bind_param('ss', $identifier, $identifier);
            $statement->execute();
            $result = $statement->get_result();
            $userData = $result->fetch_assoc();
            $result->free();

            // recupera le informazioni dell'utente e crea l'oggetto User con queste
            return $userData === null ? null : User::fromDatabaseRow($userData);
        } finally {
            $statement->close();
        }
    }

    public function create(User $user): bool
    {
        $sql = 'INSERT INTO users (
                    username,
                    email,
                    password_hash,
                    first_name,
                    last_name,
                    role
                ) VALUES (?, ?, ?, ?, ?, ?)';
        $statement = $this->connection->prepare($sql);

        try {
            $username = $user->getUsername();
            $email = $user->getEmail();
            $passwordHash = $user->getPasswordHash();
            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $role = $user->getRole();

            $statement->bind_param(
                'ssssss',
                $username,
                $email,
                $passwordHash,
                $firstName,
                $lastName,
                $role
            );
            $statement->execute();

            return $statement->affected_rows === 1;
        } finally {
            $statement->close();
        }
    }
}
