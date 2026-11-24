<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea e sincronizza i ruoli e i permessi Spatie previsti dal sistema RBAC.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public const ROLES = [
        'organizer' => ['users.manage', 'projects.create', 'projects.assign-manager', 'projects.view-all'],
        'project_manager' => ['projects.manage'],
        'member' => ['tasks.update-status', 'comments.create'],
    ];

    /**
     * Svuota la cache delle autorizzazioni e crea ruoli e permessi associati.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role => $permissions) {
            // Si passano i model, non i nomi: la cache di Spatie non vede ancora i permessi appena creati.
            Role::findOrCreate($role)->syncPermissions(
                array_map(fn (string $name) => Permission::findOrCreate($name), $permissions)
            );
        }
    }
}
