<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('location_id')->constrained('agents')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('agent_id')->constrained('services')->nullOnDelete();
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'reference')) {
                $table->string('reference', 30)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('assignments', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'location_id')) {
                $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'assigned_at')) {
                $table->date('assigned_at')->nullable();
            }
            if (! Schema::hasColumn('assignments', 'expected_return_at')) {
                $table->date('expected_return_at')->nullable();
            }
            if (! Schema::hasColumn('assignments', 'closed_at')) {
                $table->dateTime('closed_at')->nullable();
            }
            if (! Schema::hasColumn('assignments', 'status')) {
                $table->string('status', 20)->default('en_cours')->index();
            }
            if (! Schema::hasColumn('assignments', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('assignments', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('asset_assignment', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_assignment', 'returned_at')) {
                $table->dateTime('returned_at')->nullable();
            }
            if (! Schema::hasColumn('asset_assignment', 'return_condition')) {
                $table->string('return_condition', 20)->nullable();
            }
            if (! Schema::hasColumn('asset_assignment', 'return_notes')) {
                $table->string('return_notes')->nullable();
            }
            if (! Schema::hasColumn('asset_assignment', 'returned_by_id')) {
                $table->foreignId('returned_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('assets_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('assets_histories', 'action')) {
                $table->string('action', 20)->nullable()->index();
            }
            if (! Schema::hasColumn('assets_histories', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets_histories', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets_histories', 'assignment_id')) {
                $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets_histories', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets_histories', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        if (! DB::table('asset_statuses')->where('name', 'Assigned')->exists()) {
            DB::table('asset_statuses')->insert([
                'name'       => 'Assigned',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('assignments')->whereNull('assigned_at')->update([
            'assigned_at' => DB::raw('DATE(created_at)'),
        ]);

        DB::table('assignments')->whereNull('reference')->update([
            'reference' => DB::raw("CONCAT('AFF-', LPAD(id, 5, '0'))"),
        ]);

        $permissionId = DB::table('permissions')->where('title', 'assignment_return')->value('id')
            ?? DB::table('permissions')->insertGetId([
                'title'      => 'assignment_return',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        foreach ([1, 2] as $roleId) {
            if (DB::table('roles')->where('id', $roleId)->exists()
                && ! DB::table('permission_role')->where(['role_id' => $roleId, 'permission_id' => $permissionId])->exists()) {
                DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        $dropForeign = function (string $tableName, array $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        };

        $dropForeign('assets_histories', ['agent_id', 'service_id', 'assignment_id', 'user_id']);
        Schema::table('assets_histories', fn (Blueprint $t) => $t->dropColumn(['action', 'notes']));

        $dropForeign('asset_assignment', ['returned_by_id']);
        Schema::table('asset_assignment', fn (Blueprint $t) => $t->dropColumn(['returned_at', 'return_condition', 'return_notes']));

        $dropForeign('assignments', ['agent_id', 'service_id', 'location_id', 'created_by_id']);
        Schema::table('assignments', fn (Blueprint $t) => $t->dropColumn(['reference', 'assigned_at', 'expected_return_at', 'closed_at', 'status', 'notes']));

        $dropForeign('assets', ['agent_id', 'service_id']);

        $ids = DB::table('permissions')->where('title', 'assignment_return')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
