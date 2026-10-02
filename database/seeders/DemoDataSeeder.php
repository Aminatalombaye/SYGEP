<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Assignment;
use App\Models\Bon;
use App\Models\ChefProjet;
use App\Models\Infrastructure;
use App\Models\Intervenant;
use App\Models\Inventaire;
use App\Models\MaintenancePlan;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Report;
use App\Models\Role;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskTag;
use App\Models\User;
use App\Services\AffectationService;
use App\Services\InventaireService;
use App\Services\MaintenanceWorkflow;
use App\Services\Notifier;
use App\Services\StockService;
use App\Services\StockVoucherService;
use App\Support\RoleProfiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Jeu de données de démonstration pour tester SYGEP de bout en bout.
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Les comptes de démonstration ont tous le mot de passe « password ».
 * Le seeder ne s'exécute qu'une fois : il s'arrête s'il détecte déjà ses données.
 */
class DemoDataSeeder extends Seeder
{
    private const MARKER_SERVICE = 'Direction de l\'Administration Générale et de l\'Équipement';

    public function run(): void
    {
        if (Service::where('name', self::MARKER_SERVICE)->exists()) {
            $this->command?->warn('Les données de démonstration existent déjà : rien à faire.');

            return;
        }

        RoleProfiles::apply();

        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();

        // Sur une installation neuve, le compte administrateur reçoit le profil complet.
        if ($superAdmin = Role::where('title', 'Super administrateur')->first()) {
            $admin->roles()->syncWithoutDetaching([$superAdmin->id]);
        }
        Auth::login($admin);

        $services = $this->services();
        $users = $this->users($services);
        $refs = $this->referentials();
        $agents = $this->agents($services);
        $assets = $this->assets($refs);
        $this->assignments($assets, $agents, $services, $refs['locations']);
        $this->stock($services, $agents, $refs['suppliers'], $refs['locations']);
        $infra = $this->infrastructures();
        $this->projects($infra, $admin);
        $this->maintenance($infra, $assets, $users);
        $this->inventaire($refs, $services);
        $this->tasksAndReports($users, $assets);
        $this->history($assets);

        Notifier::users(User::pluck('id'), 'Bienvenue dans SYGEP : un jeu de données de démonstration a été chargé.', route('admin.home'));

        Auth::logout();

        $this->command?->info('Données de démonstration chargées. Comptes : voir DemoDataSeeder (mot de passe « password »).');
    }

    /* ------------------------------------------------------------------ */

    private function services(): array
    {
        $names = [
            'dage'     => self::MARKER_SERVICE,
            'dfp'      => 'Direction de la Formation Professionnelle',
            'examens'  => 'Direction des Examens et Concours',
            'dsi'      => 'Cellule informatique',
            'cfp_thies' => 'CFP de Thiès',
            'cfp_kaff' => 'CFP de Kaffrine',
        ];

        return collect($names)->map(fn ($name) => Service::create(['name' => $name]))->all();
    }

    private function users(array $services): array
    {
        $accounts = [
            ['directeur', 'Mamadou Diop', 'directeur@sygep.test', 'Directeur', null],
            ['comptable', 'Awa Ndiaye', 'comptable@sygep.test', 'Comptable matière principal', null],
            ['secondaire', 'Ibrahima Sow', 'secondaire@sygep.test', 'Comptable matière secondaire', 'cfp_thies'],
            ['maintenance', 'Cheikh Fall', 'maintenance@sygep.test', 'Responsable maintenance', null],
            ['infra', 'Fatou Sarr', 'infrastructures@sygep.test', 'Responsable infrastructures', null],
            ['gestionnaire', 'Ousmane Ba', 'matieres@sygep.test', 'Administrateur des matières', null],
            ['agent', 'Khady Gueye', 'agent@sygep.test', 'Agent / demandeur', 'cfp_thies'],
        ];

        $users = [];

        foreach ($accounts as [$key, $name, $email, $roleTitle, $service]) {
            $user = User::create([
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make('password'),
                'approved'   => 1,
                'service_id' => $service ? $services[$service]->id : null,
            ]);

            if ($role = Role::where('title', $roleTitle)->first()) {
                $user->roles()->sync([$role->id]);
            }

            $users[$key] = $user;
        }

        return $users;
    }

