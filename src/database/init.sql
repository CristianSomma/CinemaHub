-- Schema iniziale CinemaHub.
-- L'entrypoint ufficiale dell'immagine MariaDB importa questo file nel database
-- indicato dalla variabile MARIADB_DATABASE impostata in compose.yaml.
-- Il comando ALTER mantiene la codifica prevista dal progetto senza fissare
-- qui il nome del database.

ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- id (pk) che identifica l'utente
    username VARCHAR(50) NOT NULL, -- username univoco dell'utente
    email VARCHAR(254) NOT NULL, -- ogni email può creare un solo utente
    password_hash VARCHAR(255) NOT NULL, -- HASH della password
    first_name VARCHAR(100) NULL, -- nome dell'utente (facoltativo)
    last_name VARCHAR(100) NULL, -- cognome dell'utente (facoltativo)
    role VARCHAR(20) NOT NULL DEFAULT 'USER', -- ruolo dell'utente (USER , ADMIN o CRITIC)
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- data di creazione
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) -- data ultima modifica
    ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT pk_users PRIMARY KEY (id),
    CONSTRAINT uq_users_username UNIQUE (username),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT chk_users_role CHECK (role IN ('USER', 'ADMIN', 'CRITIC'))
);

CREATE TABLE IF NOT EXISTS directors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- id univoco del regista
    first_name VARCHAR(100) NOT NULL, -- nome
    last_name VARCHAR(100) NOT NULL, -- cognome
    birth_date DATE NOT NULL, -- data di nascita
    death_date DATE NULL DEFAULT NULL, -- data di morte (NULL se ancora vivo)
    nationality VARCHAR(100) NULL, -- nazionalità
    biography TEXT NULL, -- descrizione della biografia
    photo_url VARCHAR(1024) NULL DEFAULT NULL, -- URL della foto profilo
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- data di creazione
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) -- data ultima modifica
    ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT pk_directors PRIMARY KEY (id),
    -- Se death_date è NULL (regista vivo), la condizione è comunque valida.
    CONSTRAINT chk_directors_dates CHECK (
        death_date IS NULL
        OR death_date >= birth_date
    )
);

CREATE TABLE IF NOT EXISTS genres (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL, -- nome del genere
    CONSTRAINT pk_genres PRIMARY KEY (id),
    CONSTRAINT uq_genres_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS movies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- identificatore univoco del film
    title VARCHAR(255) NOT NULL, -- titolo del film
    director_id BIGINT UNSIGNED NOT NULL, -- id del regista del film
    release_year SMALLINT UNSIGNED NOT NULL, -- anno di rilascio del film
    duration_minutes SMALLINT UNSIGNED NOT NULL, -- durata in minuti del film
    usa_rating ENUM('G', 'PG', 'PG-13', 'R', 'NC-17') NULL, -- rating USA
    it_rating ENUM('T', '6', '10', '14', '18') NULL, -- rating Italia
    description TEXT NULL DEFAULT NULL, -- trama del film (facoltativa)
    poster_url VARCHAR(1024) NULL DEFAULT NULL, -- URL della locandina (non foto profilo!)
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT pk_movies PRIMARY KEY (id),
    CONSTRAINT fk_movies_director FOREIGN KEY (director_id) REFERENCES directors (id),
    -- NOTA: Validare l'anno a livello applicativo per bloccare film futuri (es. > anno corrente)
    CONSTRAINT chk_movies_year CHECK (
        release_year >= 1850
        AND release_year <= 2100
    ),

    CONSTRAINT chk_movies_duration CHECK (
        duration_minutes > 0
    )
);

CREATE TABLE IF NOT EXISTS movie_genre (
    movie_id BIGINT UNSIGNED NOT NULL,
    genre_id BIGINT UNSIGNED NOT NULL,
    CONSTRAINT pk_movie_genres PRIMARY KEY (movie_id, genre_id),
    CONSTRAINT fk_mg_movie FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE,
    CONSTRAINT fk_mg_genre FOREIGN KEY (genre_id) REFERENCES genres (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- id univoco della recensione
    user_id BIGINT UNSIGNED NOT NULL, -- utente che scrive la recensione
    movie_id BIGINT UNSIGNED NOT NULL, -- film recensito
    rating TINYINT UNSIGNED NOT NULL, -- voto da 1 a 10
    content TEXT NULL, -- testo della recensione (facoltativo)
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- data di creazione
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) -- data ultima modifica
    ON UPDATE CURRENT_TIMESTAMP(6),
    CONSTRAINT pk_reviews PRIMARY KEY (id),
    -- Un utente può recensire un film una sola volta
    CONSTRAINT uq_reviews_user_movie UNIQUE (user_id, movie_id),
    -- Se l'utente o il film vengono eliminati, elimina anche le sue recensioni (CASCADE)
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_movie FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (
        rating >= 1
        AND rating <= 10
    )
);

CREATE TABLE IF NOT EXISTS favorites (
    user_id    BIGINT UNSIGNED NOT NULL,
    movie_id   BIGINT UNSIGNED NOT NULL,
    
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- data di creazione
    
    CONSTRAINT pk_favorites PRIMARY KEY (user_id, movie_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_movie FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE
);