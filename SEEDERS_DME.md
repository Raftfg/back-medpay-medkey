# 🌱 Seeders DME - Guide d'exécution

## 📋 Vue d'ensemble

Des seeders ont été créés pour peupler les tables DME avec des données de test (2 lignes par table) :

- ✅ **Antecedents** : 2 antécédents médicaux/chirurgicaux
- ✅ **Allergies** : 2 allergies (médicament et aliment)
- ✅ **Clinical Observations** : 2 observations cliniques SOAP
- ✅ **Vaccinations** : 2 vaccinations
- ✅ **Prescriptions** : 2 prescriptions avec leurs items
- ✅ **DME Documents** : 2 documents médicaux

## 🚀 Exécution des seeders

### Option 1 : Exécution pour un tenant spécifique

```bash
# Exécuter les seeders pour un hôpital spécifique
php artisan tenant:seed {hospital_id} --class=Modules\\Movment\\Database\\Seeders\\DmeDatabaseSeeder

# Exemple pour l'hôpital ID 1
php artisan tenant:seed 1 --class=Modules\\Movment\\Database\\Seeders\\DmeDatabaseSeeder
```

### Option 2 : Exécution pour tous les tenants

```bash
# Exécuter les seeders DME sur tous les tenants
php artisan tenant:seed-all --class=Modules\\Movment\\Database\\Seeders\\DmeDatabaseSeeder --force

# Ou exécuter tous les seeders du module Movment
php artisan tenant:seed-all --class=Modules\\Movment\\Database\\Seeders\\MovmentDatabaseSeeder --force
```

### Option 3 : Via le seeder principal du module

Les seeders DME sont automatiquement appelés par `MovmentDatabaseSeeder` :

```bash
php artisan db:seed --class=Modules\\Movment\\Database\\Seeders\\MovmentDatabaseSeeder
```

## 📝 Structure des seeders

### DmeDatabaseSeeder
Seeder principal qui appelle tous les seeders DME individuels.

### Seeders individuels

1. **AntecedentSeeder** : Crée 2 antécédents
   - Hypertension artérielle (médical)
   - Appendicectomie (chirurgical)

2. **AllergieSeeder** : Crée 2 allergies
   - Pénicilline (médicament, sévère)
   - Arachides (aliment, anaphylaxie)

3. **ClinicalObservationSeeder** : Crée 2 observations SOAP
   - Consultation pour douleur thoracique
   - Suivi diabète de type 2

4. **VaccinationSeeder** : Crée 2 vaccinations
   - Vaccin COVID-19 (Pfizer)
   - Vaccin DTP (rappel)

5. **PrescriptionSeeder** : Crée 2 prescriptions avec items
   - Prescription antibiotique (2 médicaments)
   - Prescription antihypertenseur (1 médicament)

6. **DmeDocumentSeeder** : Crée 2 documents
   - Radiographie thorax
   - Compte-rendu d'hospitalisation

## ⚠️ Prérequis

Les seeders nécessitent :
- ✅ Au moins 2 patients existants dans la base de données
- ✅ Tables DME créées (via migrations)
- ✅ Connexion au tenant approprié

## 🔍 Vérification

Après exécution, vous pouvez vérifier les données :

```sql
-- Vérifier les antécédents
SELECT * FROM antecedents LIMIT 2;

-- Vérifier les allergies
SELECT * FROM allergies LIMIT 2;

-- Vérifier les observations
SELECT * FROM clinical_observations LIMIT 2;

-- Vérifier les vaccinations
SELECT * FROM vaccinations LIMIT 2;

-- Vérifier les prescriptions
SELECT * FROM prescriptions LIMIT 2;

-- Vérifier les items de prescription
SELECT * FROM prescription_items LIMIT 5;

-- Vérifier les documents
SELECT * FROM dme_documents LIMIT 2;
```

## 📌 Notes importantes

- Les seeders utilisent `updateOrCreate` pour éviter les doublons
- Les UUID sont générés automatiquement
- Les données sont liées aux 2 premiers patients de la base
- Les seeders respectent l'architecture multi-tenant (pas de `hospital_id`)