    private function referentials(): array
    {
        $categories = collect([
            'Ordinateurs portables', 'Ordinateurs de bureau', 'Imprimantes et copieurs', 'Mobilier de bureau',
            'Climatiseurs', 'Véhicules', 'Matériel d\'atelier', 'Réseau et télécoms', 'Vidéoprojecteurs',
        ])->mapWithKeys(fn ($n) => [$n => AssetCategory::create(['name' => $n])])->all();

        $locations = collect([
            'siege'   => 'Siège — Bureaux DAGE',
            'serveur' => 'Siège — Salle serveur',
            'magasin' => 'Magasin central',
            'thies_a' => 'CFP de Thiès — Atelier',
            'thies_i' => 'CFP de Thiès — Salle informatique',
            'kaff'    => 'CFP de Kaffrine — Administration',
        ])->map(fn ($n) => AssetLocation::create(['name' => $n]))->all();

        $suppliers = collect([
            'Sénégal Informatique Services', 'Bureau Équipement Dakar', 'Frigo Confort Sénégal', 'Auto Plus Dakar', 'Technopro Atelier',
        ])->mapWithKeys(fn ($n, $i) => [$i => Supplier::create(['name' => $n, 'contact' => '33 8'.random_int(10, 99).' '.random_int(10, 99).' '.random_int(10, 99)])])->all();

        $bons = [];
        foreach ([
            ['BC-2025-014', 'Sénégal Informatique Services', 60, 'Awa Ndiaye'],
            ['BC-2025-027', 'Bureau Équipement Dakar', 40, 'Awa Ndiaye'],
            ['BC-2025-033', 'Frigo Confort Sénégal', 25, 'Cheikh Fall'],
            ['BC-2026-004', 'Auto Plus Dakar', 15, 'Mamadou Diop'],
            ['BC-2026-011', 'Technopro Atelier', 8, 'Ibrahima Sow'],
        ] as $i => [$ref, $org, $daysAgo, $dest]) {
            $bons[$i] = Bon::create([
                'date_emission'      => now()->subDays($daysAgo + 20)->format('Y-m-d'),
                'organisation'       => $org,
                'reference_commande' => $ref,
                'nom_destinataire'   => $dest,
                'bon'                => 'BL-'.substr($ref, 3),
                'date_livraison'     => now()->subDays($daysAgo)->format('Y-m-d'),
            ]);
        }

        // Un bon encore en attente de livraison.
        $bons[] = Bon::create([
            'date_emission'      => now()->subDays(5)->format('Y-m-d'),
            'organisation'       => 'Technopro Atelier',
            'reference_commande' => 'BC-2026-019',
            'nom_destinataire'   => 'Ibrahima Sow',
            'bon'                => 'BL-2026-019',
            'date_livraison'     => null,
        ]);

        return compact('categories', 'locations', 'suppliers', 'bons');
    }

    private function agents(array $services): array
    {
        $rows = [
            ['Ndiaye', 'Moussa', 'dage'], ['Diallo', 'Aminata', 'dage'], ['Sy', 'Pape', 'dfp'], ['Faye', 'Mariama', 'dfp'],
            ['Thiam', 'Abdou', 'examens'], ['Mbaye', 'Rokhaya', 'examens'], ['Kane', 'Samba', 'dsi'], ['Seck', 'Ndèye', 'dsi'],
            ['Gomis', 'Jean', 'cfp_thies'], ['Camara', 'Fanta', 'cfp_thies'], ['Ndao', 'Alioune', 'cfp_kaff'], ['Badiane', 'Coumba', 'cfp_kaff'],
        ];

        return collect($rows)->map(fn ($r, $i) => Agent::create([
            'nom'        => $r[0],
            'prenom'     => $r[1],
            'adresse'    => 'Dakar, Sénégal',
            'email'      => strtolower($r[1].'.'.$r[0]).'@mefpt.test',
            'telephone'  => '77 '.str_pad((string) (100 + $i * 7), 3, '0').' '.str_pad((string) (10 + $i), 2, '0').' '.str_pad((string) (20 + $i), 2, '0'),
            'service_id' => $services[$r[2]]->id,
        ]))->all();
    }

