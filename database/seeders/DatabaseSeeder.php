<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Compte de test uniquement en local : la factory a besoin de Faker (dépendance de dev).
        if (app()->isLocal()) {
            User::firstOrCreate(['email' => 'test@example.com'], User::factory()->raw([
                'name' => 'Test User',
            ]));
        }

        $this->call(SiteContentSeeder::class);
    }
}
