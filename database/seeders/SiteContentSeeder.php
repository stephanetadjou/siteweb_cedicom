<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\PartnerBrand;
use App\Models\Pole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contenus de la vitrine : pôles, marques partenaires, divisions et enseignes.
 * Les textes sont bilingues ({"fr": ..., "en": ...}).
 * Idempotent : les tables sont vidées puis rechargées à chaque exécution.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Pole::query()->delete();
            PartnerBrand::query()->delete();
            Division::query()->delete(); // supprime aussi les enseignes (cascade)

            $this->seedPartnerBrands();
            $this->seedPoles();
            $this->seedDivisions();
        });
    }

    private function seedPartnerBrands(): void
    {
        $brands = [
            // Partenaires majeurs en tête du mur des marques
            ['FrieslandCampina', 'food', 'frieslandcampina.png'],
            ['Danone Nutricia', 'food', 'danone-nutricia.png'],
            ['Coca-Cola', 'food', 'coca-cola.png'],
            ['Barilla', 'food', 'barilla.png'],
            ['Riz parfumé Ma Famille', 'food', 'riz-parfume-ma-famille.png'],
            ['Pampers', 'pgc', 'pampers.png'],
            ['Oral-B', 'pgc', 'oral-b.png'],
            ['Gillette', 'pgc', 'gillette.png'],
            ['Always', 'pgc', 'always.png'],
            ['ORO', 'food', 'oro.png'],
            ['Head & Shoulders', 'pgc', 'head-and-shoulders.png'],
            ['Ariel', 'pgc', 'ariel.png'],
            ['Countre Dairy', 'food', 'countre-dairy.png'],
            ['Ciao', 'food', 'ciao.png'],
            ['Bavaria', 'food', 'bavaria.png'],
            ['Power Malt', 'food', 'power-malt.png'],
            ['Mutlu', 'food', 'mutlu.png'],
            ['Lago', 'food', 'lago.png'],
            ['Indomie', 'food', 'indomie.png'],
            ['Mentos', 'food', 'mentos.png'],
            ['Chupa Chups', 'food', 'chupa-chups.jpg'],
            ['Deemah', 'food', 'deemah.jpg'],
            ['Chatel', 'food', 'chatel.jpg'],
            ['Yogu+', 'food', 'yogu.jpg'],
            ['Coroos', 'food', 'coroos.jpg'],
            ['Niers', 'food', 'niers.jpg'],
            ['Camper Gaz', 'pgc', 'camper-gaz.jpg'],
            ['Tom', 'food', 'tom.jpg'],
            ['Selpak', 'pgc', 'selpak.jpg'],
            ['Master', 'food', 'master.jpg'],
        ];

        foreach ($brands as $i => [$name, $category, $file]) {
            PartnerBrand::create([
                'name' => $name,
                'category' => $category,
                'logo' => 'images/partenaires/'.$file,
                'sort' => $i,
            ]);
        }
    }

    private function seedPoles(): void
    {
        $poles = [
            [
                'title' => ['fr' => 'FMCG & produits essentiels', 'en' => 'FMCG & essential goods'],
                'description' => [
                    'fr' => 'Hygiène, entretien, épicerie, boissons et confiserie pour boutiques, supérettes et grossistes.',
                    'en' => 'Personal care, household, groceries, beverages and confectionery for shops, mini-markets and wholesalers.',
                ],
                'image' => 'images/poles/fmcg-logos.jpg',
                'image_position' => 'center',
                'logos' => [
                    ['FrieslandCampina', 'images/partenaires/frieslandcampina.png'],
                    ['Danone Nutricia', 'images/partenaires/danone-nutricia.png'],
                    ['Coca-Cola', 'images/partenaires/coca-cola.png'],
                    ['Pampers', 'images/partenaires/pampers.png'],
                    ['Ariel', 'images/partenaires/ariel.png'],
                ],
                'round_logos' => false,
                'tags' => [
                    'fr' => 'FrieslandCampina · Danone · Coca-Cola · Pampers · +26 marques',
                    'en' => 'FrieslandCampina · Danone · Coca-Cola · Pampers · +26 brands',
                ],
                'anchor' => 'marques',
            ],
            [
                'title' => ['fr' => 'Mode et Lifestyle', 'en' => 'Fashion & Lifestyle'],
                'description' => [
                    'fr' => 'Franchises de mode internationales et concepts propres à Libreville.',
                    'en' => 'International fashion franchises and in-house concepts in Libreville.',
                ],
                'image' => 'images/divisions/mode-lifestyle.jpg',
                'image_position' => 'center 30%',
                'logos' => [
                    ['Mango', 'images/enseignes/mango.png'],
                    ['adidas', 'images/enseignes/adidas.png'],
                    ['Carré Maison de Mode', 'images/enseignes/carre.png'],
                    ['Grand Store', 'images/enseignes/grand-store.png'],
                ],
                'round_logos' => false,
                'tags' => ['fr' => 'Mango · adidas · Carré · Grand Store', 'en' => 'Mango · adidas · Carré · Grand Store'],
                'anchor' => 'divisions',
            ],
            [
                'title' => ['fr' => 'Restauration et Hospitalité', 'en' => 'Food Service & Hospitality'],
                'description' => [
                    'fr' => 'Restaurants, cafés et restauration rapide, avec une cuisine de production centralisée.',
                    'en' => 'Restaurants, cafés and quick-service dining, backed by a central production kitchen.',
                ],
                'image' => 'images/restaurants/resto-paul.jpg',
                'image_position' => 'center 60%',
                'logos' => [
                    ['PAUL', 'images/enseignes/paul.png'],
                    ['KFC', 'images/enseignes/kfc.png'],
                    ['Pizza Hut', 'images/enseignes/pizza-hut.png'],
                    ['Pinkberry', 'images/enseignes/pinkberry.png'],
                    ['Sushi Bar', 'images/enseignes/sushi-bar.png'],
                ],
                'round_logos' => true,
                'tags' => ['fr' => 'PAUL · KFC · Pizza Hut · +8 enseignes', 'en' => 'PAUL · KFC · Pizza Hut · +8 brands'],
                'anchor' => 'divisions',
            ],
        ];

        foreach ($poles as $i => $pole) {
            Pole::create($pole + ['sort' => $i]);
        }
    }

    private function seedDivisions(): void
    {
        $divisions = [
            [
                'slug' => 'mode-lifestyle',
                'title' => ['fr' => 'Mode et Lifestyle', 'en' => 'Fashion & Lifestyle'],
                'description' => [
                    'fr' => 'Nous développons et exploitons des concepts de retail mode et lifestyle, apportant des marques internationales et des expériences shopping soignées aux marchés africains émergents.',
                    'en' => 'We develop and operate fashion and lifestyle retail concepts, bringing international brands and carefully designed shopping experiences to emerging African markets.',
                ],
                'image' => 'images/divisions/mode-lifestyle.jpg',
                'image_alt' => [
                    'fr' => 'Boutiques Mango, adidas et Carré à Libreville',
                    'en' => 'Mango, adidas and Carré stores in Libreville',
                ],
                'categories' => [
                    'fr' => ['Franchises de mode', 'Vêtements de sport', 'Retail premium', 'Concepts outlet', 'Boutiques lifestyle'],
                    'en' => ['Fashion franchises', 'Sportswear', 'Premium retail', 'Outlet concepts', 'Lifestyle stores'],
                ],
                'enseignes' => [
                    'franchise' => [['Mango', 'mango.png'], ['adidas', 'adidas.png']],
                    'concept' => [['Carré', 'carre.png'], ['Grand Store', 'grand-store.png']],
                ],
            ],
            [
                'slug' => 'restauration-hospitalite',
                'title' => ['fr' => 'Restauration et Hospitalité', 'en' => 'Food Service & Hospitality'],
                'description' => [
                    'fr' => "Nous exploitons et développons des restaurants, des cafés, des concepts de restauration rapide et des expériences d'hospitalité conçus pour les villes africaines modernes. Une cuisine de production centralisée soutient nos marques avec une qualité constante, des recettes standardisées et une coordination logistique efficace entre les points de vente.",
                    'en' => 'We operate and develop restaurants, cafés, quick-service concepts and hospitality experiences designed for modern African cities. A central production kitchen supports our brands with consistent quality, standardised recipes and efficient logistics across all outlets.',
                ],
                'image' => 'images/restaurants/resto-paul.jpg',
                'image_alt' => [
                    'fr' => 'Terrasse du restaurant PAUL Boulangerie Café à Libreville',
                    'en' => 'The terrace of the PAUL bakery café restaurant in Libreville',
                ],
                'gallery' => [
                    [
                        'image' => 'images/restaurants/resto-kfc.jpg',
                        'alt' => ['fr' => 'Façade du restaurant KFC à Libreville', 'en' => 'The KFC restaurant front in Libreville'],
                    ],
                    [
                        'image' => 'images/restaurants/resto-grill-sushi.jpg',
                        'alt' => ['fr' => 'Salle du restaurant Grill & Sushi avec sa cuisine ouverte', 'en' => 'The Grill & Sushi dining room and its open kitchen'],
                    ],
                ],
                'categories' => [
                    'fr' => ['Cafés et boulangeries', 'Restauration rapide', 'Restauration décontractée', 'Restaurants signature', 'Marques alimentaires locales', 'Concepts prêts pour la livraison', 'Cuisine centrale'],
                    'en' => ['Cafés & bakeries', 'Quick-service restaurants', 'Casual dining', 'Signature restaurants', 'Local food brands', 'Delivery-ready concepts', 'Central kitchen'],
                ],
                'enseignes' => [
                    'franchise' => [
                        ['PAUL', 'paul.png'], ['PAUL Le Café', 'paul-le-cafe.png'], ['KFC', 'kfc.png'],
                        ['Pizza Hut', 'pizza-hut.png'], ['Pinkberry', 'pinkberry.png'], ['the coffee.', 'the-coffee.png'],
                    ],
                    'concept' => [
                        ['MUN', 'mun.png'], ['Sushi Bar', 'sushi-bar.png'], ['Zaytouna', 'zaytouna.png'],
                        ['BURGYS', 'burgys.png'], ["Jardin D'amin", 'jardin-d-amin.png'],
                    ],
                ],
            ],
        ];

        foreach ($divisions as $i => $data) {
            $enseignes = $data['enseignes'];
            unset($data['enseignes']);

            $division = Division::create($data + ['sort' => $i]);

            foreach ($enseignes as $type => $items) {
                foreach ($items as $j => [$name, $file]) {
                    $division->enseignes()->create([
                        'name' => $name,
                        'type' => $type,
                        'logo' => 'images/enseignes/'.$file,
                        'sort' => $j,
                    ]);
                }
            }
        }
    }
}
