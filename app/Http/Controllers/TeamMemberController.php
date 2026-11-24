<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce l'aggiunta e la rimozione dei membri operativi all'interno di un team aziendale.
 */
class TeamMemberController extends Controller
{
    /**
     * Aggiunge un utente member al team specificato.
     */
    public function store(StoreTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $team->users()->syncWithoutDetaching([$request->integer('user_id')]);

        return back();
    }

    /**
     * Rimuove l'utente dal team specificato.
     */
    public function destroy(Team $team, User $user): RedirectResponse
    {
        $team->users()->detach($user);

        return back();
    }
}