    /** @return array<string, Asset> */
    private function assets(array $refs): array
    {
        $cat = $refs['categories'];
        $loc = $refs['locations'];
        $sup = $refs['suppliers'];
        $bons = $refs['bons'];

        // [clé, catégorie, nom, modèle, emplacement, fournisseur, bon, mois depuis l'achat]
        $rows = [
            ['pc1', 'Ordinateurs portables', 'Ordinateur portable HP ProBook', 'HP ProBook 450 G9', 'siege', 0, 0, 14],
            ['pc2', 'Ordinateurs portables', 'Ordinateur portable HP ProBook', 'HP ProBook 450 G9', 'siege', 0, 0, 14],
            ['pc3', 'Ordinateurs portables', 'Ordinateur portable Dell Latitude', 'Dell Latitude 5430', 'siege', 0, 0, 11],
            ['pc4', 'Ordinateurs portables', 'Ordinateur portable Dell Latitude', 'Dell Latitude 5430', 'thies_i', 0, 0, 11],
            ['pc5', 'Ordinateurs portables', 'Ordinateur portable Lenovo ThinkPad', 'Lenovo ThinkPad E15', 'magasin', 0, 0, 6],
            ['pc6', 'Ordinateurs portables', 'Ordinateur portable Lenovo ThinkPad', 'Lenovo ThinkPad E15', 'magasin', 0, 0, 6],
            ['fix1', 'Ordinateurs de bureau', 'Unité centrale HP ProDesk', 'HP ProDesk 400 G7', 'thies_i', 0, 0, 20],
            ['fix2', 'Ordinateurs de bureau', 'Unité centrale HP ProDesk', 'HP ProDesk 400 G7', 'thies_i', 0, 0, 20],
            ['fix3', 'Ordinateurs de bureau', 'Unité centrale HP ProDesk', 'HP ProDesk 400 G7', 'thies_i', 0, 0, 20],
            ['fix4', 'Ordinateurs de bureau', 'Unité centrale HP ProDesk', 'HP ProDesk 400 G7', 'thies_i', 0, 0, 20],
            ['fix5', 'Ordinateurs de bureau', 'Unité centrale Dell OptiPlex', 'Dell OptiPlex 3080', 'kaff', 0, 0, 30],
            ['fix6', 'Ordinateurs de bureau', 'Unité centrale Dell OptiPlex', 'Dell OptiPlex 3080', 'kaff', 0, 0, 30],
            ['imp1', 'Imprimantes et copieurs', 'Imprimante laser HP LaserJet', 'HP LaserJet Pro M404dn', 'siege', 1, 1, 18],
            ['imp2', 'Imprimantes et copieurs', 'Photocopieur Canon', 'Canon iR 2630', 'siege', 1, 1, 26],
            ['imp3', 'Imprimantes et copieurs', 'Imprimante laser HP LaserJet', 'HP LaserJet Pro M404dn', 'kaff', 1, 1, 18],
            ['bur1', 'Mobilier de bureau', 'Bureau direction', 'Bureau 160 cm', 'siege', 1, 1, 24],
            ['bur2', 'Mobilier de bureau', 'Bureau direction', 'Bureau 160 cm', 'siege', 1, 1, 24],
            ['bur3', 'Mobilier de bureau', 'Fauteuil ergonomique', 'Fauteuil cuir noir', 'siege', 1, 1, 24],
            ['arm1', 'Mobilier de bureau', 'Armoire métallique', 'Armoire 2 portes', 'magasin', 1, 1, 36],
            ['arm2', 'Mobilier de bureau', 'Armoire métallique', 'Armoire 2 portes', 'magasin', 1, 1, 36],
            ['cli1', 'Climatiseurs', 'Climatiseur split 18000 BTU', 'Midea 18000 BTU', 'siege', 2, 2, 16],
            ['cli2', 'Climatiseurs', 'Climatiseur split 18000 BTU', 'Midea 18000 BTU', 'serveur', 2, 2, 16],
            ['cli3', 'Climatiseurs', 'Climatiseur split 12000 BTU', 'Midea 12000 BTU', 'thies_i', 2, 2, 16],
            ['cli4', 'Climatiseurs', 'Climatiseur split 12000 BTU', 'Midea 12000 BTU', 'kaff', 2, 2, 16],
            ['veh1', 'Véhicules', 'Pick-up Toyota Hilux', 'Toyota Hilux 2.4 D-4D', 'siege', 3, 3, 8],
            ['veh2', 'Véhicules', 'Berline de service Toyota Corolla', 'Toyota Corolla 1.8', 'siege', 3, 3, 8],
            ['at1', 'Matériel d\'atelier', 'Poste à souder inverter', 'Telwin 200 A', 'thies_a', 4, 4, 5],
            ['at2', 'Matériel d\'atelier', 'Perceuse à colonne', 'Metabo BS 20', 'thies_a', 4, 4, 5],
            ['at3', 'Matériel d\'atelier', 'Étau d\'établi', 'Étau 150 mm', 'thies_a', 4, 4, 5],
            ['at4', 'Matériel d\'atelier', 'Compresseur d\'air', 'Compresseur 50 L', 'thies_a', 4, 4, 5],
            ['res1', 'Réseau et télécoms', 'Commutateur réseau 24 ports', 'Cisco SG350-28', 'serveur', 0, 0, 22],
            ['res2', 'Réseau et télécoms', 'Routeur Wi-Fi', 'TP-Link Archer AX55', 'siege', 0, 0, 22],
            ['res3', 'Réseau et télécoms', 'Baie de brassage 12U', 'Baie murale 12U', 'serveur', 0, 0, 22],
            ['vp1', 'Vidéoprojecteurs', 'Vidéoprojecteur Epson', 'Epson EB-X49', 'thies_i', 1, 1, 12],
            ['vp2', 'Vidéoprojecteurs', 'Vidéoprojecteur Epson', 'Epson EB-X49', 'siege', 1, 1, 12],
        ];

        $available = AssetStatus::idFor(AssetStatus::AVAILABLE);
        $assets = [];

        foreach ($rows as $i => [$key, $category, $name, $model, $location, $supplier, $bon, $monthsAgo]) {
            $bought = now()->subMonths($monthsAgo)->subDays(($i * 3) % 20);

            $asset = Asset::create([
                'category_id'          => $cat[$category]->id,
                'serial_number'        => in_array($category, ['Mobilier de bureau'], true) ? null : strtoupper(substr($key, 0, 3)).'-'.(2024 + $i % 3).'-'.str_pad((string) (1000 + $i * 13), 5, '0', STR_PAD_LEFT),
                'name'                 => $name,
                'status_id'            => $available,
                'location_id'          => $loc[$location]->id,
                'type'                 => $category,
                'date_achat'           => $bought->format('Y-m-d'),
                'date_mise_en_service' => $bought->copy()->addDays(10)->format('Y-m-d'),
                'modele'               => $model,
            ]);
            $asset->fournisseurs()->sync([$sup[$supplier]->id]);
            $asset->bons()->sync([$bons[$bon]->id]);

            $assets[$key] = $asset;
        }

        return $assets;
    }

