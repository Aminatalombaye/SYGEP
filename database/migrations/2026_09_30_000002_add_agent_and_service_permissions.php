<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        'agent_access', 'agent_create', 'agent_edit', 'agent_show', 'agent_delete',
        'service_access', 'service_create', 'service_edit', 'service_show', 'service_delete',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $title) {
            $id = DB::table('permissions')->where('title', $title)->value('id')
                ?? DB::table('permissions')->insertGetId([
                    'title'      => $title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            foreach ([1, 2] as $roleId) {
                if (DB::table('roles')->where('id', $roleId)->exists()
                    && ! DB::table('permission_role')->where(['role_id' => $roleId, 'permission_id' => $id])->exists()) {
                    DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $id]);
                }
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('title', $this->permissions)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
