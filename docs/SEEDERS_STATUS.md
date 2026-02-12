# État d'Avancement des Seeders

## ✅ Seeders Corrigés

1. ✅ `Database\Seeders\DatabaseSeeder` - Fonctionne
2. ✅ `Modules\Acl\Database\Seeders\AclDatabaseSeeder` - Fonctionne
3. ✅ `Modules\Stock\Database\Seeders\CategoryTableSeeder` - Corrigé (à tester)
4. ✅ `Modules\Acl\Database\Seeders\UserTableSeeder` - Corrigé (à tester)

## ⏳ Seeders à Corriger

Les seeders suivants doivent être adaptés pour supprimer les références à `hospital_id` et les boucles sur les hôpitaux :

1. ⏳ `Modules\Administration\Database\Seeders\AdministrationDatabaseSeeder`
   - Utilise `HospitalTableSeeder` qui cherche la table `hospitals` dans la base tenant
   - Solution : Adapter `HospitalTableSeeder` ou le retirer (les hôpitaux sont dans CORE)

2. ⏳ `Modules\Stock\Database\Seeders\StockDatabaseSeeder`
   - Utilise encore `Hospital::where('status', 'active')->get()`
   - Solution : Supprimer la boucle, créer directement les données

3. ⏳ `Modules\Patient\Database\Seeders\PatientDatabaseSeeder`
   - Utilise encore `Hospital::where('status', 'active')->get()`
   - Solution : Supprimer la boucle, créer directement les données

4. ⏳ `Modules\Cash\Database\Seeders\CashDatabaseSeeder`
   - Utilise encore `Hospital::where('status', 'active')->get()`
   - Solution : Supprimer la boucle, créer directement les données

5. ⏳ `Modules\Hospitalization\Database\Seeders\HospitalizationDatabaseSeeder`
   - Utilise encore `Hospital::where('status', 'active')->get()`
   - Solution : Supprimer la boucle, créer directement les données

6. ⏳ `Modules\Movment\Database\Seeders\MovmentTableSeeder`
   - Utilise `Patiente::where('hospital_id', $hospital->id)`
   - Solution : Utiliser `Patiente::all()` ou `Patiente::limit(5)->get()`

7. ⏳ `Modules\Medicalservices\Database\Seeders\ConsultationRecordTableSeeder`
   - Utilise `Movment::where('hospital_id', $hospital->id)`
   - Solution : Utiliser `Movment::all()` ou `Movment::limit(5)->get()`

8. ⏳ `Modules\Absence\Database\Seeders\AbsentTableSeeder`
   - Utilise `User::where('hospital_id', $hospital->id)`
   - Solution : Utiliser `User::all()` ou `User::limit(3)->get()`

9. ⏳ `Modules\Annuaire\Database\Seeders\AnnuaireDatabaseSeeder`
   - Utilise encore `Hospital::where('status', 'active')->get()`
   - Solution : Supprimer la boucle, créer directement les données

## 📝 Pattern de Correction

Voir `docs/SEEDERS_ADAPTATION_PATTERN.md` pour le pattern de correction à appliquer.

## 🚀 Prochaines Étapes

1. Corriger tous les seeders restants selon le pattern
2. Tester l'exécution complète des seeders
3. Vérifier que toutes les données sont créées correctement
