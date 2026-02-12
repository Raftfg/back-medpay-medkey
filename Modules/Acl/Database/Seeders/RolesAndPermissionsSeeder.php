<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Entities\Role;
use Modules\Acl\Entities\Permission;

class RolesAndPermissionsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //'ACL' => ['display' => 'Droit d\'accès', 'action' => 'crud', 'attribuables' => 'super,admin,responsable,agent,proprietaire,locataire'],
        //La clé display permet de fournir le nom de la permission à afficher
        //La clé action permet de déterminer si on applique toutes les actions du CRUD sur cet objet ou pas
        //La clé attribuables permet de déterminer les rôles qui peuvent avoir par défaut les permissions de cet objet
        //

        $objetsPermission = [
            //Modules ACL
            'Permission' => [
                'display' => "Privilège",
                'groupe' => 'Acl',
                'action' => 'r',
                'attribuables' => [
                    'super' => 'r',
                    'admin' => 'r',
                    'agent' => '',
                ]
            ],
            'Role' => [
                'display' => "Rôle",
                'groupe' => 'Acl',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => '',
                ]
            ],
            'User' => [
                'display' => "Utilisateur",
                'groupe' => 'Acl',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'r',
                ]
            ],
            
            //Modules Media
            'Media' => [
                'display' => "Médias",
                'groupe' => 'Media',
                'action' => 'c,r,d',
                'attribuables' => [
                    'super' => 'c,r,d',
                    'admin' => 'c,r,d',
                    'agent' => 'c,r,d',
                ]
            ],
            
            //Modules Notifier
            'NotifierTracking' => [
                'display' => "Notifications",
                'groupe' => 'Tracking',
                'action' => 'r',
                'attribuables' => [
                    'super' => 'r',
                    'admin' => 'r',
                    'agent' => '',
                ]
            ],
            
            //Modules Parametrage
            'Parametre' => [
                'display' => "Paramètres",
                'groupe' => 'Parametrage',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => '',
                ]
            ],
            
            'Etat' => [
                'display' => "États",
                'groupe' => 'Parametrage',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'r',
                ]
            ],
            'Pay' => [
                'display' => "Pays",
                'groupe' => 'Parametrage',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'r',
                ]
            ],
            'Ville' => [
                'display' => "Villes",
                'groupe' => 'Parametrage',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'r',
                ]
            ],
                        
            //Modules Tracking
            'ActivityLog' => [
                'display' => "Log des activités",
                'groupe' => 'Tracking',
                'action' => 'r',
                'attribuables' => [
                    'super' => 'r',
                    'admin' => 'r',
                    'agent' => '',
                ]
            ],

            //Module Absence             
            'Absent' => [
                'display' => "Absences",
                'groupe' => 'Absence',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            'Mission' => [
                'display' => "Missions",
                'groupe' => 'Absence',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            'MissionParticipant' => [
                'display' => "Participants de Mission",
                'groupe' => 'Absence',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            'TypeVacation' => [
                'display' => "Types de congés",
                'groupe' => 'Absence',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            'Vacation' => [
                'display' => "Congés",
                'groupe' => 'Absence',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],

            //Module Separation
            'Piece' => [
                'display' => "Pièces à fournir",
                'groupe' => 'Separation',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            'Request' => [
                'display' => "Demandes",
                'groupe' => 'Separation',
                'action' => 'c,r,u,d',
                'attribuables' => [
                    'super' => 'c,r,u,d',
                    'admin' => 'c,r,u,d',
                    'agent' => 'c,r,u,d',
                ]
            ],
            
            //Autres
            
        ];
        

        $cruds = [
            'c' => ['create' => 'Ajouter'],
            'r' => ['read' => 'Lire'],
            'u' => ['update' => 'Modifier'],
            'd' => ['delete' => 'Supprimer'],
        ];
        /*
          |--------------------------------------------------------------------------
          | Create permissions : CRUD
          |--------------------------------------------------------------------------
         */
        $permissionAgentIds = $permissionAdminIds = [];
        foreach ($objetsPermission as $objet => $objetValue) {
            //Récupérer les actions CRUD possibles sur cet objet
            $objetAction = $objetValue['action'];       //Exemple : 'crud'
            $objetActions = explode(",", $objetAction); //Exemple : ['c','r','u','d']
            //Fabriquer les privilèges
            foreach ($objetActions as $action) {        //Exemple : $action = c
                $crud = $cruds[$action];                //Exemple : ['create' => 'Ajouter'],
                $crudKey = array_keys($crud)[0];       //Exemple : create
                $crudValue = array_values($crud)[0];   //Exemple : Ajouter
                $nomPrivilege = "$crudKey $objet";      //Exemple : create User
                $displayName = $objetValue['display'];  //Exemple : Utilisateur
                $groupe = $objetValue['groupe'];        //Exemple : Acl
                //Créer ou récupérer la permission de manière idempotente
                $permission = Permission::firstOrCreate(
                    [
                        'name' => $nomPrivilege,
                        'guard_name' => guard_web(),
                    ],
                    [
                        'display_name' => $crudValue . ' ' . $displayName,
                        'groupe' => $groupe,
                    ]
                );
                $permission->setTranslation('display_name', 'en', $nomPrivilege);
                $permission->groupe = $groupe;
                $permission->save();

                //Traitons les permissions par défaut pour chaque rôle du tableau
                $permissionAdminIds[] = $permission->id;
                
                $permissionAgentIds[] = $this->getPermissionIdParDefaut($permission, $objetValue, $action, 'agent');
            }
        }
        
        /*
          |--------------------------------------------------------------------------
          | Create roles and assign created permissions. This can be done as separate statements
          |--------------------------------------------------------------------------
         */

        $permissionAdminIds = array_values(array_filter($permissionAdminIds));
        $permissions = Permission::whereIn('id', $permissionAdminIds)->where(['guard_name' => guard_web()])->get();
        $superR = Role::firstOrCreate(['name' => 'Super', 'guard_name' => guard_web()]);
        $translations = ['en' => 'Super admin', 'fr' => 'Super admin'];
        $superR->display_name = $translations;
        $superR->save();
        $superR->syncPermissions($permissions);
        
        $adminR = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => guard_web()]);
        $translations = ['en' => 'Admin', 'fr' => 'Admin'];
        $adminR->display_name = $translations;
        $adminR->save();
        $adminR->syncPermissions($permissions);
        
        // S'assurer que Super et Admin ont toutes les permissions existantes
        $allPermissions = Permission::where(['guard_name' => guard_web()])->get();
        $superR->syncPermissions($allPermissions);
        $adminR->syncPermissions($allPermissions);

        $permissionAgentIds = array_values(array_filter($permissionAgentIds));
        $permissions = Permission::whereIn('id', $permissionAgentIds)->where(['guard_name' => guard_web()])->get();
        $AgentR = Role::firstOrCreate(['name' => 'Agent', 'guard_name' => guard_web()]);
        $translations = ['en' => 'Agent', 'fr' => 'Agent'];
        $AgentR->display_name = $translations;
        $AgentR->save();
        $AgentR->syncPermissions($permissions);
        
        $agentR = Role::firstOrCreate(['name' => 'Client', 'guard_name' => guard_web()]);
        $translations = ['en' => 'Client', 'fr' => 'Client'];
        $agentR->display_name = $translations;
        $agentR->save();

    }

    private function getPermissionIdParDefaut($permission, $objetValue, $action, $role) {
        $attribuables = $objetValue['attribuables'];
        //Si la clé du rôle est définie dans la liste. Exemple :
        //'attribuables' => ['super' => 'r', 'admin' => 'r', 'agent' => 'r',] 
        //Si agent est dans attribuables
        if (isset($attribuables[$role])) {
            $roleAction = $attribuables[$role];             //Exemple : 'crud'
            $roleActions = explode(",", $roleAction);       //Exemple : ['c','r','u','d']
            if (in_array($action, $roleActions)) {            //On peut lui attribuer les permissions par défaut
                return $permission->id;
            }
        }
        return null;
    }
    
}
