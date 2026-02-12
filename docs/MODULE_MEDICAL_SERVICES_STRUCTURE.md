# Structure du Module "Services Médicaux"

## Vue d'ensemble

Le système de gestion des services médicaux est divisé en **deux modules principaux** :

1. **Module `Administration`** : Gère la **création, modification et suppression** des services médicaux (Service)
2. **Module `Medicalservices`** : Gère les **enregistrements/consultations** pour chaque service médical

---

## 1. Gestion des Services (Module Administration)

### Backend

**Contrôleur** : `Modules\Administration\Http\Controllers\AdministrationController`
- `getServices()` : Récupère tous les services
- `storeServices()` : Crée un nouveau service
- `deleteServices()` : Supprime un service
- `getServicesByDepartment()` : Récupère les services par département

**Routes API** : `Modules\Administration\Routes\api.php`
```php
Route::get('/administration/services', [AdministrationController::class, 'getServices']);
Route::post('/administration/services', [AdministrationController::class, 'storeServices']);
Route::post('/administration/services/delete', [AdministrationController::class, 'deleteServices']);
Route::get('/administration/departments/get/{department_id}', [AdministrationController::class, 'getServicesByDepartment']);
```

**Modèle** : `Modules\Administration\Entities\Service`
- Table : `services`
- Colonnes principales : `id`, `name`, `code`, `description`, `departments_id`, `hospital_id`

### Frontend

**Page principale** : `front-medpay-2/src/pages/module-medicalServices/index.vue`
- Affiche la liste des services médicaux
- Permet d'ajouter un nouveau service (formulaire)
- Permet de supprimer un service
- Permet de naviguer vers la gestion des actes médicaux d'un service

**Routes** : `/medical-services` (via `front-medpay-2/src/router/service.js`)

---

## 2. Gestion des Enregistrements par Service (Module Medicalservices)

### Backend

**Contrôleurs** : Un contrôleur par type de service
- `UrgencesRecordController` : Gère les enregistrements d'urgences
- `ConsultationRecordController` : Gère les consultations générales
- `ChirurgieRecordController` : Gère les interventions chirurgicales
- `LaboratoireRecordController` : Gère les analyses de laboratoire
- `ImagerieRecordController` : Gère les examens d'imagerie
- `MaterniteRecordController` : Gère les consultations de maternité
- `PediatrieRecordController` : Gère les consultations pédiatriques
- `InfirmerieRecordController` : Gère les soins infirmiers

**Routes API** : `Modules\Medicalservices\Routes\api.php`
```php
// Routes pour chaque type de service
Route::apiResource('urgences_records', UrgencesRecordController::class);
Route::apiResource('consultation_records', ConsultationRecordController::class);
Route::apiResource('chirurgie_records', ChirurgieRecordController::class);
Route::apiResource('laboratoire_records', LaboratoireRecordController::class);
Route::apiResource('imagerie_records', ImagerieRecordController::class);
Route::apiResource('maternite_records', MaterniteRecordController::class);
Route::apiResource('pediatrie_records', PediatrieRecordController::class);
Route::apiResource('infirmerie_records', InfirmerieRecordController::class);

// Routes spécifiques
Route::get('urgences/record', [UrgencesRecordController::class, 'getRecord']);
Route::get('urgences/categories', [UrgencesRecordController::class, 'getUrgencesCategories']);
Route::get('urgences/gravites', [UrgencesRecordController::class, 'getUrgencesGravities']);
Route::get('maternite/record', [MaterniteRecordController::class, 'getRecord']);
Route::get('pediatrie/record', [PediatrieRecordController::class, 'getRecord']);
Route::get('infirmerie/record', [InfirmerieRecordController::class, 'getRecord']);

// Routes utilitaires
Route::get('actes-by-services/{service_id}', [MedicalservicesController::class, 'getActesByServicesCode']);
Route::get('services-by-code/{service_code}', [MedicalservicesController::class, 'getServiceByCode']);
```

**Modèles** : `Modules\Medicalservices\Entities\*Record`
- Tables : `urgences_records`, `consultation_records`, `chirurgie_records`, etc.
- Colonnes communes : `id`, `uuid`, `services_id`, `movments_id`, `operator`, `created_at`, `updated_at`

### Frontend

**Pages par service** : `front-medpay-2/src/pages/module-medicalServices/`
- `urgences.vue` : Liste des patients en traitement aux urgences
- `consultation.vue` : Liste des consultations générales
- `chirurgie.vue` : Liste des interventions chirurgicales
- `laboratoire.vue` : Liste des analyses de laboratoire
- `imagerie.vue` : Liste des examens d'imagerie
- `maternite.vue` : Liste des consultations de maternité
- `pediatrie.vue` : Liste des consultations pédiatriques
- `infirmerie.vue` : Liste des soins infirmiers

