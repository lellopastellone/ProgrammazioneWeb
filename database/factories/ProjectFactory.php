<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory per la generazione di progetti software dimostrativi.
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    private const SAMPLE_PROJECTS = [
        ['Piattaforma E-commerce B2B', 'Nuovo portale per ordini all\'ingrosso con listini personalizzati, gestione ordini e fatturazione elettronica.'],
        ['Sistema Gestione Assistenza', 'Helpdesk aziendale interno per il tracciamento dei ticket di supporto tecnico e rispetto degli SLA contrattuali.'],
        ['App Mobile Rilevazione Presenze', 'Applicazione per la timbratura geolocalizzata e gestione delle richieste di ferie e permessi dei dipendenti.'],
        ['Dashboard Analytics Direzionale', 'Pannello di controllo con indicatori chiave di performance (KPI), grafici di avanzamento e reportistica vendite.'],
    ];

    /**
     * Definisce lo stato predefinito del modello Project con dati casuali verosimili.
     */
    public function definition(): array
    {
        $sample = fake()->randomElement(self::SAMPLE_PROJECTS);

        return [
            'name' => $sample[0] . ' ' . fake()->unique()->lexify('????'),
            'description' => $sample[1],
            'status' => ProjectStatus::InProgress,
            'created_by' => User::factory(),
            'pm_id' => null,
        ];
    }

    /**
     * Imposta lo stato del modello assegnando uno specifico Project Manager.
     */
    public function managedBy(User $pm): static
    {
        return $this->state(['pm_id' => $pm->id]);
    }
}