    private function assignments(array $assets, array $agents, array $services, array $locations): void
    {
        $service = app(AffectationService::class);
        $today = now();

        // Dotations durables.
        $service->assign(['agent_id' => $agents[0]->id, 'assigned_at' => $today->copy()->subMonths(9)->format('Y-m-d'), 'type' => 'dotation', 'notes' => 'Dotation poste de travail'], [$assets['pc1']->id, $assets['bur3']->id]);
        $service->assign(['agent_id' => $agents[1]->id, 'assigned_at' => $today->copy()->subMonths(8)->format('Y-m-d'), 'type' => 'dotation'], [$assets['pc2']->id]);
        $service->assign(['agent_id' => $agents[2]->id, 'assigned_at' => $today->copy()->subMonths(5)->format('Y-m-d'), 'type' => 'dotation'], [$assets['pc3']->id, $assets['imp1']->id]);
        $service->assign(['service_id' => $services['cfp_thies']->id, 'location_id' => $locations['thies_i']->id, 'assigned_at' => $today->copy()->subMonths(10)->format('Y-m-d'), 'type' => 'dotation', 'notes' => 'Équipement de la salle informatique'], [$assets['fix1']->id, $assets['fix2']->id, $assets['fix3']->id, $assets['fix4']->id, $assets['vp1']->id]);
        $service->assign(['service_id' => $services['cfp_kaff']->id, 'location_id' => $locations['kaff']->id, 'assigned_at' => $today->copy()->subMonths(7)->format('Y-m-d'), 'type' => 'dotation'], [$assets['fix5']->id, $assets['fix6']->id, $assets['imp3']->id]);

        // Mise à disposition temporaire en retard.
        $service->assign(['agent_id' => $agents[4]->id, 'assigned_at' => $today->copy()->subDays(45)->format('Y-m-d'), 'expected_return_at' => $today->copy()->subDays(10)->format('Y-m-d'), 'type' => 'temporaire', 'notes' => 'Session d\'examens'], [$assets['vp2']->id]);

        // Mise à disposition en cours, retour prévu prochainement.
        $service->assign(['agent_id' => $agents[8]->id, 'assigned_at' => $today->copy()->subDays(6)->format('Y-m-d'), 'expected_return_at' => $today->copy()->addDays(14)->format('Y-m-d'), 'type' => 'formation'], [$assets['pc4']->id]);

        // Affectation restituée.
        $returned = $service->assign(['agent_id' => $agents[6]->id, 'assigned_at' => $today->copy()->subMonths(3)->format('Y-m-d'), 'expected_return_at' => $today->copy()->subMonths(2)->format('Y-m-d'), 'type' => 'temporaire'], [$assets['pc5']->id]);
        $service->returnAssets($returned, null, 'bon', 'Rendu en bon état', $today->copy()->subMonths(2)->subDays(3)->format('Y-m-d'));

        // Pannes.
        $this->setStatus($assets['imp2'], AssetStatus::BROKEN);
        $this->setStatus($assets['cli3'], AssetStatus::BROKEN);
    }

    private function setStatus(Asset $asset, string $status): void
    {
        if ($id = AssetStatus::idFor($status)) {
            $asset->status_id = $id;
            $asset->save();
        }
    }

    private function stock(array $services, array $agents, array $suppliers, array $locations): void
    {
        $stock = app(StockService::class);

        $items = [
            ['Ramette papier A4 80 g', 'bureau', 'ramette', 40, 600 * 5, 3200, false, 120],
            ['Toner HP 85A', 'informatique', 'unité', 5, 28500, 28500, false, 12],
            ['Cartouche d\'encre noire', 'informatique', 'unité', 8, 9500, 9500, false, 20],
            ['Stylos à bille (boîte de 50)', 'bureau', 'boîte', 6, 4500, 4500, false, 15],
            ['Agrafeuse et agrafes', 'bureau', 'lot', 4, 2500, 2500, false, 10],
            ['Détergent multi-usage 5 L', 'entretien', 'unité', 6, 3800, 3800, true, 14],
            ['Électrodes de soudure (paquet)', 'pedagogique', 'paquet', 10, 6200, 6200, false, 30],
            ['Gasoil véhicules', 'carburant', 'litre', 150, 755, 755, false, 400],
            ['Câble réseau RJ45 (rouleau 100 m)', 'pieces', 'rouleau', 3, 18500, 18500, false, 8],
            ['Désinfectant surfaces', 'entretien', 'litre', 10, 2100, 2100, true, 40],
        ];

        $baseSupplier = $suppliers[1]->id;

        foreach ($items as $i => [$name, $category, $unit, $min, , $price, $perishable, $initial]) {
            $item = StockItem::create([
                'reference'    => StockItem::nextReference(),
                'name'         => $name,
                'category'     => $category,
                'unit'         => $unit,
                'quantity'     => 0,
                'min_quantity' => $min,
                'unit_price'   => $price,
                'perishable'   => $perishable,
                'location_id'  => $locations['magasin']->id,
                'supplier_id'  => $baseSupplier,
            ]);

            $stock->record($item, 'entree', $initial, [
                'moved_at'    => now()->subMonths(5)->format('Y-m-d'),
                'supplier_id' => $baseSupplier,
                'unit_price'  => $price,
                'document'    => 'BL-STOCK-INIT-'.($i + 1),
                'expires_at'  => $perishable ? now()->addMonths(10)->format('Y-m-d') : null,
                'notes'       => 'Stock initial',
            ]);

            // Sorties étalées sur 5 mois, vers différents services.
            foreach ([4, 3, 2, 1, 0] as $k => $monthsAgo) {
                $qty = max(1, (int) round($initial * (0.08 + 0.02 * (($i + $k) % 4))));
                $target = array_values($services)[($i + $k) % count($services)];
                $stock->record($item->fresh(), 'sortie', $qty, [
                    'moved_at'   => now()->subMonths($monthsAgo)->subDays(2)->format('Y-m-d'),
                    'service_id' => $target->id,
                    'agent_id'   => $agents[($i + $k) % count($agents)]->id,
                    'document'   => 'BS-'.now()->subMonths($monthsAgo)->format('Ym').'-'.($i + 1).$k,
                ]);
            }

            // Réapprovisionnement ponctuel.
            if ($i % 3 === 0) {
                $stock->record($item->fresh(), 'entree', (int) round($initial * 0.4), [
                    'moved_at'    => now()->subMonths(2)->format('Y-m-d'),
                    'supplier_id' => $baseSupplier,
                    'unit_price'  => $price,
                    'document'    => 'BL-STOCK-REAP-'.($i + 1),
                    'expires_at'  => $perishable ? now()->addMonths(8)->format('Y-m-d') : null,
                ]);
            }
        }

        // Bons d'entrée et de sortie regroupant plusieurs articles.
        $vouchers = app(StockVoucherService::class);
        $byName = fn (string $name) => StockItem::where('name', 'like', $name.'%')->firstOrFail()->id;

        $vouchers->create(
            ['type' => 'entree', 'moved_at' => now()->subDays(12)->format('Y-m-d'), 'supplier_id' => $baseSupplier, 'document' => 'BL-2026-0412'],
            [
                ['stock_item_id' => $byName('Ramette'), 'quantity' => 30, 'unit_price' => 3200],
                ['stock_item_id' => $byName('Stylos'), 'quantity' => 10, 'unit_price' => 4500],
                ['stock_item_id' => $byName('Détergent'), 'quantity' => 10, 'unit_price' => 3800, 'expires_at' => now()->addMonths(9)->format('Y-m-d')],
            ]
        );
        $vouchers->create(
            ['type' => 'sortie', 'moved_at' => now()->subDays(9)->format('Y-m-d'), 'service_id' => $services['cfp_thies']->id, 'agent_id' => $agents[8]->id, 'document' => 'Demande n° 18'],
            [
                ['stock_item_id' => $byName('Ramette'), 'quantity' => 5],
                ['stock_item_id' => $byName('Stylos'), 'quantity' => 3],
                ['stock_item_id' => $byName('Agrafeuse'), 'quantity' => 1],
            ]
        );
        $vouchers->create(
            ['type' => 'sortie', 'moved_at' => now()->subDays(4)->format('Y-m-d'), 'service_id' => $services['dsi']->id, 'document' => 'Demande n° 21'],
            [['stock_item_id' => $byName('Cartouche'), 'quantity' => 2]]
        );

        // Des articles sous le seuil d'alerte (rupture et stock bas) pour tester les alertes.
        $toner = StockItem::where('name', 'Toner HP 85A')->first();
        $stock->record($toner->fresh(), 'sortie', max(0, (float) $toner->fresh()->quantity - 3), [
            'moved_at' => now()->subDays(3)->format('Y-m-d'), 'service_id' => $services['dsi']->id, 'document' => 'BS-ALERTE-1',
        ]);
        $cable = StockItem::where('name', 'like', 'Câble réseau%')->first();
        $stock->record($cable->fresh(), 'sortie', (float) $cable->fresh()->quantity, [
            'moved_at' => now()->subDays(2)->format('Y-m-d'), 'service_id' => $services['dsi']->id, 'document' => 'BS-ALERTE-2',
        ]);
    }

