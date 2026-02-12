# Plan d'Implémentation : Multi-Tenancy Database-Per-Tenant

## 📋 Vue d'ensemble

Ce document présente le plan d'implémentation pour faire évoluer la plateforme Medkey d'une architecture **Shared Database** (une base avec `hospital_id`) vers une architecture **Database-Per-Tenant** (une base MySQL par hôpital).

---

## 🎯 Objectifs

1. **Isolation complète des données** : Chaque hôpital dispose de sa propre base MySQL
2. **Code unique** : Une seule codebase Laravel pour tous les hôpitaux
3. **Scalabilité** : Faciliter l'ajout de nouveaux hôpitaux sans impact
4. **Sécurité renforcée** : Isolation physique des données médicales
5. **Migration progressive** : Ne pas casser l'existant

---

## 📊 Architecture Cible

### Schéma des Bases de Données

```
┌─────────────────────────────────────────────────────────────┐
│                    BASE CORE (medkey_core)                    │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Table: hospitals                                       │   │
│  │ - id, name, domain, slug, status                      │   │
│  │ - database_name, database_host, database_port         │   │
│  │ - created_at, updated_at                             │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Table: hospital_modules                               │   │
│  │ - hospital_id, module_name, is_enabled               │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Table: system_admins                                  │   │
│  │ - id, email, name, permissions                       │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              BASE TENANT (medkey_hospital_1)                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Toutes les tables des modules :                       │   │
│  │ - users, patients, hospitalizations, payments, etc.  │   │
│  │ ⚠️ SANS colonne hospital_id (plus nécessaire)         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              BASE TENANT (medkey_hospital_2)                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Même structure que hospital_1                         │   │
│  │ Données complètement isolées                          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🏗️ Structure des Dossiers Laravel

```
back-medpay/
├── app/
│   ├── Core/                          # NOUVEAU : Logique CORE
│   │   ├── Models/
│   │   │   └── Hospital.php           # Modèle Hospital (CORE)
│   │   ├── Services/
│   │   │   ├── TenantConnectionService.php
│   │   │   └── TenantProvisioningService.php
│   │   └── Commands/
│   │       ├── CreateTenantCommand.php
│   │       └── MigrateTenantCommand.php
│   ├── Http/
│   │   └── Middleware/
│   │       ├── TenantMiddleware.php    # MODIFIÉ : Bascule DB
│   │       └── EnsureTenantConnection.php
│   ├── Services/
│   │   └── TenantService.php          # MODIFIÉ : Gestion connexions
│   └── Traits/
│       └── BelongsToHospital.php     # SUPPRIMÉ : Plus nécessaire
│
├── database/
│   ├── core/                          # NOUVEAU : Migrations CORE
│   │   └── migrations/
│   │       ├── create_hospitals_table.php
│   │       ├── create_hospital_modules_table.php
│   │       └── create_system_admins_table.php
│   ├── tenant/                        # NOUVEAU : Migrations TENANT
│   │   └── migrations/
│   │       └── (toutes les migrations existantes)
│   └── migrations/                    # CONSERVÉ : Pour compatibilité
│
├── config/
│   ├── database.php                   # MODIFIÉ : Connexions dynamiques
│   └── tenant.php                     # NOUVEAU : Config multi-tenant
│
└── Modules/                            # CONSERVÉ : Structure existante
    ├── Acl/
    ├── Patient/
    ├── Payment/
    └── ...
