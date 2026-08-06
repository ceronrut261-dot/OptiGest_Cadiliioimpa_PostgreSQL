<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $administrador = Role::firstOrCreate(['name' => 'administrador']);
        $tecnico = Role::firstOrCreate(['name' => 'tecnico']);
        $cotizador = Role::firstOrCreate(['name' => 'cotizador']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@cadiliompa.com'],
            ['name' => 'Administrador OptiGest', 'password' => 'password', 'email_verified_at' => now()]
        );
        $admin->assignRole($administrador);

        $tecnicoUser = User::firstOrCreate(
            ['email' => 'tecnico@cadiliompa.com'],
            ['name' => 'Tecnico de Campo', 'password' => 'password', 'email_verified_at' => now()]
        );
        $tecnicoUser->assignRole($tecnico);

        $cotizadorUser = User::firstOrCreate(
            ['email' => 'cotizador@cadiliompa.com'],
            ['name' => 'Cotizador', 'password' => 'password', 'email_verified_at' => now()]
        );
        $cotizadorUser->assignRole($cotizador);
    }
}
