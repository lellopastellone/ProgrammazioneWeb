<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailSettingRequest;
use App\Models\MailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestisce la visualizzazione e la modifica delle impostazioni del server SMTP.
 */
class MailSettingController extends Controller
{
    /**
     * Mostra il form per la configurazione del server SMTP.
     */
    public function edit(): View
    {
        return view('mail-settings.edit', ['smtp' => MailSetting::current()]);
    }

    /**
     * Aggiorna i parametri di configurazione del server SMTP nel database.
     */
    public function update(MailSettingRequest $request): RedirectResponse
    {
        MailSetting::current()->fill($request->smtp())->save();

        return to_route('mail-settings.edit')->with('status', 'Server di posta aggiornato.');
    }
}
