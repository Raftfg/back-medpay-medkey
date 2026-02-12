<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Product;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $user = \Modules\Acl\Entities\User::first();
        
        // Récupérer les catégories, units, etc. (sans hospital_id car on est déjà dans la base tenant)
        $category = \Modules\Stock\Entities\Category::where('name', 'COMPRIME')->first();
        $conditioningUnit = \Modules\Stock\Entities\ConditioningUnit::first();
        $saleUnit = \Modules\Stock\Entities\SaleUnit::first();
        $administrationRoute = \Modules\Stock\Entities\AdministrationRoute::first();
        $typeProduct = \Modules\Stock\Entities\TypeProduct::first();

        if (!$category || !$conditioningUnit || !$saleUnit || !$administrationRoute || !$typeProduct) {
            $this->command->warn("⚠️  Données de référence manquantes. Veuillez d'abord exécuter les seeders de référence.");
            return;
        }

            $products = [
                [
                    'code' => 'DRU-COM-15896',
                    'name' => 'Paracetamol',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-16896',
                    'name' => 'Omeprazole',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Omep',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-17896',
                    'name' => 'Amoxicillin',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-15256',
                    'name' => 'CIPROFLOXACINE',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Blister',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-18896',
                    'name' => 'Atorvastatin',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-19896',
                    'name' => 'Aspirin',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-20896',
                    'name' => 'Loratadine Syrup',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-21896',
                    'name' => 'Lingettes désinfectantes',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-22896',
                    'name' => 'Masques chirurgicaux',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
                [
                    'code' => 'DRU-COM-23896',
                    'name' => 'Bandages',
                    'dosage' => 'Pour les adultes est généralement de 500 milligrammes (mg) à 1000 mg toutes les 4 à 6 heures au besoin.',
                    'brand' => 'Febridol',
                    'category_id' => $category->id,
                ],
            ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                [
                    'code' => $productData['code'],
                ],
                array_merge($productData, [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'conditioning_unit_id' => $conditioningUnit->id,
                    'administration_route_id' => $administrationRoute->id,
                    'sale_unit_id' => $saleUnit->id,
                    'type_id' => $typeProduct->id,
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Produits créés.');
    }
}