```

---

## 🔄 Phases d'Implémentation

### **PHASE 1 : Préparation de l'Infrastructure CORE** ⏱️ 2-3 jours

#### 1.1 Création de la Base CORE
- [ ] Créer la base de données `medkey_core`
- [ ] Créer les migrations CORE :
  - `hospitals` (avec `database_name`, `database_host`, `database_port`)
  - `hospital_modules` (modules activés par hôpital)
  - `system_admins` (administrateurs système)
- [ ] Créer les modèles CORE dans `app/Core/Models/`

#### 1.2 Configuration des Connexions
- [ ] Modifier `config/database.php` pour ajouter :
  - Connexion `core` (base CORE)
  - Connexion `tenant` (dynamique, sera configurée par middleware)
- [ ] Créer `config/tenant.php` pour la configuration multi-tenant

#### 1.3 Service de Gestion des Connexions
- [ ] Créer `TenantConnectionService` :
  - Méthode `connect(Hospital $hospital)` : Configure la connexion tenant
  - Méthode `disconnect()` : Réinitialise la connexion
  - Méthode `getCurrentConnection()` : Récupère la connexion active

---

### **PHASE 2 : Adaptation du Middleware** ⏱️ 1-2 jours

#### 2.1 Modification du TenantMiddleware
- [ ] Adapter `TenantMiddleware` pour :
  1. Identifier l'hôpital (déjà fait)
  2. **NOUVEAU** : Basculer la connexion DB vers la base du tenant
  3. Vérifier que la base existe et est accessible
  4. Gérer les erreurs de connexion

#### 2.2 Middleware de Vérification
- [ ] Créer `EnsureTenantConnection` :
  - Vérifie que la connexion tenant est active
  - Bloque l'accès si la connexion échoue

---

### **PHASE 3 : Migration des Données Existantes** ⏱️ 3-5 jours

#### 3.1 Script de Migration
- [ ] Créer une commande Artisan `tenant:migrate-existing`
- [ ] Pour chaque hôpital existant :
  1. Créer une nouvelle base `medkey_hospital_{id}`
  2. Copier la structure (sans `hospital_id`)
  3. Migrer les données filtrées par `hospital_id`
  4. Mettre à jour la table `hospitals` dans CORE

#### 3.2 Migration Progressive
- [ ] Option 1 : Migration complète en une fois (recommandé pour < 5 hôpitaux)
- [ ] Option 2 : Migration progressive hôpital par hôpital
- [ ] Créer un rollback possible

---

### **PHASE 4 : Adaptation des Modèles** ⏱️ 2-3 jours

#### 4.1 Suppression du Trait BelongsToHospital
- [ ] Retirer `BelongsToHospital` de tous les modèles tenant
- [ ] Supprimer `HospitalScope` (plus nécessaire)
- [ ] Supprimer les colonnes `hospital_id` des migrations tenant

#### 4.2 Adaptation du Modèle Hospital
- [ ] Déplacer `Hospital` vers `app/Core/Models/Hospital`
- [ ] Utiliser la connexion `core`
- [ ] Ajouter les champs : `database_name`, `database_host`, `database_port`

#### 4.3 Mise à Jour des Relations
- [ ] Supprimer les relations `hospital_id` dans les modèles tenant
- [ ] Adapter les policies (plus besoin de vérifier `hospital_id`)

---

### **PHASE 5 : Système d'Onboarding** ⏱️ 2-3 jours

#### 5.1 Service de Provisioning
- [ ] Créer `TenantProvisioningService` :
  - `provision(Hospital $hospital)` : Crée la base, exécute migrations
  - `activateModules(Hospital $hospital, array $modules)` : Active les modules
  - `seed(Hospital $hospital)` : Exécute les seeders

#### 5.2 Commande Artisan
- [ ] Créer `php artisan tenant:create {name} {domain}`
- [ ] Créer `php artisan tenant:migrate {hospital_id}`
- [ ] Créer `php artisan tenant:seed {hospital_id}`

---

### **PHASE 6 : Gestion des Modules** ⏱️ 1-2 jours

#### 6.1 Système d'Activation de Modules
- [ ] Créer la table `hospital_modules` dans CORE
- [ ] Service pour activer/désactiver des modules par hôpital
- [ ] Middleware pour vérifier qu'un module est activé

#### 6.2 Migration des Modules Existants
- [ ] Adapter chaque module pour fonctionner sans `hospital_id`
- [ ] Tester chaque module individuellement

---

### **PHASE 7 : Tests et Validation** ⏱️ 3-5 jours

#### 7.1 Tests Unitaires
- [ ] Tests pour `TenantConnectionService`
- [ ] Tests pour `TenantProvisioningService`
- [ ] Tests pour le middleware

#### 7.2 Tests d'Intégration
- [ ] Tester la création d'un nouveau tenant
- [ ] Tester l'accès aux données d'un tenant
- [ ] Tester l'isolation entre tenants
- [ ] Tester les migrations par tenant

#### 7.3 Tests de Performance
- [ ] Vérifier que le basculement de connexion est rapide
- [ ] Tester avec plusieurs tenants simultanés

---

## 🔧 Détails Techniques

### 1. Configuration Dynamique des Connexions

```php
// config/database.php
'connections' => [
    'core' => [
        'driver' => 'mysql',
        'host' => env('CORE_DB_HOST', '127.0.0.1'),
        'database' => env('CORE_DB_DATABASE', 'medkey_core'),
        // ...
    ],
    'tenant' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => null, // Sera défini dynamiquement
        // ...
    ],
],
```

### 2. Service de Connexion Tenant

```php
// app/Core/Services/TenantConnectionService.php
class TenantConnectionService
{
    public function connect(Hospital $hospital): void
    {
        config([
            'database.connections.tenant.database' => $hospital->database_name,
            'database.connections.tenant.host' => $hospital->database_host ?? config('database.connections.tenant.host'),
        ]);
        
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Définir comme connexion par défaut pour les modèles tenant
        app()->instance('tenant.connection', 'tenant');
    }
}
```

### 3. Middleware Adapté

```php
// app/Http/Middleware/TenantMiddleware.php
public function handle(Request $request, Closure $next)
{
    $hospital = $this->identifyHospital($domain);
    
    if (!$hospital) {
        abort(404, 'Tenant not found');
    }
    
    // NOUVEAU : Basculer la connexion DB
    app(TenantConnectionService::class)->connect($hospital);
    
    // Définir le tenant dans l'application
    app()->instance('hospital', $hospital);
    
    return $next($request);
}
```

### 4. Modèle Hospital (CORE)

```php
// app/Core/Models/Hospital.php
class Hospital extends Model
{
    protected $connection = 'core';
    