    /** @return array<string, Infrastructure> */
    private function infrastructures(): array
    {
        $make = fn (array $data) => Infrastructure::create($data);

        $siege = $make([
            'name' => 'Siège du ministère', 'nature' => 'structure', 'status' => 'en_service', 'condition' => 'bon',
            'location' => 'Dakar, Plateau', 'type' => 'Administration', 'surface' => 4200,
            'last_inspection_at' => now()->subMonths(2)->format('Y-m-d'),
        ]);
        $thies = $make([
            'name' => 'Centre de formation professionnelle de Thiès', 'nature' => 'structure', 'status' => 'en_service', 'condition' => 'moyen',
            'location' => 'Thiès', 'type' => 'Formation', 'surface' => 8500,
            'last_inspection_at' => now()->subMonths(7)->format('Y-m-d'),
        ]);
        $kaff = $make([
            'name' => 'Centre de formation professionnelle de Kaffrine', 'nature' => 'structure', 'status' => 'en_construction', 'condition' => null,
            'location' => 'Kaffrine', 'type' => 'Formation', 'surface' => 6000,
        ]);

        $bat = [];
        $bat['siege_a'] = $make(['name' => 'Bâtiment A — Administration', 'nature' => 'batiment', 'parent_id' => $siege->id, 'status' => 'en_service', 'condition' => 'bon', 'type' => 'Bureaux', 'surface' => 2200, 'location' => 'Dakar, Plateau', 'construction_date' => now()->subYears(9)->format('Y-m-d'), 'acquisition_value' => 850000000, 'depreciation_years' => 40, 'depreciation_plan' => '40 ans', 'last_inspection_at' => now()->subMonths(3)->format('Y-m-d')]);
        $bat['siege_b'] = $make(['name' => 'Bâtiment B — Annexe', 'nature' => 'batiment', 'parent_id' => $siege->id, 'status' => 'en_rehabilitation', 'condition' => 'degrade', 'type' => 'Bureaux', 'surface' => 1100, 'location' => 'Dakar, Plateau', 'construction_date' => now()->subYears(22)->format('Y-m-d'), 'acquisition_value' => 320000000, 'depreciation_years' => 30, 'depreciation_plan' => '30 ans', 'last_inspection_at' => now()->subMonths(5)->format('Y-m-d')]);
        $bat['thies_ateliers'] = $make(['name' => 'Bloc des ateliers', 'nature' => 'batiment', 'parent_id' => $thies->id, 'status' => 'en_service', 'condition' => 'moyen', 'type' => 'Ateliers', 'surface' => 2600, 'location' => 'Thiès', 'construction_date' => now()->subYears(14)->format('Y-m-d'), 'acquisition_value' => 410000000, 'depreciation_years' => 30, 'depreciation_plan' => '30 ans', 'last_inspection_at' => now()->subMonths(8)->format('Y-m-d')]);
        $bat['thies_admin'] = $make(['name' => 'Bloc administratif', 'nature' => 'batiment', 'parent_id' => $thies->id, 'status' => 'en_maintenance', 'condition' => 'degrade', 'type' => 'Bureaux', 'surface' => 900, 'location' => 'Thiès', 'construction_date' => now()->subYears(14)->format('Y-m-d'), 'acquisition_value' => 150000000, 'depreciation_years' => 25, 'depreciation_plan' => '25 ans', 'last_inspection_at' => now()->subMonths(10)->format('Y-m-d')]);

        $make(['name' => 'Salle informatique', 'nature' => 'bloc', 'parent_id' => $bat['thies_admin']->id, 'status' => 'en_service', 'condition' => 'bon', 'type' => 'Salle de formation', 'surface' => 85, 'location' => 'Thiès']);
        $make(['name' => 'Atelier soudure', 'nature' => 'bloc', 'parent_id' => $bat['thies_ateliers']->id, 'status' => 'en_service', 'condition' => 'moyen', 'type' => 'Atelier', 'surface' => 140, 'location' => 'Thiès']);
        $make(['name' => 'Atelier électricité', 'nature' => 'bloc', 'parent_id' => $bat['thies_ateliers']->id, 'status' => 'hors_service', 'condition' => 'critique', 'type' => 'Atelier', 'surface' => 120, 'location' => 'Thiès']);
        $make(['name' => 'Salle de réunion', 'nature' => 'bloc', 'parent_id' => $bat['siege_a']->id, 'status' => 'en_service', 'condition' => 'bon', 'type' => 'Réunion', 'surface' => 60, 'location' => 'Dakar, Plateau']);

        return ['siege' => $siege, 'thies' => $thies, 'kaffrine' => $kaff] + $bat;
    }

