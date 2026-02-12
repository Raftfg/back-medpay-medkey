# Exemple : Créer un Hôpital

## 🎯 Objectif

Créer un hôpital de test dans la base CORE pour tester le système multi-tenant.

---

## 🚀 Méthode 1 : Commande Artisan (Recommandée)

### Commande de base

```bash
php artisan hospital:create "Hôpital Central"
```

Cette commande :
- ✅ Génère automatiquement le domaine (`hopital-central.medkey.com`)
- ✅ Génère automatiquement le nom de la base (`medkey_hopital_central`)
- ✅ Crée l'hôpital avec le statut `provisioning`

### Commande complète avec options

```bash
php artisan hospital:create "Hôpital Central" \
    --domain="hopital-central.medkey.com" \
    --database="medkey_hospital_1" \
    --host="127.0.0.1" \
    --port="3306" \
    --status="provisioning" \
    --email="contact@hopital-central.com" \
    --phone="+33 1 23 45 67 89" \
    --address="123 Rue de la Santé, 75014 Paris"
```

### Options disponibles

| Option | Description | Défaut |
|--------|-------------|--------|
| `--domain` | Domaine de l'hôpital | Généré automatiquement |
| `--database` | Nom de la base de données | Généré automatiquement |
| `--host` | Host de la base de données | `127.0.0.1` |
| `--port` | Port de la base de données | `3306` |
| `--status` | Statut (active, inactive, suspended, provisioning) | `provisioning` |
| `--email` | Email de l'hôpital | `null` |
| `--phone` | Téléphone de l'hôpital | `null` |
| `--address` | Adresse de l'hôpital | `null` |

---

## 📝 Méthode 2 : Via Tinker (Laravel)

```bash
php artisan tinker
```

Puis dans Tinker :

```php
use App\Core\Models\Hospital;

$hospital = Hospital::create([
    'name' => 'Hôpital Central',
    'domain' => 'hopital-central.medkey.com',
    'database_name' => 'medkey_hospital_1',
    'status' => 'provisioning',
]);

// Activer des modules
use App\Core\Models\HospitalModule;

HospitalModule::create([
    'hospital_id' => $hospital->id,
    'module_name' => 'Patient',
    'is_enabled' => true,
]);

HospitalModule::create([
    'hospital_id' => $hospital->id,
    'module_name' => 'Payment',
    'is_enabled' => true,
]);
```

---

## 📝 Méthode 3 : Via Seeder

Créez un seeder dans `database/seeders/Core/HospitalSeeder.php` :

```php
<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;

class HospitalSeeder extends Seeder
{
    public function run()
    {
        $hospital = Hospital::create([
            'name' => 'Hôpital Central',
            'domain' => 'hopital-central.medkey.com',
            'database_name' => 'medkey_hospital_1',
            'status' => 'provisioning',
        ]);

        // Activer des modules
        $modules = ['Acl', 'Administration', 'Patient', 'Payment'];
        foreach ($modules as $moduleName) {
            HospitalModule::create([
                'hospital_id' => $hospital->id,
                'module_name' => $moduleName,
                'is_enabled' => true,
            ]);
        }
    }
}
```

Puis exécutez :

```bash
php artisan db:seed --class="Database\Seeders\Core\HospitalSeeder" --database=core
```

---

## ✅ Vérification

Après avoir créé l'hôpital, vérifiez qu'il existe :

```bash
php artisan tinker
```

```php
use App\Core\Models\Hospital;

// Lister tous les hôpitaux
Hospital::all();

// Trouver un hôpital par domaine
Hospital::where('domain', 'hopital-central.medkey.com')->first();

// Vérifier les modules activés
$hospital = Hospital::find(1);
$hospital->enabledModules;
```

---

## 🎯 Prochaines Étapes

Une fois l'hôpital créé :

1. **Créer la base de données tenant** :
   ```sql
   CREATE DATABASE `medkey_hospital_1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Exécuter les migrations tenant** (quand la commande sera créée) :
   ```bash
   php artisan tenant:migrate 1
   ```

3. **Exécuter les seeders tenant** (quand la commande sera créée) :
   ```bash
   php artisan tenant:seed 1
   ```

---

## 📚 Exemples Complets

### Exemple 1 : Hôpital simple

```bash
php artisan hospital:create "Hôpital Central"
```

### Exemple 2 : Hôpital avec toutes les informations

```bash
php artisan hospital:create "Hôpital Saint-Antoine" \
    --domain="saint-antoine.medkey.com" \
    --database="medkey_saint_antoine" \
    --email="contact@saint-antoine.fr" \
    --phone="+33 1 23 45 67 89" \
    --address="184 Rue du Faubourg Saint-Antoine, 75012 Paris" \
    --status="active"
```

### Exemple 3 : Hôpital avec base de données distante

```bash
php artisan hospital:create "Hôpital Régional" \
    --domain="hopital-regional.medkey.com" \
    --database="medkey_regional" \
    --host="192.168.1.100" \
    --port="3306"
```

---

**Date de création** : 2025-01-20
