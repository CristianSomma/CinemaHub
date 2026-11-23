<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Support\SessionManager;

$successMessage = SessionManager::pullFlashMessage('success');
$errorMessage = SessionManager::pullFlashMessage('error');
$isAuthenticated = SessionManager::isAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaHub</title>
</head>
<body>
    <main>
        <h1>Welcome to CinemaHub</h1>

        <?php if ($successMessage !== null): ?>
            <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($isAuthenticated): ?>
            <p>
                You are logged in as
                <?= htmlspecialchars((string) $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>.
            </p>
            <a href="/new_movie.php">Add a movie</a>
        <?php else: ?>
            <a href="/login.php">Log in</a>
            <a href="/register.php">Register</a>
        <?php endif; ?>
    </main>
</body>
</html>
