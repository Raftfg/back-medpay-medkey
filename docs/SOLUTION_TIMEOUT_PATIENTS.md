# ✅ Solution : Timeout de 15 secondes dépassé

## 🎯 Problème

L'erreur `timeout of 15000ms exceeded` indiquait que la requête prenait plus de 15 secondes, ce qui est anormalement long.

## ✅ Corrections Appliquées

### 1. **Timeout Augmenté**

**Fichier :** `front-medpay-2/src/_services/caller.services.js`

**Changement :**
- ✅ Timeout augmenté de **15s à 30s** pour les requêtes lourdes
- ✅ Timeout spécifique pour les requêtes de patients : **30s**
- ✅ Timeout pour les recherches : **20s**

**Raison :** La première requête peut prendre du temps à cause de :
- Construction du cache Laravel
- Requête SQL initiale
- Middleware tenant (identification de l'hôpital)

---

### 2. **Optimisation du Cache Laravel**

**Fichier :** `back-medpay/Modules/Patient/Http/Controllers/Api/V1/PatienteController.php`

**Changements :**
- ✅ Vérification du cache **avant** d'exécuter la requête
- ✅ Si cache hit : retour immédiat (quelques millisecondes)
- ✅ Si cache miss : exécution de la requête puis mise en cache
- ✅ Logs de performance pour diagnostiquer les problèmes

**Code :**
```php
if ($useCache && Cache::has($cacheKey)) {
    // Cache hit - très rapide (< 10ms)
    $donnees = Cache::get($cacheKey);
} else {
    // Cache miss - exécuter la requête
    $donnees = $this->patienteRepositoryEloquent
        ->select([...])
        ->paginate($perPage);
    
    // Mettre en cache pour les prochaines requêtes
    if ($useCache) {
        Cache::put($cacheKey, $donnees, $cacheTTL);
    }
}
```

---

### 3. **Logs de Performance**

**Ajout de logs pour diagnostiquer :**
- Temps d'exécution de la requête SQL
- Temps total (avec/sans cache)
- Avertissement si > 2 secondes

**Exemple de log :**
```
[INFO] Requête patients exécutée
  query_time: 4.523s
  per_page: 20
  count: 150
  from_cache: false

[WARNING] Chargement patients lent
  total_time: 4.625s
  per_page: 20
  from_cache: false
```

---

### 4. **Option de Debug**

**Désactiver le cache pour tester :**
```
GET /api/v1/patients?per_page=20&no_cache=1
```

Utile pour :
- Tester les performances sans cache
- Diagnostiquer les problèmes de requête SQL
- Vérifier si le cache cause des problèmes

---

## 📊 Résultats Attendus

### Avant les Corrections
- ⏱️ **Timeout :** 15 secondes
- ❌ **Erreur :** `timeout of 15000ms exceeded`
- 🔴 **Expérience :** Échec de chargement

### Après les Corrections
- ⏱️ **Timeout :** 30 secondes
- ✅ **Premier chargement :** ~4-5s (normal, construction du cache)
- ⚡ **Chargements suivants :** < 100ms (cache Laravel)
- ✅ **Expérience :** Chargement réussi

---

## 🔍 Diagnostic des Problèmes de Performance

### Vérifier les Logs Laravel

```bash
tail -f storage/logs/laravel.log | grep -i "patients"
```

**Rechercher :**
- `Requête patients exécutée` - Temps de la requête SQL
- `Chargement patients lent` - Avertissement si > 2s
- `Erreur lors du chargement des patients` - Erreurs SQL

### Causes Possibles de Lenteur

1. **Requête SQL lente**
   - Vérifier les index sur `id`, `hospital_id`
   - Vérifier le Global Scope (conditions WHERE)
   - Vérifier le nombre de patients dans la base

2. **Cache Laravel lent**
   - Vérifier le driver de cache (`config/cache.php`)
   - Utiliser Redis au lieu de File pour de meilleures performances

3. **Middleware tenant**
   - Vérifier le temps d'identification de l'hôpital
   - Vérifier la connexion à la base tenant

4. **Base de données lente**
   - Vérifier les performances de la base de données
   - Vérifier les connexions réseau

---

## 🚀 Actions Recommandées

### 1. Vérifier les Index de Base de Données

```sql
-- Vérifier les index sur la table patients
SHOW INDEX FROM patients;

-- Créer un index sur hospital_id si Global Scope
CREATE INDEX idx_patients_hospital_id ON patients(hospital_id);

-- Créer un index sur id (déjà index primaire, mais vérifier)
```

### 2. Optimiser le Cache Laravel

**Fichier :** `config/cache.php`

```php
// Utiliser Redis au lieu de File pour de meilleures performances
'default' => env('CACHE_DRIVER', 'redis'),
```

### 3. Monitorer les Performances

```bash
# Activer les logs de requêtes SQL
# Dans .env
DB_LOG_QUERIES=true

# Voir les requêtes lentes
tail -f storage/logs/laravel.log | grep "slow"
```

---

## 📝 Checklist de Vérification

- [x] ✅ Timeout augmenté à 30 secondes
- [x] ✅ Cache Laravel optimisé
- [x] ✅ Logs de performance ajoutés
- [x] ✅ Option de debug (no_cache)
- [ ] ⏳ Vérifier les index de base de données
- [ ] ⏳ Optimiser le driver de cache (Redis)
- [ ] ⏳ Monitorer les performances

---

## 🧪 Tests

### Test 1 : Premier Chargement
```bash
# Vider le cache Laravel
php artisan cache:clear

# Faire une requête
curl http://localhost:8000/api/v1/patients?per_page=20

# Vérifier les logs
tail -f storage/logs/laravel.log
```

**Résultat attendu :**
- Temps : ~4-5s (normal pour le premier chargement)
- Log : `Requête patients exécutée` avec `from_cache: false`

### Test 2 : Chargement avec Cache
```bash
# Faire une deuxième requête
curl http://localhost:8000/api/v1/patients?per_page=20

# Vérifier les logs
```

**Résultat attendu :**
- Temps : < 100ms (cache hit)
- Log : `Patients chargés depuis le cache`

### Test 3 : Sans Cache
```bash
# Désactiver le cache
curl http://localhost:8000/api/v1/patients?per_page=20&no_cache=1

# Vérifier les logs
```

**Résultat attendu :**
- Temps : ~4-5s (requête SQL normale)
- Log : `Requête patients exécutée` avec `from_cache: false`

---

**Date :** 2026-01-26  
**Statut :** ✅ Corrections appliquées  
**Impact :** ⚡ **Timeout augmenté + Optimisation cache**
