<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de maintenance : cible (infrastructure ou matière), priorité et circuit
 * Soumise → Validée / Rejetée → Planifiée → En cours → Terminée.
 * Plans de maintenance préventive qui génèrent les demandes périodiquement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_plans')) {
            Schema::create('maintenance_plans', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('target_type', 20)->default('infrastructure');
                $table->foreignId('infrastructure_id')->nullable()->constrained('infrastructures')->nullOnDelete();
                $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
                $table->unsignedSmallInteger('frequency_months')->default(6);
                $table->date('next_due_at');
                $table->date('last_done_at')->nullable();
                $table->unsignedSmallInteger('lead_days')->default(7);
                $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $columns = [
                'reference'           => fn () => $table->string('reference', 20)->nullable()->unique()->after('id'),
                'title'               => fn () => $table->string('title')->nullable()->after('reference'),
                'kind'                => fn () => $table->string('kind', 20)->default('corrective')->after('title'),
                'target_type'         => fn () => $table->string('target_type', 20)->default('autre')->after('kind'),
                'infrastructure_id'   => fn () => $table->foreignId('infrastructure_id')->nullable()->after('target_type')->constrained('infrastructures')->nullOnDelete(),
                'asset_id'            => fn () => $table->foreignId('asset_id')->nullable()->after('infrastructure_id')->constrained('assets')->nullOnDelete(),
                'establishment'       => fn () => $table->string('establishment')->nullable()->after('asset_id'),
                'priority'            => fn () => $table->string('priority', 10)->default('normale')->after('establishment'),
                'requested_by_id'     => fn () => $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete(),
                'validated_by_id'     => fn () => $table->foreignId('validated_by_id')->nullable()->constrained('users')->nullOnDelete(),
                'validated_at'        => fn () => $table->dateTime('validated_at')->nullable(),
                'decision_notes'      => fn () => $table->text('decision_notes')->nullable(),
                'planned_for'         => fn () => $table->date('planned_for')->nullable(),
                'assigned_to_id'      => fn () => $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete(),
                'started_at'          => fn () => $table->dateTime('started_at')->nullable(),
                'completed_at'        => fn () => $table->dateTime('completed_at')->nullable(),
                'resolution'          => fn () => $table->text('resolution')->nullable(),
                'cost'                => fn () => $table->decimal('cost', 15, 2)->nullable(),
                'maintenance_plan_id' => fn () => $table->foreignId('maintenance_plan_id')->nullable()->constrained('maintenance_plans')->nullOnDelete(),
            ];

            foreach ($columns as $name => $add) {
                if (! Schema::hasColumn('maintenance_requests', $name)) {
                    $add();
                }
            }
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('created_by')->nullable()->change();
            $table->string('status', 20)->default('soumise')->change();
        });

        $perYear = [];
        foreach (DB::table('maintenance_requests')->orderBy('id')->get() as $req) {
            $value = mb_strtolower(trim((string) $req->status));
            $status = match (true) {
                str_contains($value, 'termin'), str_contains($value, 'résolu'), str_contains($value, 'resolu'), str_contains($value, 'clôtur'), str_contains($value, 'ferm'), str_contains($value, 'trait') => 'terminee',
                str_contains($value, 'rejet'), str_contains($value, 'refus') => 'rejetee',
                str_contains($value, 'cours') => 'en_cours',
                str_contains($value, 'planif') => 'planifiee',
                str_contains($value, 'valid') => 'validee',
                default => 'soumise',
            };

            $y = substr((string) $req->created_at, 0, 4) ?: date('Y');
            $perYear[$y] = ($perYear[$y] ?? 0) + 1;

            $requester = $req->created_by
                ? DB::table('users')->where('name', $req->created_by)->value('id')
                : null;

            DB::table('maintenance_requests')->where('id', $req->id)->update(array_filter([
                'status'          => $status,
                'reference'       => $req->reference ?: sprintf('DMT-%s-%03d', $y, $perYear[$y]),
                'title'           => $req->title ?: mb_substr((string) $req->description, 0, 120),
                'requested_by_id' => $req->requested_by_id ?: $requester,
                'completed_at'    => $status === 'terminee' ? ($req->updated_at ?? now()) : null,
            ], fn ($v) => $v !== null));
        }

        $this->permissions();
    }

    private function permissions(): void
    {
        $titles = [
            'maintenance_request_validate',
            'maintenance_plan_access', 'maintenance_plan_create', 'maintenance_plan_edit', 'maintenance_plan_show', 'maintenance_plan_delete',
        ];

        $admin = DB::table('roles')->whereRaw('LOWER(title) = ?', ['admin'])->value('id') ?? 1;

        foreach ($titles as $title) {
            $id = DB::table('permissions')->where('title', $title)->value('id')
                ?? DB::table('permissions')->insertGetId(['title' => $title, 'created_at' => now(), 'updated_at' => now()]);

            if (DB::table('roles')->where('id', $admin)->exists()
                && ! DB::table('permission_role')->where(['role_id' => $admin, 'permission_id' => $id])->exists()) {
                DB::table('permission_role')->insert(['role_id' => $admin, 'permission_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            foreach (['maintenance_plan_id', 'assigned_to_id', 'validated_by_id', 'requested_by_id', 'asset_id', 'infrastructure_id'] as $fk) {
                if (Schema::hasColumn('maintenance_requests', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'title', 'kind', 'target_type', 'establishment', 'priority', 'validated_at',
                'decision_notes', 'planned_for', 'started_at', 'completed_at', 'resolution', 'cost']);
        });

        Schema::dropIfExists('maintenance_plans');

        $ids = DB::table('permissions')->where('title', 'like', 'maintenance_plan_%')->orWhere('title', 'maintenance_request_validate')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
