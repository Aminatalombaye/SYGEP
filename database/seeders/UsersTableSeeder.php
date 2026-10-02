<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'approved' => 1]
        );
    }
}
