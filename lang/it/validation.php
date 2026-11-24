<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linee di Lingua per la Validazione
    |--------------------------------------------------------------------------
    |
    | Le seguenti linee di lingua contengono i messaggi di errore predefiniti usati
    | dalla classe del validatore. Alcune regole hanno molteplici versioni come
    | le regole relative alle dimensioni. Sentiti libero di modificare i messaggi.
    |
    */

    'accepted' => 'Il campo :attribute deve essere accettato.',
    'accepted_if' => 'Il campo :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il campo :attribute non è un URL valido.',
    'after' => 'Il campo :attribute deve essere una data successiva al :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale al :date.',
    'alpha' => 'Il campo :attribute può contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute può contenere solo lettere, numeri, trattini e trattini bassi.',
    'alpha_num' => 'Il campo :attribute può contenere solo lettere e numeri.',
    'any_of' => 'Il campo :attribute contiene un valore non valido.',
    'array' => 'Il campo :attribute deve essere un elenco di elementi.',
    'ascii' => 'Il campo :attribute può contenere solo caratteri alfanumerici e simboli a byte singolo.',
    'before' => 'Il campo :attribute deve essere una data precedente al :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale al :date.',
    'between' => [
        'array' => 'Il campo :attribute deve contenere tra :min e :max elementi.',
        'file' => 'Il file :attribute deve avere una dimensione compresa tra :min e :max kilobyte.',
        'numeric' => 'Il campo :attribute deve essere compreso tra :min e :max.',
        'string' => 'Il campo :attribute deve contenere tra :min e :max caratteri.',
    ],
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'can' => 'Il campo :attribute contiene un valore non autorizzato.',
    'confirmed' => 'Il campo :attribute e la relativa conferma non corrispondono.',
    'contains' => 'Nel campo :attribute manca un valore obbligatorio.',
    'current_password' => 'La password attuale non è corretta.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute non corrisponde al formato :format.',
    'decimal' => 'Il campo :attribute deve avere :decimal cifre decimali.',
    'declined' => 'Il campo :attribute deve essere rifiutato.',
    'declined_if' => 'Il campo :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'I campi :attribute e :other devono essere diversi.',
    'digits' => 'Il campo :attribute deve essere composto da :digits cifre.',
    'digits_between' => 'Il campo :attribute deve essere composto da un numero di cifre compreso tra :min e :max.',
    'dimensions' => 'L\'immagine :attribute ha dimensioni non valide.',
    'distinct' => 'Il campo :attribute contiene un valore duplicato.',
    'doesnt_end_with' => 'Il campo :attribute non può terminare con uno dei seguenti valori: :values.',
    'doesnt_start_with' => 'Il campo :attribute non può iniziare con uno dei seguenti valori: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti valori: :values.',
    'enum' => 'Il valore selezionato per :attribute non è valido.',
    'exists' => 'Il valore selezionato per :attribute non è valido o inesistente.',
    'extensions' => 'Il file :attribute deve avere una delle seguenti estensioni: :values.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute non può essere vuoto.',
    'gt' => [
        'array' => 'Il campo :attribute deve contenere più di :value elementi.',
        'file' => 'Il file :attribute deve essere più grande di :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'string' => 'Il campo :attribute deve contenere più di :value caratteri.',
    ],
    'gte' => [
        'array' => 'Il campo :attribute deve contenere almeno :value elementi.',
        'file' => 'Il file :attribute deve essere di almeno :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'string' => 'Il campo :attribute deve contenere almeno :value caratteri.',
    ],
    'hex_color' => 'Il campo :attribute deve essere un colore esadecimale valido.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'in' => 'Il valore selezionato per :attribute non è valido.',
    'in_array' => 'Il campo :attribute non esiste in :other.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una stringa JSON valida.',
    'lowercase' => 'Il campo :attribute deve essere in lettere minuscole.',
    'lt' => [
        'array' => 'Il campo :attribute deve contenere meno di :value elementi.',
        'file' => 'Il file :attribute deve essere inferiore a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere minore di :value.',
        'string' => 'Il campo :attribute deve contenere meno di :value caratteri.',
    ],
    'lte' => [
        'array' => 'Il campo :attribute non può contenere più di :value elementi.',
        'file' => 'Il file :attribute non deve superare :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere minore o uguale a :value.',
        'string' => 'Il campo :attribute non deve contenere più di :value caratteri.',
    ],
    'mac_address' => 'Il campo :attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => 'Il campo :attribute non può contenere più di :max elementi.',
        'file' => 'Il file :attribute non può superare :max kilobyte.',
        'numeric' => 'Il campo :attribute non può essere maggiore di :max.',
        'string' => 'Il campo :attribute non può superare :max caratteri.',
    ],
    'max_digits' => 'Il campo :attribute non può avere più di :max cifre.',
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il campo :attribute deve contenere almeno :min elementi.',
        'file' => 'Il file :attribute deve essere di almeno :min kilobyte.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve contenere almeno :min caratteri.',
    ],
    'min_digits' => 'Il campo :attribute deve avere almeno :min cifre.',
    'missing' => 'Il campo :attribute deve essere assente.',
    'missing_if' => 'Il campo :attribute deve essere assente quando :other è :value.',
    'missing_unless' => 'Il campo :attribute deve essere assente a meno che :other non sia :value.',
    'missing_with' => 'Il campo :attribute deve essere assente quando :values è presente.',
    'missing_with_all' => 'Il campo :attribute deve essere assente quando sono presenti :values.',
    'multiple_of' => 'Il campo :attribute deve essere un multiplo di :value.',
    'not_in' => 'Il valore selezionato per :attribute non è valido.',
    'not_regex' => 'Il formato del campo :attribute non è valido.',
    'numeric' => 'Il campo :attribute deve essere un valore numerico.',
    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un carattere speciale.',
        'uncompromised' => 'La password inserita compare in una violazione di dati pubblica. Scegli una password diversa per sicurezza.',
    ],
    'present' => 'Il campo :attribute deve essere presente.',
    'present_if' => 'Il campo :attribute deve essere presente quando :other è :value.',
    'present_unless' => 'Il campo :attribute deve essere presente a meno che :other non sia :value.',
    'present_with' => 'Il campo :attribute deve essere presente quando :values è presente.',
    'present_with_all' => 'Il campo :attribute deve essere presente quando sono presenti :values.',
    'prohibited' => 'Il campo :attribute non è consentito.',
    'prohibited_if' => 'Il campo :attribute non è consentito quando :other è :value.',
    'prohibited_if_accepted' => 'Il campo :attribute non è consentito quando :other è accettato.',
    'prohibited_if_declined' => 'Il campo :attribute non è consentito quando :other è rifiutato.',
    'prohibited_unless' => 'Il campo :attribute non è consentito a meno che :other non sia compreso in :values.',
    'prohibits' => 'Il campo :attribute impedisce la presenza del campo :other.',
    'regex' => 'Il formato del campo :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_array_keys' => 'Il campo :attribute deve contenere voci per: :values.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_if_accepted' => 'Il campo :attribute è obbligatorio quando :other è accettato.',
    'required_if_declined' => 'Il campo :attribute è obbligatorio quando :other è rifiutato.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other non sia compreso in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando sono presenti :values.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno tra :values è presente.',
    'same' => 'I campi :attribute e :other devono coincidere.',
    'size' => [
        'array' => 'Il campo :attribute deve contenere :size elementi.',
        'file' => 'Il file :attribute deve avere una dimensione di :size kilobyte.',
        'numeric' => 'Il campo :attribute deve essere uguale a :size.',
        'string' => 'Il campo :attribute deve contenere :size caratteri.',
    ],
    'starts_with' => 'Il campo :attribute deve iniziare con uno dei seguenti valori: :values.',
    'string' => 'Il campo :attribute deve essere una stringa di testo.',
    'timezone' => 'Il campo :attribute deve essere un fuso orario valido.',
    'unique' => 'Questo valore per :attribute è già in uso.',
    'uploaded' => 'Il caricamento del file :attribute non è riuscito.',
    'uppercase' => 'Il campo :attribute deve essere in lettere maiuscole.',
    'url' => 'Il campo :attribute deve essere un URL valido.',
    'ulid' => 'Il campo :attribute deve essere un ULID valido.',
    'uuid' => 'Il campo :attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Messaggi di Validazione Personalizzati
    |--------------------------------------------------------------------------
    |
    | Qui puoi specificare messaggi di validazione personalizzati per specifici
    | attributi usando la convenzione "attributo.regola".
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Attributi Personalizzati di Validazione
    |--------------------------------------------------------------------------
    |
    | Le seguenti linee di lingua sostituiscono i segnaposto degli attributi
    | (es. "email") con etichette più chiare e leggibili in italiano (es. "indirizzo email").
    |
    */

    'attributes' => [
        'name' => 'nome',
        'surname' => 'cognome',
        'email' => 'indirizzo email',
        'password' => 'password',
        'password_confirmation' => 'conferma password',
        'current_password' => 'password attuale',
        'role' => 'ruolo',
        'teams' => 'team',
        'teams.*' => 'team selezionato',
        'team_id' => 'team',
        'title' => 'titolo',
        'description' => 'descrizione',
        'status' => 'stato',
        'priority' => 'priorità',
        'project_id' => 'progetto',
        'iteration_id' => 'iterazione',
        'user_id' => 'utente',
        'starts_on' => 'data di inizio',
        'ends_on' => 'data di fine',
        'start_date' => 'data di inizio',
        'end_date' => 'data di scadenza',
        'due_date' => 'data di scadenza',
        'host' => 'host',
        'port' => 'porta',
        'username' => 'nome utente',
        'from_address' => 'indirizzo mittente',
        'smtp_host' => 'server SMTP',
        'smtp_port' => 'porta SMTP',
        'smtp_username' => 'nome utente SMTP',
        'smtp_password' => 'password SMTP',
        'smtp_from_address' => 'indirizzo mittente SMTP',
        'domains' => 'domini aziendali consentiti',
        'text' => 'testo del commento',
        'code' => 'codice di verifica',
        'recovery_code' => 'codice di emergenza',
        'photo' => 'foto profilo',
        'manager_id' => 'project manager',
        'pm_id' => 'project manager',
        'app_name' => 'nome dell\'applicazione',
        'app_url' => 'URL dell\'applicazione',
        'token' => 'token di attivazione',
        'month' => 'mese',
        'terms' => 'termini e condizioni',
    ],

];