    private function projects(array $infra, User $admin): void
    {
        $chefs = [
            ChefProjet::create(['nom' => 'Diouf', 'prenom' => 'Babacar', 'adresse' => 'Dakar', 'e_mail' => 'babacar.diouf@mefpt.test', 'telephone' => '77 421 10 10']),
            ChefProjet::create(['nom' => 'Sall', 'prenom' => 'Aïssatou', 'adresse' => 'Dakar', 'e_mail' => 'aissatou.sall@mefpt.test', 'telephone' => '77 421 20 20']),
            ChefProjet::create(['nom' => 'Cissé', 'prenom' => 'Modou', 'adresse' => 'Thiès', 'e_mail' => 'modou.cisse@mefpt.test', 'telephone' => '77 421 30 30']),
        ];

        $intervenants = [
            Intervenant::create(['nom' => 'Thiaw', 'prenom' => 'Lamine', 'organisation' => 'CSE Construction', 'role' => 'entreprise', 'telephone' => '33 821 40 40', 'email' => 'contact@cse.test']),
            Intervenant::create(['nom' => 'Barry', 'prenom' => 'Ismaïla', 'organisation' => 'Cabinet Archi-Sahel', 'role' => 'bureau_etudes', 'telephone' => '33 821 50 50', 'email' => 'info@archisahel.test']),
            Intervenant::create(['nom' => 'Lô', 'prenom' => 'Ousmane', 'organisation' => 'Bureau Veritas Sénégal', 'role' => 'controle', 'telephone' => '33 821 60 60', 'email' => 'senegal@bv.test']),
            Intervenant::create(['nom' => 'Dème', 'prenom' => 'Pape', 'organisation' => null, 'role' => 'technicien', 'telephone' => '77 555 66 77', 'email' => null]),
        ];

        $projects = [
            ['Construction du CFP de Kaffrine', 'construction', 'en_cours', now()->subMonths(10), now()->addMonths(8), 2400000000, 1350000000, [$infra['kaffrine']->id], [$chefs[0]->id], [
                ['Études et plans', 'done', 1, 1], ['Terrassement et fondations', 'done', 3, 1], ['Gros œuvre', 'late', 5, 0], ['Second œuvre', 'todo', 4, 0], ['Équipements et mise en service', 'todo', 3, 0],
            ]],
            ['Réhabilitation de l\'annexe du siège', 'rehabilitation', 'en_cours', now()->subMonths(3), now()->addMonths(5), 180000000, 60000000, [$infra['siege_b']->id], [$chefs[1]->id], [
                ['Diagnostic technique', 'done', 1, 1], ['Appel d\'offres', 'done', 2, 1], ['Travaux de toiture', 'todo', 4, 0], ['Peinture et finitions', 'todo', 2, 0],
            ]],
            ['Modernisation de la salle informatique de Thiès', 'equipement', 'termine', now()->subMonths(8), now()->subMonths(2), 45000000, 43800000, [$infra['thies']->id], [$chefs[2]->id], [
                ['Commande du matériel', 'done', 1, 1], ['Installation', 'done', 2, 1], ['Réception', 'done', 1, 1],
            ]],
            ['Extension de l\'atelier de soudure', 'extension', 'planifie', now()->addMonths(1), now()->addMonths(9), 95000000, 0, [$infra['thies_ateliers']->id], [$chefs[2]->id, $chefs[0]->id], []],
        ];

        foreach ($projects as [$name, $type, $status, $start, $end, $budget, $spent, $infraIds, $chefIds, $milestones]) {
            $project = Project::create([
                'reference'     => Project::nextReference(),
                'name'          => $name,
                'type'          => $type,
                'description'   => 'Projet de démonstration : '.$name.'.',
                'status'        => $status,
                'start_date'    => $start->format('Y-m-d'),
                'end_date'      => $end->format('Y-m-d'),
                'budget'        => $budget,
                'spent'         => $spent,
                'progress'      => $status === 'termine' ? 100 : 0,
                'created_by_id' => $admin->id,
            ]);
            $project->infrastructures()->sync($infraIds);
            $project->chef_projets()->sync($chefIds);

            foreach ($milestones as $p => [$title, $state, $weight, $done]) {
                ProjectMilestone::create([
                    'project_id'     => $project->id,
                    'title'          => $title,
                    'due_date'       => $state === 'late' ? now()->subDays(20)->format('Y-m-d') : $start->copy()->addMonths($p + 1)->format('Y-m-d'),
                    'weight'         => $weight,
                    'done_at'        => $done ? $start->copy()->addMonths($p)->addDays(15) : null,
                    'intervenant_id' => $intervenants[$p % 3]->id,
                    'position'       => $p + 1,
                ]);
            }

            if ($milestones) {
                $project->refresh()->refreshProgress();
            }
        }

        $this->projectReports();
    }

