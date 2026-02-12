<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Acl\Entities\Permission;
use Illuminate\Support\Facades\Hash;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            'Patient' => [
                'Créer un patient',
                'Voir détail patient',
                'Modifier patient',
                'Voir facture et paiement',
                'Voir dossier médical',
                'Créer une venue',
            ],
            'Mouvement' => [
                'Ajouter mouvement',
                'Voir historique mouvement',
                'Modifier mouvement',
                'Supprimer mouvement',
            ],
            'Service' => [
                'Ajouter service',
                'Voir détails service',
                'Modifier service',
                'Supprimer service',
            ],
            'Pharmacie' => [
                'Ajouter médicament',
                'Voir stock médicament',
                'Modifier médicament',
                'Supprimer médicament',
            ],
            'Caisse' => [
                'Ajouter transaction',
                'Voir historique transactions',
                'Modifier transaction',
                'Supprimer transaction',
            ],
            'Facturation' => [
                'Créer facture',
                'Voir facture détaillée',
                'Modifier facture',
                'Supprimer facture',
            ],
            'Hospitalisation' => [
                'Admettre patient',
                'Voir historique hospitalisation',
                'Modifier statut patient',
                'Décharger patient',
            ],
            'Recouvrement' => [
                'Enregistrer paiement',
                'Voir historique paiements',
                'Modifier paiement',
                'Supprimer paiement',
            ],
            'Stock' => [
                'Ajouter produit au stock',
                'Voir stock détaillé',
                'Modifier produit du stock',
                'Supprimer produit du stock',
            ],
            'Ressources' => [
                'Gérer utilisateurs',
                'Gérer rôles',
                'Voir activité système',
            ],
            'Assurance' => [
                'Ajouter assurance',
                'Voir détails assurance',
                'Modifier assurance',
                'Supprimer assurance',
            ],
            'Rendezvous' => [
                'Planifier rendez-vous',
                'Voir calendrier rendez-vous',
                'Modifier rendez-vous',
                'Annuler rendez-vous',
            ],
            'Profil' => [
                'Voir profil utilisateur',
                'Modifier profil utilisateur',
                'Changer mot de passe',
            ],
            'Petite' => [
                'Ajouter petite action',
                'Voir détails petite action',
                'Modifier petite action',
                'Supprimer petite action',
            ],
            'Rapport' => [
                'Générer rapport',
                'Voir historique rapports',
                'Modifier rapport',
                'Supprimer rapport',
            ],
            'Configuration' => [
                'Configurer système',
                'Modifier paramètres système',
                'Gérer modules',
                'Gérer permissions',
            ],
            'Utilisateur' => [
                'créer un utilisateur',
                'Modifier un utilisateur',
                'Supprimer un utilisateur',
            ],
        ];

        foreach ($modules as $module => $permissions) {
            // Création d'une permission pour voir le module
            Permission::firstOrCreate(
                [
                    'name' => "voir_module_" . strtolower($module),
                    'guard_name' => 'api',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'display_name' => "Voir $module",
                    'groupe' => $module,
                ]
            );

            // Création des sous-permissions pour chaque module
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(
                    [
                        'name' =>  Str::slug("$permissionName"),
                        'guard_name' => 'api',
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'display_name' => $permissionName,
                        'groupe' => $module,
                    ]
                );
            }
        }
    }
}
