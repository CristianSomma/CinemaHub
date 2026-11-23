<?php
declare(strict_types=1);

namespace CinemaHub\Services;

use CinemaHub\Models\Movie;
use CinemaHub\Repositories\DirectorRepository;
use CinemaHub\Repositories\MovieRepository;
use CinemaHub\Support\Validator;

final class MovieService
{
    // recupera tutti gli oggetti necessari per locandina e caricamento nel database
    public function __construct(
        private MovieRepository $movieRepository,
        private DirectorRepository $directorRepository,
        private PosterUploader $posterUploader
    ) {
    }

    public function create(Movie $movie, ?array $uploadedPoster): bool
    {
        // VERIFICA CHE GLI ATTRIBUTI DEL FILM SIANO VALIDI PER ESSERE
        // CARICATI SUL DATABASE

        if (trim($movie->getTitle()) === '') {
            throw new \InvalidArgumentException('Movie title is required.');
        }

        if (mb_strlen($movie->getTitle(), 'UTF-8') > 255) {
            throw new \InvalidArgumentException('Movie title must not exceed 255 characters.');
        }

        if ($movie->getReleaseYear() < 1850 || $movie->getReleaseYear() > (int) date('Y')) {
            throw new \InvalidArgumentException('Invalid movie release year.');
        }

        if ($movie->getDurationMinutes() < 1 || $movie->getDurationMinutes() > 65535) {
            throw new \InvalidArgumentException('Invalid movie duration.');
        }

        if (!$this->directorRepository->existsById($movie->getDirectorId())) {
            throw new \InvalidArgumentException('The selected director does not exist.');
        }

        if (
            $movie->getUsaRating() !== null
            && !Validator::isUsaRatingValid($movie->getUsaRating())
        ) {
            throw new \InvalidArgumentException('Invalid US age rating.');
        }

        if (
            $movie->getItalianRating() !== null
            && !Validator::isItalianRatingValid($movie->getItalianRating())
        ) {
            throw new \InvalidArgumentException('Invalid Italian age rating.');
        }

        if (
            $movie->getDescription() !== null
            && mb_strlen($movie->getDescription(), 'UTF-8') > 5000
        ) {
            throw new \InvalidArgumentException('Movie description must not exceed 5000 characters.');
        }

        // L'upload avviene dopo tutte le validazioni, così non vengono creati file per dati errati.
        $posterPath = $this->posterUploader->upload($uploadedPoster);

        // dato che gli oggetti sono per design immutabili (fatto appositamente per
        // evitare modifiche accidentali e mantenere uno stato costante)
        // viene creato un nuovo oggetto film, con il percorso del poster appena caricato
        $movieToSave = new Movie(
            $movie->getId(),
            $movie->getTitle(),
            $movie->getDirectorId(),
            $movie->getReleaseYear(),
            $movie->getDurationMinutes(),
            $movie->getUsaRating(),
            $movie->getItalianRating(),
            $movie->getDescription(),
            $posterPath
        );

        try {
            // viene caricato sul database il film compreso dell'url del poster, 
            // se l'operazione fallisce, allora viene lanciata un'operazione
            if (!$this->movieRepository->create($movieToSave)) {
                throw new \RuntimeException('Unable to insert movie.');
            }

            return true;
        } catch (\Throwable $exception) {
            // se l'operazione fallisce, viene cancellato il poster dal progetto
            $this->posterUploader->delete($posterPath);
            throw $exception;
        }
    }
}
