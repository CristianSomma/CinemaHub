<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Support\SessionManager;

if (!SessionManager::isAuthenticated()) {
    SessionManager::setFlashMessage('error', 'Authentication required.');
    header('Location: /login.php');
    exit;
}

$successMessage = SessionManager::pullFlashMessage('success');
$errorMessage = SessionManager::pullFlashMessage('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie | CinemaHub</title>
</head>
<body>
    <header>
        <h1>Add a new movie</h1>
        <p>Enter the information below to add a movie to CinemaHub.</p>
    </header>

    <main>
        <?php if ($successMessage !== null): ?>
            <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="/actions/new_movie.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="title">Movie title</label>
                <small>Enter the official title of the movie (maximum 255 characters).</small>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="e.g. Inception"
                    maxlength="255"
                    required
                >
            </div>

            <div>
                <label for="director_id">Director ID</label>
                <small>
                    Enter the ID of the movie director. This field will later be replaced
                    with a director selection menu.
                </small>
                <input
                    type="number"
                    id="director_id"
                    name="director_id"
                    placeholder="e.g. 1"
                    min="1"
                    step="1"
                    required
                >
            </div>

            <div>
                <label for="release_year">Release year</label>
                <small>Enter the year in which the movie was originally released.</small>
                <input
                    type="number"
                    id="release_year"
                    name="release_year"
                    placeholder="e.g. 2010"
                    min="1850"
                    max="<?= (int) date('Y') ?>"
                    step="1"
                    required
                >
            </div>

            <div>
                <label for="duration_minutes">Duration (minutes)</label>
                <small>Enter the total running time of the movie in minutes.</small>
                <input
                    type="number"
                    id="duration_minutes"
                    name="duration_minutes"
                    placeholder="e.g. 148"
                    min="1"
                    max="65535"
                    step="1"
                    required
                >
            </div>

            <div>
                <label for="usa_rating">USA age rating</label>
                <select id="usa_rating" name="usa_rating">
                    <option value="">Not specified</option>
                    <option value="G">G - General Audiences</option>
                    <option value="PG">PG - Parental Guidance Suggested</option>
                    <option value="PG-13">PG-13 - Parents Strongly Cautioned</option>
                    <option value="R">R - Restricted</option>
                    <option value="NC-17">NC-17 - Adults Only</option>
                </select>
            </div>

            <div>
                <label for="it_rating">Italian age rating</label>
                <select id="it_rating" name="it_rating">
                    <option value="">Not specified</option>
                    <option value="T">T - Suitable for all ages</option>
                    <option value="6">6 - Not suitable for children under 6</option>
                    <option value="10">10 - Not suitable for children under 10</option>
                    <option value="14">14 - Not suitable for children under 14</option>
                    <option value="18">18 - Adults only</option>
                </select>
            </div>

            <div>
                <label for="description">Movie description</label>
                <small>
                    Provide a brief synopsis of the movie (maximum 5000 characters).
                    This field is optional.
                </small>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    maxlength="5000"
                    placeholder="Write the movie synopsis here..."
                ></textarea>
            </div>

            <div>
                <label for="poster">Movie poster</label>
                <small>
                    Upload the movie poster. Accepted formats: JPEG, PNG and WebP.
                    Maximum size: 5 MB. This field is optional.
                </small>
                <input
                    type="file"
                    id="poster"
                    name="poster"
                    accept="image/jpeg, image/png, image/webp"
                >
            </div>

            <div>
                <button type="submit">Add movie</button>
                <button type="reset">Clear form</button>
            </div>
        </form>
    </main>
</body>
</html>
