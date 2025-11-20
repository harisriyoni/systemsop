<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin','produksi','qa','logistik','operator'];

        foreach ($roles as $role) {
            User::updateOrCreate(
                ['email' => $role.'@demo.test'],
                [
                    'name'     => ucfirst($role).' Demo',
                    'password' => bcrypt('password'),
                    'role'     => $role,
                ]
            );
        }
    }
}
