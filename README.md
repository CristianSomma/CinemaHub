# CinemaHub — ambiente Docker di sviluppo

Il progetto usa tre servizi principali, separati:

- **`web` — Apache**: riceve le richieste HTTP e serve le pagine pubbliche;
- **`php` — PHP-FPM**: esegue gli script PHP e comunica con il database;
- **`database` — MariaDB**: conserva utenti, film, recensioni e gli altri dati.

È presente anche **phpMyAdmin**, opzionale e avviabile soltanto quando serve.

Il codice PHP viene montato dal computer nei container: salvando un file non devi ricostruire l'immagine né riavviare i servizi. MariaDB utilizza un volume Docker nominato, separato dal container, quindi i dati non vengono cancellati quando fermi o ricrei i container.

## 1. Struttura rilevante

```text
CinemaHub/
├── .dockerignore
├── .env.example
├── .gitignore
├── Dockerfile
├── compose.yaml
└── src/
    ├── actions/             # Script che elaborano i form
    ├── config/              # Configurazione PHP del database
    ├── database/init.sql    # Schema iniziale
    ├── docker/apache/       # Virtual host Apache
    ├── errors/              # Pagine di errore
    ├── includes/            # Funzioni riutilizzabili
    └── public/              # Unica DocumentRoot del sito
```

Apache espone `src/public` come DocumentRoot, per non rendere direttamente accessibili dal browser i file di configurazione e lo schema SQL. Gli script di `src/actions` sono raggiungibili solo attraverso l'alias `/actions/`, usato dai form di login e registrazione.

## 2. Configurazione iniziale

Dalla cartella principale del progetto, crea `.env` copiando il modello.

**Windows PowerShell**

```powershell
Copy-Item .env.example .env
```

**Linux / macOS**

```bash
cp .env.example .env
```

Apri `.env` e sostituisci almeno `DB_PASSWORD` e `DB_ROOT_PASSWORD` con password locali non banali. Non condividere `.env` e non inserirlo nel repository: è già escluso da `.gitignore`.

> **Importante per i dati esistenti:** conserva esattamente il valore di `COMPOSE_PROJECT_NAME` che hai usato con la configurazione precedente. Il modello incluso usa `php-dev` (e quindi il volume `php-dev_db_data`), ma il tuo `.env` locale potrebbe avere un altro nome: cambiarlo farebbe cercare a Docker un volume diverso. Il nuovo Compose mantiene lo stesso nome del servizio e lo stesso volume logico `db_data`, così può riutilizzare quello già esistente.

## 3. Avvio

Dalla cartella che contiene `compose.yaml`:

```bash
docker compose config
docker compose up -d --build
```

La prima esecuzione scarica le immagini e costruisce i due servizi applicativi. Gli avvii successivi possono usare semplicemente:

```bash
docker compose up -d
```

Apri il sito:

- Sito: <http://localhost:8080>
- MariaDB dal computer: host `127.0.0.1`, porta `3307`

Le porte si possono cambiare nel file `.env` (`WEB_PORT` e `DB_HOST_PORT`). Sono pubblicate solo sull'interfaccia locale del computer.

## 4. Come comunicano i tre servizi

```text
Browser
   │ http://localhost:8080
   ▼
web — Apache
   │ inoltra i file .php a php:9000
   ▼
php — PHP-FPM
   │ DB_HOST=database, DB_PORT=3306
   ▼
database — MariaDB
   │
   ▼
volume Docker db_data
```

Il servizio `php` non pubblica la porta 9000 sul computer: è accessibile soltanto nella rete privata di Compose. Dal PHP, l'host del database deve essere `database`, cioè il nome del servizio Compose. Non usare `localhost`: dentro il container `php`, `localhost` indicherebbe il container PHP stesso.

Le variabili `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` arrivano al PHP dal file `.env` attraverso `compose.yaml`.

## 5. Aggiornamento del sito senza rebuild

Sia Apache sia PHP vedono la cartella locale `src` montata in `/var/www/html`:

- Apache la monta in sola lettura (`:ro`), perché deve soltanto servire i file;
- PHP la monta in lettura/scrittura, per eseguire gli script.

Di conseguenza:

- modificare PHP, HTML, CSS o JavaScript: salva il file e aggiorna il browser;
- aggiungere o modificare file in `src`: non serve ricostruire le immagini;
- modificare `Dockerfile` o installare un'estensione PHP: ricostruisci il servizio `php` con `docker compose up -d --build php`;
- modificare il virtual host o i moduli Apache: ricostruisci `web` con `docker compose up -d --build web`.

Il target PHP installa `mysqli`, `pdo_mysql` e `mbstring`. Quest'ultima estensione è necessaria perché `src/includes/validation.php` utilizza `mb_strlen()`. Inoltre usa `php.ini-development`, adatto al lavoro in locale e non alla pubblicazione in produzione.

## 6. Database persistente e inizializzazione

MariaDB memorizza i file del database nel volume nominato `db_data`, non nel filesystem effimero del container. Quindi:

```bash
docker compose down
docker compose up -d
```

ferma e ricrea i container senza cancellare i dati.

Lo script `src/database/init.sql` è montato in `/docker-entrypoint-initdb.d/01-init.sql`. L'immagine ufficiale MariaDB importa gli script SQL di quella directory **solo quando inizializza un datadir nuovo**; il file viene importato nel database indicato da `DB_NAME`. Se il volume contiene già un database, modificare `init.sql` non provoca una nuova esecuzione automatica. Per i dettagli, consulta la [documentazione ufficiale dell’immagine MariaDB](https://github.com/docker-library/docs/blob/master/mariadb/content.md#initializing-the-database-contents).

### Applicare lo schema a un volume già esistente

Se il volume era già stato creato ma non contiene ancora le tabelle CinemaHub, avvia i container e importa manualmente lo schema, senza eliminare il volume:

```bash
docker compose exec -T database sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE" < /docker-entrypoint-initdb.d/01-init.sql'
```

Le tabelle sono dichiarate con `CREATE TABLE IF NOT EXISTS`, quindi lo script può creare quelle mancanti senza sovrascrivere le righe già presenti nelle tabelle esistenti. Non è però uno strumento per modificare tabelle già create: per cambiare la struttura in futuro serviranno query di migrazione `ALTER TABLE`.

### Backup del database

Per salvare un dump SQL nella cartella da cui esegui il comando:

```bash
docker compose exec -T database sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' > backup.sql
```

### Comandi da non confondere

| Comando | Effetto sui dati |
|---|---|
| `docker compose stop` | Ferma i container; conserva il volume. |
| `docker compose down` | Rimuove i container e la rete; conserva il volume. |
| `docker compose up -d --build` | Ricostruisce/avvia i servizi richiesti; conserva il volume. |
| `docker compose down -v` | **Elimina anche il volume e tutti i dati MariaDB.** |

Non usare `down -v` a meno che tu non voglia davvero cancellare il database.

## 7. phpMyAdmin (facoltativo)

phpMyAdmin non parte nell'avvio normale. Per avviare anche il servizio `phpmyadmin`:

```bash
docker compose --profile tools up -d
```

Apri <http://localhost:8081> e accedi con `DB_USER` e `DB_PASSWORD` del file `.env`. Per fermarlo senza fermare gli altri servizi:

```bash
docker compose stop phpmyadmin
```

## 8. Comandi utili

| Operazione | Comando |
|---|---|
| Stato dei servizi | `docker compose ps` |
| Log Apache | `docker compose logs -f web` |
| Log PHP | `docker compose logs -f php` |
| Log MariaDB | `docker compose logs -f database` |
| Shell nel container PHP | `docker compose exec php bash` |
| Client MariaDB root | `docker compose exec database mariadb -u root -p` |
| Ricostruzione PHP | `docker compose up -d --build php` |
| Ricostruzione Apache | `docker compose up -d --build web` |

## 9. Modifiche successive alle credenziali

Le variabili `MARIADB_DATABASE`, `MARIADB_USER`, `MARIADB_PASSWORD` e `MARIADB_ROOT_PASSWORD` vengono usate dall'immagine MariaDB durante la prima inizializzazione del volume. Se in seguito cambi le password in `.env`, il solo riavvio o la ricreazione del container non modifica automaticamente gli account già esistenti nel database. In quel caso bisogna aggiornare anche gli account MariaDB con apposite istruzioni SQL.

Questa configurazione è per lo **sviluppo locale**, non per pubblicare il sito direttamente su Internet.