    private function projectReports(): void
    {
        $projects = Project::orderBy('id')->get();

        foreach ([
            ['Rapport d\'avancement — mois précédent', 25, [0]],
            ['Compte rendu de visite de chantier', 12, [0, 1]],
            ['Rapport de réception — salle informatique', 60, [2]],
        ] as [$title, $daysAgo, $idx]) {
            $report = Report::create(['title' => $title, 'report_date' => now()->subDays($daysAgo)->format('Y-m-d')]);
            $report->projects()->sync(collect($idx)->map(fn ($i) => $projects[$i]->id ?? null)->filter()->all());
        }
    }

    private function maintenance(array $infra, array $assets, array $users): void
    {
        $workflow = app(MaintenanceWorkflow::class);

        $maintenance = $users['maintenance'];
        $director = $users['directeur'];

        // Demande soumise (en attente d'avis).
        Auth::login($users['agent']);
        $workflow->submit(['title' => 'Imprimante en panne au secrétariat', 'kind' => 'corrective', 'priority' => 'normale', 'target_type' => 'asset', 'asset_id' => $assets['imp2']->id, 'establishment' => 'Siège', 'description' => 'Bourrages papier répétés, bruit anormal depuis lundi.']);

        // Demande en attente du Directeur.
        Auth::login($users['comptable']);
        $waiting = $workflow->submit(['title' => 'Fuite d\'eau dans le bloc administratif', 'kind' => 'corrective', 'priority' => 'haute', 'target_type' => 'infrastructure', 'infrastructure_id' => $infra['thies_admin']->id, 'establishment' => 'CFP de Thiès', 'description' => 'Infiltration au plafond du couloir, depuis les dernières pluies.']);
        Auth::login($maintenance);
        $workflow->validate($waiting, 'Fuite confirmée sur place. Intervention d\'un plombier nécessaire.');

        // Demande approuvée puis planifiée.
        Auth::login($users['secondaire']);
        $planned = $workflow->submit(['title' => 'Climatiseur de la salle informatique en panne', 'kind' => 'corrective', 'priority' => 'urgente', 'target_type' => 'asset', 'asset_id' => $assets['cli3']->id, 'establishment' => 'CFP de Thiès', 'description' => 'Ne refroidit plus : les postes surchauffent.']);
        Auth::login($maintenance);
        $workflow->validate($planned, 'Recharge en gaz probable.');
        Auth::login($director);
        $workflow->approve($planned, 'Approuvé, intervention prioritaire.');
        Auth::login($maintenance);
        $workflow->plan($planned->fresh(), ['planned_for' => now()->addDays(3)->format('Y-m-d'), 'assigned_to_id' => $maintenance->id, 'instructions' => 'Prévoir bouteille de gaz R410A.']);

        // Demande en cours.
        Auth::login($users['agent']);
        $running = $workflow->submit(['title' => 'Remplacement de la serrure du magasin', 'kind' => 'corrective', 'priority' => 'normale', 'target_type' => 'infrastructure', 'infrastructure_id' => $infra['siege_a']->id, 'description' => 'Serrure bloquée, accès difficile.']);
        Auth::login($maintenance);
        $workflow->validate($running);
        Auth::login($director);
        $workflow->approve($running);
        Auth::login($maintenance);
        $workflow->plan($running->fresh(), ['planned_for' => now()->subDays(2)->format('Y-m-d'), 'assigned_to_id' => $maintenance->id]);
        $workflow->start($running->fresh());

        // Demande terminée.
        Auth::login($users['comptable']);
        $done = $workflow->submit(['title' => 'Révision du climatiseur de la salle serveur', 'kind' => 'preventive', 'priority' => 'normale', 'target_type' => 'asset', 'asset_id' => $assets['cli2']->id, 'description' => 'Entretien semestriel.']);
        Auth::login($maintenance);
        $workflow->validate($done);
        Auth::login($director);
        $workflow->approve($done);
        Auth::login($maintenance);
        $workflow->plan($done->fresh(), ['planned_for' => now()->subDays(20)->format('Y-m-d'), 'assigned_to_id' => $maintenance->id]);
        $workflow->start($done->fresh());
        $workflow->complete($done->fresh(), ['resolution' => 'Nettoyage des filtres, contrôle du gaz et du compresseur. RAS.', 'cost' => 35000, 'back_in_service' => true]);

        // Demande rejetée.
        Auth::login($users['agent']);
        $rejected = $workflow->submit(['title' => 'Peinture du bureau', 'kind' => 'corrective', 'priority' => 'basse', 'target_type' => 'infrastructure', 'infrastructure_id' => $infra['siege_a']->id, 'description' => 'Souhait de repeindre le bureau en bleu.']);
        Auth::login($maintenance);
        $workflow->reject($rejected->fresh(), 'Hors programme de maintenance : à inscrire au plan de réhabilitation.');

        // Plans préventifs.
        MaintenancePlan::create(['title' => 'Révision des climatiseurs', 'description' => "Nettoyage des filtres\nContrôle du niveau de gaz\nVérification du condensat", 'target_type' => 'asset', 'asset_id' => $assets['cli1']->id, 'frequency_months' => 6, 'next_due_at' => now()->addDays(5)->format('Y-m-d'), 'lead_days' => 7, 'responsible_id' => $maintenance->id, 'active' => true]);
        MaintenancePlan::create(['title' => 'Contrôle des extincteurs', 'description' => "Contrôle de la pression\nDate de péremption\nAccessibilité", 'target_type' => 'infrastructure', 'infrastructure_id' => $infra['siege_a']->id, 'frequency_months' => 12, 'next_due_at' => now()->addMonths(2)->format('Y-m-d'), 'lead_days' => 15, 'responsible_id' => $maintenance->id, 'active' => true]);
        MaintenancePlan::create(['title' => 'Vérification de l\'installation électrique', 'description' => "Tableau général\nDisjoncteurs\nMise à la terre", 'target_type' => 'infrastructure', 'infrastructure_id' => $infra['thies_ateliers']->id, 'frequency_months' => 12, 'next_due_at' => now()->addDays(4)->format('Y-m-d'), 'lead_days' => 7, 'responsible_id' => $maintenance->id, 'active' => true]);

        $workflow->generatePreventive();

        Auth::login(User::where('email', 'admin@admin.com')->first() ?? User::first());
    }

