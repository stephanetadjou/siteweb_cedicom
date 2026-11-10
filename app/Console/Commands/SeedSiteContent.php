<?php

namespace App\Console\Commands;

use App\Models\HeroSlide;
use App\Models\Pole;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Console\Command;

/**
 * Charge les contenus par défaut de la vitrine seulement si la base est vide.
 * À utiliser au déploiement : les modifications faites dans le back office ne sont jamais écrasées.
 */
class SeedSiteContent extends Command
{
    protected $signature = 'cedicom:seed-content';

    protected $description = 'Charge les contenus par défaut de la vitrine si la base est vide (sans écraser le back office)';

    public function handle(): int
    {
        if (Pole::exists()) {
            $this->info('Contenus déjà présents : rien à faire (ils se gèrent dans le back office).');

            if (! HeroSlide::exists()) {
                // Base remplie avant l'arrivée du diaporama en base : on ajoute seulement les diapositives.
                (new SiteContentSeeder)->seedHeroSlidesOnly();
                $this->info('Diaporama par défaut ajouté.');
            }

            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => SiteContentSeeder::class, '--force' => true]);

        return self::SUCCESS;
    }
}
