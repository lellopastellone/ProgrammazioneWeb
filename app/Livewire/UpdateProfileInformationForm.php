<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm as JetstreamUpdateProfileInformationForm;

/**
 * Gestisce il form di aggiornamento del profilo utente e la rimozione della foto con redirect.
 */
class UpdateProfileInformationForm extends JetstreamUpdateProfileInformationForm
{
    /**
     * Salva il profilo e poi toglie la password attuale (richiesta per cambiare email) dallo stato del componente,
     * così non resta nello snapshot di Livewire inviato al browser.
     */
    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        try {
            return parent::updateProfileInformation($updater);
        } finally {
            unset($this->state['current_password']);
        }
    }

    /**
     * Elimina la foto profilo dell'utente, imposta la notifica e reindirizza alla pagina del profilo.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfilePhoto()
    {
        Auth::user()->deleteProfilePhoto();

        session()->flash('profile_updated', 'Informazioni del profilo modificate correttamente.');

        return redirect()->route('profile.show');
    }
}
