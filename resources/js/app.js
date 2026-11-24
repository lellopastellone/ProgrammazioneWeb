import { Modal } from 'bootstrap';

// ========================================================================================
// 1. MODALE DI CONFERMA PER AZIONI DISTRUTTIVE (FORM HTML)
// Intercetta i form che hanno l'attributo data-confirm="Messaggio" (es. eliminazione).
// Invece di inviare subito la richiesta, blocca il submit e apre la modale #confirm-modal.
// ========================================================================================
document.addEventListener('submit', (event) => {
    const form = event.target;
    const dialog = document.getElementById('confirm-modal');

    // Se il form non richiede conferma o se la finestra modale non esiste nella pagina, lascia proseguire normalmente.
    if (!form.matches('form[data-confirm]') || !dialog) return;

    // Blocca l'invio immediato del form al server.
    event.preventDefault();

    // Recupera il pulsante di conferma e imposta il testo del messaggio personalizzato.
    const ok = dialog.querySelector('[data-confirm-ok]');
    dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
    ok.textContent = form.dataset.confirmButton ?? 'Conferma';

    // All'effettivo clic su "Conferma", invia il form (il metodo nativo form.submit() non riattiva questo listener).
    ok.onclick = () => form.submit();

    // Mostra a video la modale grafica di Bootstrap.
    Modal.getOrCreateInstance(dialog).show();
});

// ========================================================================================
// 2. HELPER GLOBALE PER CONFERMA AZIONI DA JAVASCRIPT
// Funzione globale riutilizzabile da script JS o Livewire per aprire la stessa modale
// prima di eseguire un'azione (callback) passata come parametro.
// ========================================================================================
window.confirmAction = (message, onConfirm, buttonText = 'Conferma') => {
    const dialog = document.getElementById('confirm-modal');

    // Piano di riserva (fallback): se la modale HTML non è presente, usa il popup nativo del browser.
    if (!dialog) {
        if (window.confirm(message)) onConfirm();
        return;
    }

    // Configura il messaggio di avviso e il testo del pulsante.
    const ok = dialog.querySelector('[data-confirm-ok]');
    dialog.querySelector('[data-confirm-message]').textContent = message;
    ok.textContent = buttonText;

    const modal = Modal.getOrCreateInstance(dialog);

    // Quando l'utente clicca su "Conferma", chiude la modale ed esegue la callback.
    ok.onclick = () => {
        modal.hide();
        onConfirm();
    };

    modal.show();
};

// ========================================================================================
// 3. TABELLE ORDINABILI LATO CLIENT (<x-sort-th>)
// Al clic sull'intestazione di una colonna, riordina istantaneamente le righe
// della tabella direttamente nel browser, senza effettuare richieste al server.
// ========================================================================================
document.addEventListener('click', (event) => {
    // Intercetta solo i clic su link di intestazione con attributo data-sort.
    const link = event.target.closest('th [data-sort]');
    if (!link) return;

    // Impedisce al link di ricaricare la pagina.
    event.preventDefault();

    const th = link.closest('th');
    const table = th.closest('table');

    // Inverte l'ordine: se non era crescente, ordina in modo crescente (A-Z), altrimenti decrescente (Z-A).
    const ascending = th.getAttribute('aria-sort') !== 'ascending';

    // Funzione helper: estrae il valore della cella (legge prima data-sort-value, altrimenti il testo visibile).
    const value = (row) => (row.cells[th.cellIndex].dataset.sortValue ?? row.cells[th.cellIndex].textContent).trim();

    // Ordina le righe del tbody usando le regole alfabetiche e numeriche italiane.
    const rows = [...table.tBodies[0].rows].sort((a, b) =>
        value(a).localeCompare(value(b), 'it', { numeric: true, sensitivity: 'base' }) * (ascending ? 1 : -1));

    // Reinserisce le righe ordinate nel corpo della tabella (aggiornamento grafico immediato).
    table.tBodies[0].append(...rows);

    // Aggiorna gli attributi di accessibilità e le icone delle freccette su tutte le colonne.
    table.querySelectorAll('th[aria-sort]').forEach((other) => {
        const state = other === th ? (ascending ? 'ascending' : 'descending') : 'none';
        other.setAttribute('aria-sort', state);
        other.querySelector('[data-sort]').className = `text-decoration-none text-nowrap ${state === 'none' ? 'link-secondary' : 'link-body-emphasis'}`;
        other.querySelector('i').className = `bi ms-1 ${{ ascending: 'bi-caret-up-fill', descending: 'bi-caret-down-fill', none: 'bi-chevron-expand opacity-50' }[state]}`;
    });
});