    protected $fillable = [
        'name',
        'domain',
        'slug',
        'database_name',      // NOUVEAU
        'database_host',     // NOUVEAU
        'database_port',     // NOUVEAU
        'status',
        // ...
    ];
}
```

---

## 🚀 Processus d'Onboarding d'un Nouvel Hôpital

### Étape 1 : Création dans CORE
```bash
php artisan tenant:create "Hôpital Central" "hopital-central.medkey.com"
```

### Étape 2 : Provisioning Automatique
1. Création de la base `medkey_hospital_{id}`
2. Exécution des migrations tenant
3. Exécution des seeders de base
4. Activation des modules par défaut

### Étape 3 : Configuration
- Configuration des paramètres de l'hôpital
- Upload du logo
- Configuration des couleurs
- Activation des modules souhaités

### Étape 4 : Mise en Production
- Vérification de l'accès via le domaine
- Tests de fonctionnement
- Formation des utilisateurs

---

## ⚠️ Points d'Attention

### 1. Migration des Données Existantes
- **Risque** : Perte de données si migration mal effectuée
- **Solution** : Backup complet avant migration, tests sur environnement de staging

### 2. Performance
- **Risque** : Basculement de connexion peut être lent
- **Solution** : Cache de connexions, pool de connexions

### 3. Gestion des Migrations
- **Risque** : Migrations doivent être exécutées sur chaque tenant
- **Solution** : Commande `php artisan tenant:migrate {hospital_id}`

### 4. Backup et Restauration
- **Risque** : Chaque tenant nécessite son propre backup
- **Solution** : Scripts automatisés de backup par tenant

### 5. Compatibilité avec l'Existant
- **Risque** : Casser le code existant
- **Solution** : Migration progressive, tests exhaustifs

---

## 📈 Évolution Future : Vers les Microservices

### Architecture Cible (Long Terme)

```
┌─────────────────┐
│   API Gateway    │
└────────┬─────────┘
         │
    ┌────┴────┐
    │         │
┌───▼───┐ ┌──▼────┐
│ Core  │ │ Tenant│
│ Service│ │Service│
└───────┘ └───────┘
    │         │
┌───▼───┐ ┌──▼────┐
│  DB   │ │  DB   │
│ Core  │ │Tenant │
└───────┘ └───────┘
```

### Étapes de Migration
1. **Phase actuelle** : Monolithe avec DB-per-tenant
2. **Phase 2** : Extraire le service CORE en microservice
3. **Phase 3** : Extraire les services métiers (Patient, Payment, etc.)
4. **Phase 4** : Service IA séparé (Python/FastAPI)

---

## ✅ Checklist de Validation

### Avant la Mise en Production
- [ ] Tous les tests passent
- [ ] Migration des données existantes réussie
- [ ] Documentation complète
- [ ] Scripts de backup/restauration testés
- [ ] Processus d'onboarding documenté
- [ ] Formation de l'équipe effectuée
- [ ] Plan de rollback préparé

### Après la Mise en Production
- [ ] Monitoring des performances
- [ ] Vérification de l'isolation des données
- [ ] Tests de charge avec plusieurs tenants
- [ ] Documentation des incidents et solutions

---

## 📚 Ressources et Documentation

### Commandes Artisan à Créer
- `tenant:create` : Créer un nouveau tenant
- `tenant:migrate {id}` : Migrer un tenant spécifique
- `tenant:seed {id}` : Seeder un tenant
- `tenant:list` : Lister tous les tenants
- `tenant:backup {id}` : Backup d'un tenant
- `tenant:restore {id}` : Restaurer un tenant

### Services à Créer
- `TenantConnectionService` : Gestion des connexions
- `TenantProvisioningService` : Provisioning des tenants
- `TenantMigrationService` : Migration des données
- `TenantBackupService` : Backup/restauration

---

## 🎯 Estimation Globale

- **Phase 1** : 2-3 jours
- **Phase 2** : 1-2 jours
- **Phase 3** : 3-5 jours
- **Phase 4** : 2-3 jours
- **Phase 5** : 2-3 jours
- **Phase 6** : 1-2 jours
- **Phase 7** : 3-5 jours

**Total estimé** : 14-23 jours (3-5 semaines)

---

## 🔒 Sécurité et Conformité

### Mesures de Sécurité
1. **Chiffrement des données sensibles** : Chiffrement au niveau application
2. **Audit logs** : Logs de toutes les actions par tenant
3. **RBAC** : Rôles et permissions par tenant
4. **Isolation réseau** : Chaque base peut être sur un serveur différent

### Conformité Données de Santé
1. **RGPD** : Isolation physique facilite la conformité
2. **HDS** : Hébergement des données de santé conforme
3. **Audit** : Traçabilité complète des accès

---

## 📝 Notes Importantes

1. **Ne pas supprimer `hospital_id` immédiatement** : Garder pour migration progressive
2. **Tests exhaustifs** : Tester chaque module individuellement
3. **Documentation** : Documenter chaque étape
4. **Communication** : Informer l'équipe des changements

---

**Date de création** : 2025-01-XX  
**Version** : 1.0  
**Auteur** : Architecture Team
