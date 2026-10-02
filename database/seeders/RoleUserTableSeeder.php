<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $super = Role::where('title', 'Super administrateur')->first();

        if ($admin && $super) {
            $admin->roles()->sync([$super->id]);
        }
    }
}
