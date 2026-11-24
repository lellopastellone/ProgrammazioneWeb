<?php

namespace App\Models;

// MustVerifyEmail non utilizzato: gli utenti vengono creati su invito dell'organizer e l'email viene già verificata quando impostano la prima password.
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\EmailChangedNotification;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Rappresenta l'utente della piattaforma con ruoli RBAC, autenticazione e progetti collegati.
 */
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Email cambiata (dal profilo o dall'organizer): avviso al vecchio indirizzo. In rescue() perché
     * un SMTP che non risponde non deve far fallire il salvataggio.
     */
    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if ($user->wasChanged('email')) {
                $changer = auth()->user();
                rescue(fn () => Notification::route('mail', $user->getOriginal('email'))->notify(new EmailChangedNotification($user, $changer)));
            }

            // Password cambiata (recupero, primo accesso o profilo): i link ancora aperti dell'altro broker non devono sovrascriverla.
            if ($user->wasChanged('password')) {
                Password::broker('users')->deleteToken($user);
                Password::broker('invites')->deleteToken($user);
            }
        });
    }

    /**
     * Mail di recupero password (chiamata dal broker su /forgot-password). In rescue(): con l'SMTP giù la risposta
     * resta "link inviato" come per un'email inesistente, invece di un 500 che rivelerebbe che l'account esiste.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        rescue(fn () => $this->notify(new ResetPassword($token)));
    }

    /**
     * Definisce la relazione molti-a-molti con i team di appartenenza dell'utente.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Definisce la relazione con i progetti coordinati dall'utente in qualità di PM.
     */
    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'pm_id');
    }

    /**
     * Definisce la relazione molti-a-molti con i progetti in cui l'utente è membro (ProjectGroup).
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * Definisce la relazione molti-a-molti con i task assegnati all'utente (UserTask).
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->withPivot('assigned_at');
    }

    /**
     * Accessor che restituisce il nome completo concatenando nome e cognome.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->name} {$this->surname}");
    }

    /**
     * Filtra la query limitandola agli utenti con stato attivo.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Ricerca per ?q=: ogni parola deve comparire in almeno una delle colonne, senza distinguere maiuscole
     * (whereLike usa ilike su PostgreSQL). Un termine vuoto o non stringa (?q[]=) non filtra.
     */
    public function scopeSearch(Builder $query, mixed $term, array $columns = ['name', 'surname']): void
    {
        if (! is_string($term)) {
            return;
        }

        // Al massimo 5 parole: il termine arriva dall'URL, la query non deve crescere senza limiti.
        foreach (array_slice(preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY), 0, 5) as $word) {
            $query->where(function (Builder $q) use ($columns, $word) {
                foreach ($columns as $column) {
                    // % e _ cercati come caratteri, non come jolly.
                    $q->orWhereLike($column, '%'.addcslashes($word, '%_\\').'%');
                }
            });
        }
    }

    /**
     * Verifica l'esistenza di almeno un utente con ruolo organizer nel sistema.
     */
    public static function hasOrganizer(): bool
    {
        return static::whereHas('roles', fn (Builder $q) => $q->where('name', 'organizer'))->exists();
    }

    /**
     * Definisce i cast degli attributi per password, date e stato attivo.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }
}
