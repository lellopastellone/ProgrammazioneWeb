<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Gestisce le operazioni CRUD sui team aziendali dell'organizzazione.
 */
class TeamController extends Controller
{
    /**
     * Mostra l'elenco dei team aziendali con il numero di membri per ciascuno.
     */
    public function index(): View
    {
        $teams = Team::with('users')->withCount('users')->orderBy('name')->get();

        return view('teams.index', compact('teams'));
    }

    /**
     * Mostra il form per la creazione di un nuovo team aziendale.
     */
    public function create(): View
    {
        return view('teams.create');
    }

    /**
     * Salva un nuovo team nel database con nome e descrizione validati.
     */
    public function store(TeamRequest $request): RedirectResponse
    {
        $team = Team::create($request->validated());

        return to_route('teams.show', $team);
    }

    /**
     * Mostra la pagina di dettaglio del team con l'elenco dei membri appartenenti (email mascherate per i non-organizer, paginati a 25).
     */
    public function show(Request $request, Team $team): View
    {
        $isOrganizer = $request->user()->hasRole('organizer');

        // Colonne passate a paginate(): su BelongsToMany aggiunge users.* e annullerebbe una select() precedente.
        $members = $team->users()
            ->orderBy('surname')
            ->orderBy('name')
            ->paginate(25, $isOrganizer ? ['*'] : ['users.id', 'name', 'surname', 'active', 'profile_photo_path']);

        return view('teams.show', compact('team', 'members'));
    }

    /**
     * Mostra la vista di modifica del team con l'elenco dei membri candidabili e membri attuali paginati.
     */
    public function edit(Request $request, Team $team): View
    {
        $team->loadCount('users');

        $members = $team->users()
            ->orderBy('surname')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // 21 = i 20 mostrati più uno per sapere se ce ne sono altri (la view segnala di affinare la ricerca).
        $candidates = User::active()->role('member')
            ->whereNotIn('id', $team->users()->allRelatedIds())
            ->search($request->query('q'))
            ->orderBy('surname')
            ->limit(21)
            ->get();

        return view('teams.edit', compact('team', 'members', 'candidates'));
    }

    /**
     * Aggiorna nome e descrizione del team nel database.
     */
    public function update(TeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->validated());

        return to_route('teams.show', $team)->with('status', 'Team aggiornato con successo.');
    }

    /**
     * Elimina il team dal database a condizione che sia privo di membri assegnati.
     */
    public function destroy(Team $team): RedirectResponse
    {
        if ($team->users()->exists()) {
            throw ValidationException::withMessages(['team' => 'Il team ha ancora dei membri.']);
        }

        $team->delete();

        return to_route('teams.index');
    }
}
