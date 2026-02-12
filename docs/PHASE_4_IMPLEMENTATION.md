# Phase 4 : Adaptation des Modèles - Documentation

## Objectif

Supprimer complètement les colonnes `hospital_id` et les scopes globaux `HospitalScope` de l'architecture tenant, car l'isolation est maintenant assurée par la séparation des bases de données.

## Étapes

### 4.1 Création de la commande de suppression des colonnes hospital_id

Créer une commande Artisan `tenant:remove-hospital-id` qui :
- Détecte automatiquement toutes les tables avec `hospital_id` dans une base tenant
- Supprime les contraintes de clés étrangères vers `hospitals`
- Supprime les colonnes `hospital_id`
- Supprime les index associés

### 4.2 Suppression du trait BelongsToHospital

Retirer le trait `BelongsToHospital` de tous les modèles tenant (environ 50+ modèles).

### 4.3 Marquer HospitalScope comme obsolète

Marquer `HospitalScope` et le trait `BelongsToHospital` comme obsolètes, mais les conserver temporairement pour la compatibilité avec la base principale (pendant la période de transition).

### 4.4 Adapter les modèles pour utiliser la connexion tenant

S'assurer que tous les modèles tenant utilisent la connexion `tenant` par défaut.

### 4.5 Supprimer les relations hospital_id

Supprimer les méthodes `hospital()` dans les modèles tenant qui référencent la table `hospitals`.

## Modèles à adapter

D'après le grep, environ 50+ modèles utilisent `BelongsToHospital` :

- Modules\Acl\Entities\User
- Modules\Patient\Entities\Patiente
- Modules\Cash\Entities\CashRegister
- Modules\Movment\Entities\Movment
- Modules\Payment\Entities\Facture
- Modules\Stock\Entities\* (tous les modèles Stock)
- Modules\Administration\Entities\* (plusieurs modèles)
- Modules\Medicalservices\Entities\* (tous les records)
- Modules\Hospitalization\Entities\* (Room, Bed, BedPatient)
- Modules\Remboursement\Entities\*
- Modules\Recouvrement\Entities\*
- Modules\Tracking\Entities\ActivityLog
- Modules\Media\Entities\Media
- Modules\Notifier\Entities\NotifierTracking
- Modules\Absence\Entities\Absent

## Ordre d'exécution

1. **Créer la commande de suppression** : `tenant:remove-hospital-id`
2. **Exécuter la commande sur chaque base tenant** : Supprimer les colonnes `hospital_id`
3. **Retirer le trait BelongsToHospital** : Modifier tous les modèles
4. **Tester** : Vérifier que tout fonctionne correctement
