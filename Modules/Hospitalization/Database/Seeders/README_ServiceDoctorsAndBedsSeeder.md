# ServiceDoctorsAndBedsSeeder

Ce seeder associe automatiquement des médecins et des lits à chaque service médical.

## Fonctionnalités

1. **Association de médecins aux services** :
   - Recherche tous les utilisateurs avec le rôle "Médecin" (ou variantes : Doctor, Docteur, medecin)
   - Crée des disponibilités (`DoctorAvailability`) pour chaque médecin dans chaque service
   - Répartition équitable des médecins entre les services (round-robin)
   - Crée des disponibilités du lundi au vendredi (08h-12h et 14h-18h)

2. **Association de lits aux services** :
   - Assigne des chambres existantes aux services (mise à jour de `services_id`)
   - Si aucune chambre n'existe, crée automatiquement des chambres et des lits pour chaque service
   - Répartition équitable des chambres entre les services
   - Crée les lits manquants dans les chambres selon leur capacité

## Prérequis

- Les services doivent exister (exécuter `ServiceTableSeeder` d'abord)
- Les utilisateurs/médecins doivent exister avec le rôle approprié
- (Optionnel) Les chambres peuvent exister, sinon elles seront créées automatiquement

## Utilisation

### Exécuter pour un tenant spécifique

```bash
php artisan tenant:seed {hospital_id} --class=Modules\\Hospitalization\\Database\\Seeders\\ServiceDoctorsAndBedsSeeder
```

Exemple :
```bash
php artisan tenant:seed 1 --class=Modules\\Hospitalization\\Database\\Seeders\\ServiceDoctorsAndBedsSeeder
```

### Exécuter via le DatabaseSeeder du module Hospitalization

Le seeder est automatiquement appelé par `HospitalizationDatabaseSeeder` :

```bash
php artisan tenant:seed {hospital_id} --class=Modules\\Hospitalization\\Database\\Seeders\\HospitalizationDatabaseSeeder
```

### Exécuter pour tous les tenants

```bash
php artisan tenant:seed-all --class=Modules\\Hospitalization\\Database\\Seeders\\ServiceDoctorsAndBedsSeeder --force
```

## Résultat attendu

Après l'exécution, chaque service aura :
- Au moins 1-3 médecins assignés (selon le nombre de médecins disponibles)
- Au moins 1-3 chambres assignées (selon le nombre de chambres disponibles)
- Des lits disponibles dans ces chambres (2 lits par chambre par défaut)

## Logs

Le seeder affiche des informations détaillées :
- Nombre de services traités
- Nombre de médecins assignés
- Nombre de chambres assignées
- Nombre de lits créés/assignés

## Notes

- Le seeder est idempotent : il peut être exécuté plusieurs fois sans créer de doublons
- Les disponibilités existantes ne sont pas recréées
- Les chambres déjà assignées à un service ne sont pas réassignées
- Les lits existants ne sont pas recréés