**Page de traitement** : `treatment.vue`
- Permet de traiter un patient pour un service donné
- Utilise les composants dans `treatment/` selon le type de service

**Routes** : 
- `/medical-services/urgences`
- `/medical-services/consultation`
- `/medical-services/chirurgie`
- `/medical-services/laboratoire`
- `/medical-services/imagerie`
- `/medical-services/maternite`
- `/medical-services/pediatrie`
- `/medical-services/infirmerie`
- `/medical-services/treatement/:movment_id`

**Sidebar** : `front-medpay-2/src/pages/module-medicalServices/layout/sidebar.vue`
- Affiche la liste des services médicaux disponibles
- Permet de naviguer entre les différents services

---

## 3. Flux de Données

### Création d'un Service
1. **Frontend** : `module-medicalServices/index.vue` → Formulaire d'ajout
2. **API** : `POST /administration/services` → `AdministrationController::storeServices()`
3. **Base de données** : Insertion dans la table `services`

### Affichage des Patients par Service
1. **Frontend** : `module-medicalServices/{service}.vue` (ex: `urgences.vue`)
2. **API** : `GET /api/v1/movments/services/{service_id}` → `MovmentController::getMovmentsByService()`
3. **Base de données** : Récupération des mouvements (venues) pour le service

### Traitement d'un Patient
1. **Frontend** : Clic sur "Traiter le patient" → Navigation vers `/medical-services/treatement/:movment_id`
2. **Frontend** : `treatment.vue` → Formulaire de traitement selon le service
3. **API** : `POST /api/v1/{service}_records` → `{Service}RecordController::store()`
4. **Base de données** : Insertion dans la table `{service}_records`

---

## 4. Relations entre Modules

```
Service (Administration)
    ↓
Movment (Movment) - Venue du patient
    ↓
{Service}Record (Medicalservices) - Enregistrement du traitement
```

**Exemple** :
- Un patient arrive aux **Urgences** (Service)
- Une **venue** (Movment) est créée pour ce patient
- Un **enregistrement d'urgence** (UrgencesRecord) est créé lors du traitement

---

## 5. Points d'Attention

### Routes API
- Les routes du module `Administration` utilisent le préfixe `/administration/`
- Les routes du module `Medicalservices` utilisent le préfixe `/api/v1/`
- Le `RouteServiceProvider` du module `Administration` doit appeler `map()` dans `boot()`

### Multi-tenancy
- Tous les modèles utilisent la connexion `tenant`
- Les services et enregistrements sont isolés par `hospital_id`

### Frontend
- Les pages utilisent `Axios` depuis `@/_services/caller.services`
- Les appels API sont automatiquement préfixés avec `/api/v1/`
- La sidebar affiche dynamiquement les services disponibles

---

## 6. Commandes Artisan Utiles

```bash
# Créer un service (via Administration)
# Utiliser l'interface frontend ou directement via API

# Synchroniser les schémas pour tous les tenants
php artisan tenant:schema-sync

# Vérifier les routes
php artisan route:list --path=administration
php artisan route:list --path=medicalservices
```

---

## 7. Structure des Fichiers

```
back-medpay/
├── Modules/
│   ├── Administration/
│   │   ├── Http/Controllers/
│   │   │   └── AdministrationController.php (CRUD Services)
│   │   ├── Entities/
│   │   │   └── Service.php
│   │   └── Routes/
│   │       └── api.php
│   └── Medicalservices/
│       ├── Http/Controllers/
│       │   ├── UrgencesRecordController.php
│       │   ├── ConsultationRecordController.php
│       │   └── ... (autres contrôleurs)
│       ├── Entities/
│       │   ├── UrgencesRecord.php
│       │   └── ... (autres entités)
│       └── Routes/
│           └── api.php

front-medpay-2/src/
└── pages/
    └── module-medicalServices/
        ├── index.vue (Gestion des services)
        ├── urgences.vue
        ├── consultation.vue
        ├── ... (autres pages de service)
        ├── treatment.vue
        └── layout/
            └── sidebar.vue
```

---

## Conclusion

Le module "Services Médicaux" est **bien structuré** avec une séparation claire entre :
- **Gestion administrative** des services (Module Administration)
- **Gestion opérationnelle** des enregistrements par service (Module Medicalservices)

Pour ajouter/modifier/supprimer un service, utiliser l'interface dans `/medical-services` (page `index.vue`).
Pour traiter un patient dans un service, utiliser les pages spécifiques (`urgences.vue`, `consultation.vue`, etc.).
