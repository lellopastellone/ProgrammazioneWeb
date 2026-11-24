<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\EmailDomain;
use App\Models\Team;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Gestisce l'elenco, la creazione, l'invito e la modifica degli utenti da parte dell'organizer.
 */
class UserController extends Controller
{
    /**
     * Mostra l'elenco degli utenti mascherando l'email per i non organizer.
     */
    public function index(Request $request): View
    {
        $isOrganizer = $request->user()->hasRole('organizer');

        // Ricerca per ?q= (l'email solo per l'organizer, l'unico che la vede) e 25 utenti per pagina.
        $users = User::query()
            ->unless($isOrganizer, fn ($q) => $q->select(['id', 'name', 'surname', 'active', 'profile_photo_path']))
            ->search($request->query('q'), $isOrganizer ? ['name', 'surname', 'email'] : ['name', 'surname'])
            ->with(['roles', 'teams'])
            ->orderBy('surname')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Mostra il form per la creazione di un nuovo utente con relativi team e domini.
     */
    public function create(): View
    {
        return view('users.create', ['teams' => Team::orderBy('name')->get(), 'emailDomain' => EmailDomain::orderBy('id')->value('domain')]);
    }

    /**
     * Crea un utente assegnando ruolo, team e inviando l'email con il token per la password.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->only(['name', 'surname', 'email']),
            'password' => Str::password(32),
        ]);

        $user->assignRole($request->validated('role'));
        $user->teams()->sync($request->validated('teams', []));

        // Mail di benvenuto con il link per la prima password. L'utente esiste già: se l'SMTP non risponde lo si dice, invece di un 500.
        try {
            $user->notify(new WelcomeNotification(Password::broker('invites')->createToken($user)));
        } catch (TransportExceptionInterface) {
            return to_route('users.show', $user)
                ->with('flash.banner', "Utente creato, ma l'email con il link per la password non è partita: controlla il server SMTP.")
                ->with('flash.bannerStyle', 'warning');
        }

        return to_route('users.show', $user);
    }

    /**
     * Mostra la scheda dettagliata dell'utente con ruoli, team e progetti collegati.
     */
    public function show(User $user): View
    {
        $user->load(['roles', 'teams', 'managedProjects', 'projects']);

        return view('users.show', compact('user'));
    }

    /**
     * Mostra il form di gestione per un collega o rimanda alla pagina profilo personale se si modifica il proprio account.
     */
    public function edit(Request $request, User $user): View|RedirectResponse
    {
        // Se l'utente sta modificando se stesso, rimanda alla pagina del profilo personale di Jetstream.
        if ($user->is($request->user())) {
            return to_route('profile.show');
        }

        $user->load(['roles', 'teams']);

        return view('users.edit', ['user' => $user, 'teams' => Team::orderBy('name')->get(), 'emailDomain' => EmailDomain::orderBy('id')->value('domain')]);
    }

    /**
     * Aggiorna i dati anagrafici e i team assegnati all'utente.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        abort_if(! $user->active, 403, 'Impossibile modificare un account disattivato.');

        $user->update($request->safe()->only(['name', 'surname', 'email']));
        $user->teams()->sync($request->validated('teams', []));

        return to_route('users.show', $user)->with('status', 'Utente aggiornato con successo.');
    }
}
