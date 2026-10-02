<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Rôle « User » : attribué aux comptes qui s'inscrivent eux-mêmes. Il ne reçoit que les droits
 * du profil le plus restreint (Agent / demandeur) et ne donne accès à rien tant qu'un
 * administrateur n'a pas approuvé le compte et choisi son rôle.
 */
class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        $user = Role::firstOrCreate(['title' => 'User']);
        $agent = Role::where('title', 'Agent / demandeur')->first();

        $ids = $agent
            ? $agent->permissions()->pluck('permissions.id')
            : Permission::whereIn('title', ['profile_password_edit', 'user_alert_access', 'user_alert_show'])->pluck('id');

        $user->permissions()->sync($ids);
    }
}
