<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal. Las cinco categorías se insertan en la migración
 * (data-model.md §9); este seeder queda vacío a propósito: el sembrador
 * de demostración (T-21) usa la API, nunca SQL directo.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El admin inicial se crea en el arranque (T-17), no aquí.
        // Las categorías se crean en la migración inicial.
    }
}
