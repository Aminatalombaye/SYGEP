<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'contact_message_access',
        'contact_message_show',
        'contact_message_delete',
    ];

    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('structure')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        foreach ($this->permissions as $title) {
            $id = DB::table('permissions')->where('title', $title)->value('id')
                ?? DB::table('permissions')->insertGetId([
                    'title'      => $title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            if (DB::table('roles')->where('id', 1)->exists()
                && ! DB::table('permission_role')->where(['role_id' => 1, 'permission_id' => $id])->exists()) {
                DB::table('permission_role')->insert(['role_id' => 1, 'permission_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('title', $this->permissions)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        Schema::dropIfExists('contact_messages');
    }
};
