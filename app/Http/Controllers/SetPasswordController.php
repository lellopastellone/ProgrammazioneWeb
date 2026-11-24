<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Gestisce la prima impostazione della password tramite il link ricevuto nell'email di benvenuto.
 */
class SetPasswordController extends Controller
{
    /**
     * Mostra la schermata per l'impostazione iniziale della password.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request, 'welcome' => true]);
    }

    /**
     * Valida il token di invito e memorizza la nuova password scelta dall'utente.
     */
    public function store(SetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('invites')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            // remember_token nuovo come nel reset di Fortify: eventuali cookie "Ricordami" precedenti smettono di valere.
            fn (User $user, string $password) => $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save(),
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'Il link non è più valido: usa «Password dimenticata?» per riceverne uno nuovo.']);
        }

        return to_route('login')->with('status', 'Password salvata: ora puoi accedere.');
    }
}
