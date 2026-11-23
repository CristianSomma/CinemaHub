<?php
declare(strict_types=1);

namespace CinemaHub\Repositories;

use CinemaHub\Models\Movie;
use mysqli;

final class MovieRepository
{
    // passa come parametro del costruttore l'oggetto connessione al database
    public function __construct(private mysqli $connection)
    {
    }


    public function create(Movie $movie): bool
    {
        // crea la query sql per inserire un nuovo film nel database
        $sql = 'INSERT INTO movies (
                    title,
                    director_id,
                    release_year,
                    duration_minutes,
                    usa_rating,
                    it_rating,
                    description,
                    poster_url
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        
        // prepara la query per il binding dei valori
        $statement = $this->connection->prepare($sql);

        try {
            $title = $movie->getTitle();
            $directorId = $movie->getDirectorId();
            $releaseYear = $movie->getReleaseYear();
            $durationMinutes = $movie->getDurationMinutes();
            $usaRating = $movie->getUsaRating();
            $italianRating = $movie->getItalianRating();
            $description = $movie->getDescription();
            $posterPath = $movie->getPosterPath();

            // fa il binding dei valori nella query, s = string, i = integer
            $statement->bind_param(
                'siiissss',
                $title,
                $directorId,
                $releaseYear,
                $durationMinutes,
                $usaRating,
                $italianRating,
                $description,
                $posterPath
            );
            
            // esegue la query
            $statement->execute();

            // ritorna true se una riga del db è stata modificata, e quindi 
            // l'esecuzione ha avuto successo, altrimenti false.
            return $statement->affected_rows === 1;
        } finally {
            // indipendentemente dal sollevamento di eccezioni, chiude la query
            // per rilasciare la risorsa
            $statement->close();
        }
    }

    public function findById(int $movieId): ?Movie
    {
        // query che recupera le informazioni utili del film dato uno specifico
        // id.
        $sql = 'SELECT id, title, director_id, release_year, duration_minutes,
                       usa_rating, it_rating, description, poster_url
                FROM movies
                WHERE id = ?
                LIMIT 1';


        $statement = $this->connection->prepare($sql);

        try {
            $statement->bind_param('i', $movieId);
            $statement->execute();

            // recupera l'oggetto mysql_result, che contiene
            // i valori recuperati dal database
            $result = $statement->get_result();
            // trasforma in array associativo l'oggetto result
            $movieData = $result->fetch_assoc();
            // rilascia la memoria occupata dall'oggetto result
            $result->free();

            // se l'array associativo esiste e non è null, allora crea
            // un nuovo oggetto Movie dati i valori recuperati dal db.
            return $movieData === null ? null : Movie::fromDatabaseRow($movieData);
        } finally {
            $statement->close();
        }
    }
}
