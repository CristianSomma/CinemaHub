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
    <title>Register — CinemaHub</title>
</head>
<body>
    <main>
        <h1>Register to CinemaHub</h1>

        <?php if ($successMessage !== null): ?>
            <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="/actions/register.php" method="post">
            <label for="username-input">Username:</label>
            <input
                type="text"
                name="username"
                id="username-input"
                placeholder="Your unique username..."
                autocomplete="username"
                minlength="3"
                maxlength="30"
                pattern="[A-Za-z0-9_]{3,30}"
                required
            >
            <small>Enter a username from 3 to 30 letters, numbers or underscores.</small>

            <label for="email-input">Email:</label>
            <input
                type="email"
                name="email"
                id="email-input"
                autocomplete="email"
                placeholder="Your personal e-mail..."
                maxlength="254"
                required
            >

            <label for="password-input">Password:</label>
            <input
                type="password"
                name="password"
                id="password-input"
                autocomplete="new-password"
                placeholder="Choose a strong password..."
                minlength="8"
                maxlength="72"
                required
            >
            <small>At least 8 characters.</small>

            <label for="first-name-input">First name:</label>
            <input
                type="text"
                name="first_name"
                id="first-name-input"
                autocomplete="given-name"
                placeholder="Your first name..."
                maxlength="100"
            >

            <label for="last-name-input">Last name:</label>
            <input
                type="text"
                name="last_name"
                id="last-name-input"
                autocomplete="family-name"
                placeholder="Your last name..."
                maxlength="100"
            >

            <label for="role-select">Account type:</label>
            <select id="role-select" name="role" required>
                <option value="" disabled selected>Choose an account type...</option>
                <option value="USER">User</option>
                <option value="CRITIC">Movie critic</option>
            </select>
            <small>Critics can publish reviews with a verified badge.</small>

            <button type="submit">Create account</button>
        </form>

        <p>Already registered? <a href="/login.php">Log in</a>.</p>
    </main>
</body>
</html>
