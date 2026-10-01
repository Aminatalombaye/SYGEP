<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le module « Attribution » ne faisait que typer les bons d'affectation.
 * Le type devient un champ du bon ; l'ancienne table est conservée pour l'historique.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('assignments', 'type')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->string('type', 20)->default('dotation')->after('reference')->index();
            });
        }

        if (! Schema::hasTable('assignment_attribution') || ! Schema::hasTable('attributions')) {
            return;
        }

        $rows = DB::table('assignment_attribution')
            ->join('attributions', 'attributions.id', '=', 'assignment_attribution.attribution_id')
            ->get(['assignment_attribution.assignment_id', 'attributions.type_atribution', 'attributions.nom']);

        foreach ($rows as $row) {
            DB::table('assignments')
                ->where('id', $row->assignment_id)
                ->update(['type' => $this->map($row->type_atribution ?: $row->nom)]);
        }
    }

    private function map(?string $label): string
    {
        $value = mb_strtolower(trim((string) $label));

        return match (true) {
            str_contains($value, 'réserv'), str_contains($value, 'reserv') => 'reservation',
            str_contains($value, 'format') => 'formation',
            str_contains($value, 'programme'), str_contains($value, 'projet') => 'programme',
            str_contains($value, 'mainten'), str_contains($value, 'répar'), str_contains($value, 'repar') => 'maintenance',
            str_contains($value, 'usage'), str_contains($value, 'temporaire'), str_contains($value, 'prêt'), str_contains($value, 'pret') => 'temporaire',
            default => 'dotation',
        };
    }

    public function down(): void
    {
        if (Schema::hasColumn('assignments', 'type')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }
    }
};
