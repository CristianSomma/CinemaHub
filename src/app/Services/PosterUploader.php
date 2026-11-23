<?php
declare(strict_types=1);

namespace CinemaHub\Services;

final class PosterUploader
{
    // la dimensione massima del poster è 5MB
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Valida il file caricato e restituisce il percorso relativo pubblico.
     * Restituisce null quando l'utente non seleziona una locandina.
     */
    public function upload(?array $uploadedFile): ?string
    {
        // se nessun file è stato caricato, ritorna null (nessun percorso)
        if ($uploadedFile === null) {
            return null;
        }

        // se il valore della chiave error di $uploadedFile è null, allora
        // setta l'errore di caricamento come NO_FILE, cosi da restituire
        // dopo un percorso nullo.
        $uploadError = $uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE;

        // se nessun file è stato caricato, restituisce un percorso nullo
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // se il caricamento non ha avuto successo, allora lancia un'eccezione
        if ($uploadError !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Poster upload failed.');
        }

        // percorso temporaneo del file e dimensione
        $temporaryPath = $uploadedFile['tmp_name'] ?? null;
        $fileSize = $uploadedFile['size'] ?? null;

        // se i valori del file caricato non sono del tipo giusto, lancia un'eccezione
        if (
            !is_string($temporaryPath)
            || !is_int($fileSize)
            || !is_uploaded_file($temporaryPath)
        ) {
            throw new \InvalidArgumentException('The uploaded poster is not valid.');
        }

        // se la dimensione del file è negativa o maggiore di 5MB lancia un'eccezione
        if ($fileSize <= 0 || $fileSize > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('The poster must not exceed 5 MB.');
        }

        // recupera l'oggetto contenente le info del file
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        // recupera il MIME del file caricato al percorso temporaneo
        $mimeType = $fileInfo->file($temporaryPath);

        // array associativo che specifica l'estensione del poster in relazione
        // al tipo di mime presente nelle info del file caricato
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        // se il MIME del file non appartiene a quelli consentiti,
        // cosa improbabile perché è gestito già nel tag html,
        // lancia un'eccezione
        if (!is_string($mimeType) || !isset($allowedMimeTypes[$mimeType])) {
            throw new \InvalidArgumentException(
                'Invalid poster format. Accepted formats: JPEG, PNG and WebP.'
            );
        }

        // se la dimensione del file non è riconosciuta come quella di un'immagine,
        // la funzione fallisce e ritorna false 
        if (getimagesize($temporaryPath) === false) {
            throw new \InvalidArgumentException('The uploaded file is not a valid image.');
        }

        // genera un nome randomico del file composto da 32 caratteri esadecimali
        // (16 byte / 4bit = 32), con estensione del file immagine caricato
        $fileName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
        // determina la posizione del file nella cartella posters del progetto.
        $posterDirectory = dirname(__DIR__, 2) . '/public/uploads/images/posters/';

        // verifica se esiste la directory nella quale dovrà caricare l'immagine,
        // se non esiste, prova a crearla (con tutti i permessi eccetto di creazione per i gruppi e altri),
        // se fallisce lancia un'eccezione.
        if (
            !is_dir($posterDirectory)
            && !mkdir($posterDirectory, 0755, true)
            && !is_dir($posterDirectory)
        ) {
            throw new \RuntimeException('Unable to create poster directory.');
        }

        // percorso assoluto del poster
        $absolutePosterPath = $posterDirectory . $fileName;

        // cerca di spostare il file caricato dal percorso temporaneo a quello
        // specificato nel progetto. Se fallisce lancia un'eccezione.
        if (!move_uploaded_file($temporaryPath, $absolutePosterPath)) {
            throw new \RuntimeException('Unable to save the poster.');
        }

        // infine ritorna il percorso del poster caricato.
        return 'uploads/images/posters/' . $fileName;
    }

    /** Elimina una locandina creata durante una richiesta non completata. */
    public function delete(?string $relativePosterPath): void
    {
        // percorso previsto per la locandina da cancellare, se non è questo
        // l'eliminazione non è consentita
        $expectedPrefix = 'uploads/images/posters/';

        // se la stringa è null oppure non inizia con lo stesso percorso
        // previsto, non avanza con l'eliminazione
        if (
            $relativePosterPath === null
            || !str_starts_with($relativePosterPath, $expectedPrefix)
        ) {
            return;
        }

        // rimuove il percorso tagliandolo, mantenendo solo il nome del file.
        $fileName = substr($relativePosterPath, strlen($expectedPrefix));

        // se non c'era il nome del file, oppure se fileName contiene più di solamente
        // il nome del file (esempio un'altra cartella), allora non avanza.
        if ($fileName === '' || basename($fileName) !== $fileName) {
            return;
        }

        // ottiene il percorso assoluto del file
        $absolutePosterPath = dirname(__DIR__, 2)
            . '/public/uploads/images/posters/' . $fileName;

        // verifica che percorso punti ad un file, in quel caso lo cancella
        if (is_file($absolutePosterPath)) {
            unlink($absolutePosterPath);
        }
    }
}
