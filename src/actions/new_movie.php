<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use CinemaHub\Models\Movie;
use CinemaHub\Repositories\DirectorRepository;
use CinemaHub\Repositories\MovieRepository;
use CinemaHub\Services\MovieService;
use CinemaHub\Services\PosterUploader;
use CinemaHub\Support\InputSanitizer;
use CinemaHub\Support\SessionManager;
use CinemaHub\Support\Validator;

/** Memorizza il messaggio e rimanda al form di inserimento del film. */
$redirectWithMessage = static function (string $type, string $message): never {
    SessionManager::setFlashMessage($type, $message);
    header('Location: /new_movie.php');
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirectWithMessage('error', 'Invalid request method.');
}

if (!SessionManager::isAuthenticated()) {
    $redirectWithMessage('error', 'Authentication required.');
}

/*
 * In questa versione didattica non viene usato un token CSRF, come deciso
 * durante lo sviluppo del progetto. Prima di una pubblicazione reale andrà
 * aggiunto un token sia al form sia a questo controllo.
 */

try {
    $title = InputSanitizer::sanitize($_POST['title'] ?? null);
    $directorId = Validator::parseInteger($_POST['director_id'] ?? null);
    $releaseYear = Validator::parseInteger($_POST['release_year'] ?? null);
    $durationMinutes = Validator::parseInteger($_POST['duration_minutes'] ?? null);

    $usaRating = InputSanitizer::nullableString($_POST['usa_rating'] ?? null);
    $italianRating = InputSanitizer::nullableString($_POST['it_rating'] ?? null);
    $description = InputSanitizer::nullableString($_POST['description'] ?? null);

    $movie = new Movie(
        null,
        $title,
        $directorId,
        $releaseYear,
        $durationMinutes,
        $usaRating,
        $italianRating,
        $description,
        null
    );

    $connection = require __DIR__ . '/../config/database.php';

    $movieService = new MovieService(
        new MovieRepository($connection),
        new DirectorRepository($connection),
        new PosterUploader()
    );

    $movieService->create($movie, $_FILES['poster'] ?? null);

    $redirectWithMessage('success', 'Movie successfully added.');
} catch (\InvalidArgumentException $exception) {
    $redirectWithMessage('error', $exception->getMessage());
} catch (\Throwable $exception) {
    error_log("Errore durante l'inserimento del film: " . $exception->getMessage());
    $redirectWithMessage('error', 'An unexpected error occurred while adding the movie.');
}