    private function inventaire(array $refs, array $services): void
    {
        $service = app(InventaireService::class);

        // Campagne clôturée.
        $closed = Inventaire::create([
            'nom' => 'Inventaire annuel 2025 — Siège', 'reference' => 'INV-'.now()->format('Y').'-'.str_pad((string) (Inventaire::withoutGlobalScopes()->count() + 1), 2, '0', STR_PAD_LEFT),
            'status' => Inventaire::DRAFT, 'location_id' => $refs['locations']['siege']->id, 'created_by_id' => Auth::id(),
            'starts_at' => now()->subMonths(4)->format('Y-m-d'), 'ends_at' => now()->subMonths(3)->format('Y-m-d'),
            'notes' => 'Campagne annuelle du siège.',
        ]);
        $service->start($closed);
        foreach (Asset::where('location_id', $refs['locations']['siege']->id)->limit(5)->get() as $asset) {
            $service->record($closed, $asset->qr_code, 'bon');
        }
        $service->close($closed->fresh(), true);

        // Campagne en cours.
        $running = Inventaire::create([
            'nom' => 'Inventaire du CFP de Thiès', 'reference' => 'INV-'.now()->format('Y').'-'.str_pad((string) (Inventaire::withoutGlobalScopes()->count() + 1), 2, '0', STR_PAD_LEFT),
            'status' => Inventaire::DRAFT, 'service_id' => $services['cfp_thies']->id, 'created_by_id' => Auth::id(),
            'starts_at' => now()->subDays(5)->format('Y-m-d'), 'ends_at' => now()->addDays(10)->format('Y-m-d'),
            'notes' => 'Pointage en cours par l\'équipe du CFP.',
        ]);
        $service->start($running);
        foreach (Asset::where('service_id', $services['cfp_thies']->id)->limit(3)->get() as $asset) {
            $service->record($running, $asset->qr_code, 'bon');
        }
    }

    private function tasksAndReports(array $users, array $assets): void
    {
        $tags = collect(['Urgent', 'Électricité', 'Plomberie', 'Informatique', 'Entretien'])->map(fn ($n) => TaskTag::create(['name' => $n]))->all();

        $open = TaskStatus::where('name', 'like', '%uvert%')->orWhere('name', 'Open')->value('id') ?? TaskStatus::orderBy('id')->value('id');
        $progress = TaskStatus::where('name', 'like', '%cours%')->orWhere('name', 'In progress')->value('id') ?? $open;
        $closed = TaskStatus::where('name', 'like', '%termin%')->orWhere('name', 'Closed')->value('id') ?? $open;

        foreach ([
            ['Inventorier la salle serveur', 'Compter et étiqueter tout le matériel.', $open, 6, 9, 'maintenance', [3], ['srv' => 'cli2']],
            ['Remplacer les néons du bureau 12', 'Prévoir 6 tubes LED.', $progress, 1, 4, 'maintenance', [1, 4], []],
            ['Configurer les postes de la salle informatique', 'Installer le système et les logiciels de formation.', $closed, -12, -5, 'secondaire', [3], []],
            ['Contrôler les extincteurs du siège', 'Vérifier la date de péremption.', $open, 10, 14, 'maintenance', [4], []],
        ] as [$name, $description, $status, $startIn, $dueIn, $who, $tagIdx, $extra]) {
            $task = Task::create([
                'name' => $name, 'description' => $description, 'status_id' => $status,
                'scheduled_date' => now()->addDays($startIn)->format('Y-m-d'),
                'due_date' => now()->addDays($dueIn)->format('Y-m-d'),
                'assigned_to_id' => $users[$who]->id,
            ]);
            $task->tags()->sync(collect($tagIdx)->map(fn ($i) => $tags[$i]->id)->all());
            if (! empty($extra['srv'])) {
                $task->equipements()->sync([$assets[$extra['srv']]->id]);
            }
        }
    }

    /** Étale l'historique dans le temps pour que les graphiques mensuels soient lisibles. */
    private function history(array $assets): void
    {
        foreach ($assets as $asset) {
            $created = $asset->date_achat ? \Carbon\Carbon::parse($asset->date_achat)->addDays(2) : now();
            DB::table('assets')->where('id', $asset->id)->update(['created_at' => $created, 'updated_at' => $created]);
            DB::table('assets_histories')->where('asset_id', $asset->id)->where('action', 'creation')->update(['created_at' => $created, 'updated_at' => $created]);
        }
    }
}
