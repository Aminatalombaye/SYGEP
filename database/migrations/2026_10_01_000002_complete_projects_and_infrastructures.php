<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Projets : référence, type, budget, avancement, jalons et intervenants.
 * Infrastructures : nature (bâtiment / bloc / structure), état, valeur et amortissement.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->projects();
        $this->milestones();
        $this->intervenants();
        $this->infrastructures();
    }

    private function projects(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'reference')) {
                $table->string('reference', 20)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('projects', 'type')) {
                $table->string('type', 20)->default('construction')->after('name');
            }
            if (! Schema::hasColumn('projects', 'budget')) {
                $table->decimal('budget', 15, 2)->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('projects', 'spent')) {
                $table->decimal('spent', 15, 2)->nullable()->after('budget');
            }
            if (! Schema::hasColumn('projects', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('spent');
            }
            if (! Schema::hasColumn('projects', 'completed_at')) {
                $table->date('completed_at')->nullable()->after('progress');
            }
            if (! Schema::hasColumn('projects', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->string('status', 20)->default('planifie')->change();
        });

        $year = [];
        foreach (DB::table('projects')->orderBy('id')->get() as $project) {
            $value = mb_strtolower(trim((string) $project->status));
            $status = match (true) {
                str_contains($value, 'termin'), str_contains($value, 'achev'), str_contains($value, 'clôtur'), str_contains($value, 'livr') => 'termine',
                str_contains($value, 'suspend'), str_contains($value, 'arrêt') => 'suspendu',
                str_contains($value, 'annul') => 'annule',
                str_contains($value, 'cours'), str_contains($value, 'construction'), str_contains($value, 'travaux') => 'en_cours',
                default => 'planifie',
            };

            $text = mb_strtolower($project->name.' '.$project->description);
            $type = match (true) {
                str_contains($text, 'réhabilit'), str_contains($text, 'rehabilit'), str_contains($text, 'rénov'), str_contains($text, 'renov') => 'rehabilitation',
                str_contains($text, 'extension'), str_contains($text, 'agrandi') => 'extension',
                str_contains($text, 'équipement'), str_contains($text, 'informatique'), str_contains($text, 'modernis') => 'equipement',
                default => 'construction',
            };

            $y = $project->start_date ? substr($project->start_date, 0, 4) : substr((string) $project->created_at, 0, 4);
            $year[$y] = ($year[$y] ?? 0) + 1;

            DB::table('projects')->where('id', $project->id)->update(array_filter([
                'status'       => $status,
                'type'         => $project->type && $project->type !== 'construction' ? null : $type,
                'reference'    => $project->reference ?: sprintf('PRJ-%s-%02d', $y ?: date('Y'), $year[$y]),
                'progress'     => $status === 'termine' ? 100 : null,
                'completed_at' => $status === 'termine' ? ($project->end_date ?: now()->toDateString()) : null,
            ], fn ($v) => $v !== null));
        }
    }

    private function milestones(): void
    {
        if (Schema::hasTable('project_milestones')) {
            return;
        }

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('weight')->default(1);
            $table->date('done_at')->nullable();
            $table->unsignedBigInteger('intervenant_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    private function intervenants(): void
    {
        if (! Schema::hasTable('intervenants')) {
            Schema::create('intervenants', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('prenom')->nullable();
                $table->string('organisation')->nullable();
                $table->string('role', 30)->default('entreprise');
                $table->string('telephone', 40)->nullable();
                $table->string('email')->nullable();
                $table->string('adresse')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('intervenant_project')) {
            Schema::create('intervenant_project', function (Blueprint $table) {
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('intervenant_id')->constrained('intervenants')->cascadeOnDelete();
                $table->string('mission')->nullable();
                $table->primary(['project_id', 'intervenant_id']);
            });
        }

        Schema::table('project_milestones', function (Blueprint $table) {
            $table->foreign('intervenant_id')->references('id')->on('intervenants')->nullOnDelete();
        });
    }

    private function infrastructures(): void
    {
        Schema::table('infrastructures', function (Blueprint $table) {
            if (! Schema::hasColumn('infrastructures', 'nature')) {
                $table->string('nature', 20)->default('batiment')->after('name');
            }
            if (! Schema::hasColumn('infrastructures', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('nature')->constrained('infrastructures')->nullOnDelete();
            }
            if (! Schema::hasColumn('infrastructures', 'condition')) {
                $table->string('condition', 20)->nullable()->after('status');
            }
            if (! Schema::hasColumn('infrastructures', 'last_inspection_at')) {
                $table->date('last_inspection_at')->nullable()->after('condition');
            }
            if (! Schema::hasColumn('infrastructures', 'surface')) {
                $table->decimal('surface', 10, 2)->nullable()->after('location');
            }
            if (! Schema::hasColumn('infrastructures', 'acquisition_value')) {
                $table->decimal('acquisition_value', 15, 2)->nullable()->after('construction_date');
            }
            if (! Schema::hasColumn('infrastructures', 'depreciation_years')) {
                $table->unsignedSmallInteger('depreciation_years')->nullable()->after('acquisition_value');
            }
        });

        Schema::table('infrastructures', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('location')->nullable()->change();
            $table->date('construction_date')->nullable()->change();
            $table->string('status', 20)->default('en_service')->change();
        });

        foreach (DB::table('infrastructures')->get() as $infra) {
            $value = mb_strtolower(trim((string) $infra->status));
            $status = match (true) {
                str_contains($value, 'construction') => 'en_construction',
                str_contains($value, 'réhabilit'), str_contains($value, 'rehabilit'), str_contains($value, 'rénov') => 'en_rehabilitation',
                str_contains($value, 'maintenance'), str_contains($value, 'répar') => 'en_maintenance',
                str_contains($value, 'hors'), str_contains($value, 'ferm'), str_contains($value, 'désaffect') => 'hors_service',
                default => 'en_service',
            };

            $type = mb_strtolower((string) $infra->type.' '.$infra->name);
            $nature = match (true) {
                str_contains($type, 'bloc'), str_contains($type, 'salle'), str_contains($type, 'atelier') => 'bloc',
                str_contains($type, 'centre'), str_contains($type, 'lycée'), str_contains($type, 'lycee'), str_contains($type, 'structure'), str_contains($type, 'institut') => 'structure',
                default => 'batiment',
            };

            preg_match('/\d+/', (string) $infra->depreciation_plan, $m);

            DB::table('infrastructures')->where('id', $infra->id)->update([
                'status'             => $status,
                'nature'             => $infra->nature && $infra->nature !== 'batiment' ? $infra->nature : $nature,
                'depreciation_years' => $infra->depreciation_years ?: ($m[0] ?? null),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('project_milestones', fn (Blueprint $t) => $t->dropForeign(['intervenant_id']));
        Schema::dropIfExists('intervenant_project');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('intervenants');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'type', 'budget', 'spent', 'progress', 'completed_at']);
        });

        Schema::table('infrastructures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['nature', 'condition', 'last_inspection_at', 'surface', 'acquisition_value', 'depreciation_years']);
        });
    }
};
