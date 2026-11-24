{{-- Campi del server di posta, per il setup e per mail-settings.edit. Parametro: $smtp (MailSetting::current()). --}}
<div class="row g-3">
    <div class="col-sm-8">
        <x-label for="smtp_host" value="Host SMTP" class="fw-medium mb-1 small" />
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-hdd-network"></i></span>
            <x-input id="smtp_host" type="text" name="smtp_host" :value="old('smtp_host', $smtp->host)" required placeholder="smtp.azienda.it" class="border-start-0" />
        </div>
        <x-input-error for="smtp_host" class="mt-1" />
    </div>
    <div class="col-sm-4">
        <x-label for="smtp_port" value="Porta" class="fw-medium mb-1 small" />
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-hash"></i></span>
            <x-input id="smtp_port" type="number" name="smtp_port" :value="old('smtp_port', $smtp->port)" required min="1" max="65535" placeholder="587" class="border-start-0" />
        </div>
        <x-input-error for="smtp_port" class="mt-1" />
    </div>
    <div class="col-sm-6">
        <x-label for="smtp_username" value="Utente SMTP" class="fw-medium mb-1 small" />
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-person"></i></span>
            <x-input id="smtp_username" type="text" name="smtp_username" :value="old('smtp_username', $smtp->username)" autocomplete="off" placeholder="user@azienda.it" class="border-start-0" />
        </div>
        <x-input-error for="smtp_username" class="mt-1" />
    </div>
    <div class="col-sm-6">
        <x-label for="smtp_password" value="Password SMTP" class="fw-medium mb-1 small" />
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-key"></i></span>
            <x-input id="smtp_password" type="password" name="smtp_password" autocomplete="new-password" :placeholder="$smtp->password ? '••••••••' : 'Password'" class="border-start-0" />
        </div>
        <x-input-error for="smtp_password" class="mt-1" />
    </div>
</div>

<div class="mt-3">
    <x-label for="smtp_from_address" value="Indirizzo mittente predefinito" class="fw-medium mb-1 small" />
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-send"></i></span>
        <x-input id="smtp_from_address" type="email" name="smtp_from_address" :value="old('smtp_from_address', $smtp->from_address)" required placeholder="noreply@azienda.it" class="border-start-0" />
    </div>
    <x-input-error for="smtp_from_address" class="mt-1" />
</div>

