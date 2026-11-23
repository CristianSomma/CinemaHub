<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Support\SessionManager;

$successMessage = SessionManager::pullFlashMessage('success');
$errorMessage = SessionManager::pullFlashMessage('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CinemaHub</title>
</head>
<body>
    <main>
        <h1>Log in to CinemaHub</h1>

        <?php if ($successMessage !== null): ?>
            <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="/actions/login.php" method="post">
            <label for="identifier-input">Email or username:</label>
            <input
                type="text"
                name="identifier"
                id="identifier-input"
                placeholder="Your e-mail or username..."
                autocomplete="username"
                maxlength="254"
                required
                autofocus
            >
            <small>You can log in with either your e-mail or your username.</small>

            <label for="password-input">Password:</label>
            <input
                type="password"
                name="password"
                id="password-input"
                placeholder="Your password..."
                autocomplete="current-password"
                required
            >

            <button type="submit">Log in</button>
        </form>

        <p>
            Don't have an account?
            <a href="/register.php">Create one</a>
        </p>
    </main>
</body>
</html>
