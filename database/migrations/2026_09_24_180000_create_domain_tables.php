<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrazione principale per le tabelle di dominio dell'applicazione:
 * team, progetti, assegnazioni a gruppi di progetto, task, assegnatari,
 * commenti, iterazioni/sprint, domini email aziendali e configurazione SMTP.
 */
return new class extends Migration
{
    /**
     * Tabelle di dominio (schema ER + iterations).
     * Gli utenti e i progetti non si cancellano mai, quindi le FK verso users sono restrict.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // UserTeam
        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->primary(['team_id', 'user_id']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('planned');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('pm_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // ProjectGroup
        Schema::create('project_user', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->primary(['project_id', 'user_id']);
        });

        Schema::create('iterations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('iteration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('todo');
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->unique(['project_id', 'title']);
        });

        // UserTask
        Schema::create('task_user', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->primary(['task_id', 'user_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('text');
            $table->timestamps();
        });

        // Domini email aziendali, scelti al setup: le email degli utenti devono appartenere a uno di questi.
        Schema::create('email_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->timestamps();
        });

        // Server SMTP scelto al setup (una sola riga): sostituisce la configurazione mail del .env.
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
        Schema::dropIfExists('email_domains');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('iterations');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
