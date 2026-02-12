# Authentification Multi-Tenant - Guide Complet

## 🔐 Vue d'Ensemble

L'authentification dans une application multi-tenant doit garantir que :
1. ✅ Chaque utilisateur appartient à un seul hôpital
2. ✅ L'utilisateur ne peut accéder qu'aux données de son hôpital
3. ✅ Le tenant est correctement identifié et validé à chaque étape
4. ✅ Les tokens d'authentification sont associés au bon hôpital

## 📋 Processus d'Authentification

### ÉTAPE 1 : Requête de Connexion

```
POST /api/v1/login
{
    "email": "user@example.com",
    "password": "password123"
}
```

### ÉTAPE 2 : TenantMiddleware (Exécuté en premier)

Le `TenantMiddleware` tente de détecter l'hôpital :

1. **Par domaine** : `hopital1.com` → `hospital_id = 1`
2. **Par utilisateur authentifié** : Si l'utilisateur est déjà connecté, utilise `user->hospital_id`
3. **Fallback développement** : Premier hôpital actif (uniquement en `local`/`testing`)

### ÉTAPE 3 : AuthController@login

#### 3.1 Recherche de l'utilisateur

```php
// Recherche SANS filtre hospital_id d'abord (pour permettre la détection)
$user = User::withoutGlobalScopes()
    ->where('email', $email)
    ->first();
```

**Pourquoi sans Global Scope ?**
- Permet de trouver l'utilisateur même si le tenant n'est pas encore défini
- Le tenant sera défini depuis l'utilisateur si nécessaire

#### 3.2 Vérification du mot de passe

```php
if (!Hash::check($password, $user->password)) {
    // Erreur 401 - Ne pas révéler si l'email existe
    return error('Email ou mot de passe non valide!');
}
```

#### 3.3 Vérification hospital_id

```php
if ($user->hospital_id === null) {
    // Erreur 403 - Compte mal configuré
    return error('Votre compte n\'est pas configuré correctement.');
}
```

#### 3.4 Gestion de la cohérence Tenant ↔ Utilisateur

**Cas 1 : Aucun tenant détecté par domaine**
```php
if ($currentHospitalId === null) {
    // Utiliser l'hôpital de l'utilisateur
    $currentHospitalId = $user->hospital_id;
    setTenant($user->hospital);
}
```

**Cas 2 : Tenant détecté par domaine**
```php
if ($user->hospital_id !== $currentHospitalId) {
    // BLOQUER - L'utilisateur n'appartient pas à cet hôpital
    return error('Email ou mot de passe non valide!');
}
```

#### 3.5 Vérification de l'état de l'hôpital

```php
if (!$hospital || !$hospital->isActive()) {
    // BLOQUER - Hôpital inactif
    return error('Votre hôpital n\'est pas actif.');
}
```

#### 3.6 Création du token Passport

```php
$token = $user->createToken($user->uuid)->accessToken;
```

**Important :** Le token Passport est automatiquement associé à l'utilisateur, qui a déjà son `hospital_id`.

#### 3.7 Retour des données

```php
return [
    'access_token' => $token,
    'user' => $user,
    'role' => $role,
    'permissions' => $permissions,
    'hospital' => $hospital, // Inclus pour le frontend
];
```

### ÉTAPE 4 : EnsureUserBelongsToHospital (Sur chaque requête authentifiée)

Ce middleware vérifie à chaque requête que :

1. **L'utilisateur a un hospital_id**
```php
if ($user->hospital_id === null) {
    abort(403, 'Votre compte n\'est associé à aucun hôpital.');
}
```

2. **Le tenant est défini (ou le définit depuis l'utilisateur)**
```php
if ($currentHospitalId === null) {
    // Définir le tenant depuis l'utilisateur
    setTenant($user->hospital);
    $currentHospitalId = $hospital->id;
}
```

3. **L'utilisateur appartient au tenant courant**
```php
if ($user->hospital_id !== $currentHospitalId) {
    abort(403, 'Vous n\'avez pas accès aux données de cet hôpital.');
}
```

4. **L'hôpital est actif**
```php
if (!$hospital || !$hospital->isActive()) {
    abort(403, 'Votre hôpital n\'est pas actif.');
}
```

## 🛡️ Protection Multi-Niveaux

### Niveau 1 : TenantMiddleware
- Détecte le tenant depuis le domaine ou l'utilisateur
- Stocke le tenant dans la requête et la session

### Niveau 2 : EnsureUserBelongsToHospital
- Vérifie que l'utilisateur appartient au tenant
- Définit le tenant depuis l'utilisateur si nécessaire

### Niveau 3 : Global Scope HospitalScope
- Filtre automatiquement toutes les requêtes par `hospital_id`
- Garantit l'isolation même si les middlewares échouent

### Niveau 4 : Policies Multi-Tenant
- Vérifie les permissions ET l'appartenance à l'hôpital
- Utilise `belongsToCurrentHospital()` pour valider

## 🔍 Scénarios de Sécurité

### Scénario 1 : Utilisateur tente de se connecter avec un domaine d'un autre hôpital

```
1. Domaine: hopital1.com → hospital_id = 1
2. Utilisateur: user@example.com → hospital_id = 2
3. ❌ BLOQUÉ: "Email ou mot de passe non valide!"
```

### Scénario 2 : Utilisateur authentifié tente d'accéder à un autre hôpital

```
1. Requête avec token valide
2. TenantMiddleware détecte: hopital1.com → hospital_id = 1
3. EnsureUserBelongsToHospital vérifie: user->hospital_id = 2
4. ❌ BLOQUÉ: "Vous n'avez pas accès aux données de cet hôpital."
```

### Scénario 3 : Utilisateur sans hospital_id

```
1. Utilisateur authentifié mais hospital_id = null
2. EnsureUserBelongsToHospital détecte
3. ❌ BLOQUÉ: "Votre compte n'est associé à aucun hôpital."
```

### Scénario 4 : Hôpital inactif

```
1. Utilisateur authentifié avec hospital_id = 1
2. Hôpital 1 a status = 'inactive'
3. ❌ BLOQUÉ: "Votre hôpital n'est pas actif."
```

## 📊 Logs de Sécurité

Tous les événements critiques sont loggés :

```php
// Tentative de connexion avec utilisateur d'un autre hôpital
Log::warning('Tentative de connexion avec utilisateur d\'un autre hôpital', [
    'user_id' => $user->id,
    'user_hospital_id' => $user->hospital_id,
    'current_hospital_id' => $currentHospitalId,
    'email' => $email,
    'ip' => $request->ip(),
]);

// Tentative d'accès non autorisé
Log::warning('Tentative d\'accès non autorisé', [
    'user_id' => $user->id,
    'user_hospital_id' => $user->hospital_id,
    'current_hospital_id' => $currentHospitalId,
    'ip' => $request->ip(),
    'url' => $request->fullUrl(),
]);
```

## ✅ Checklist de Sécurité

- [x] Filtrage des utilisateurs par `hospital_id` lors de la connexion
- [x] Vérification que l'utilisateur appartient au tenant détecté
- [x] Validation de l'état de l'hôpital (actif/inactif)
- [x] Middleware de vérification sur chaque requête authentifiée
- [x] Global Scope pour isolation automatique des données
- [x] Policies multi-tenant pour autorisation
- [x] Logs de sécurité pour audit
- [x] Messages d'erreur génériques (ne pas révéler d'informations)
- [x] Validation que `hospital_id` n'est jamais envoyé depuis le frontend
- [x] Token Passport associé à l'utilisateur avec `hospital_id`

## 🔧 Configuration

### Ordre des Middlewares (Kernel.php)

```php
'api' => [
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\TenantMiddleware::class, // 1. Détecte le tenant
    \App\Http\Middleware\EnsureUserBelongsToHospital::class, // 2. Vérifie l'utilisateur
    \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class, // 3. Gère le token
],
```

**Ordre critique :**
1. `TenantMiddleware` doit être AVANT `EnsureUserBelongsToHospital`
2. `EnsureUserBelongsToHospital` doit être APRÈS `auth:api` (appliqué sur les routes)

## 📝 Notes Importantes

1. **Ne jamais révéler si un email existe ou non** : Messages d'erreur génériques
2. **Toujours vérifier l'état de l'hôpital** : Actif, inactif, suspendu
3. **Logs de sécurité** : Toutes les tentatives suspectes doivent être loggées
4. **Isolation stricte** : L'utilisateur ne peut jamais voir les données d'un autre hôpital
5. **Définition du tenant depuis l'utilisateur** : Si le tenant n'est pas détecté par domaine, utiliser l'`hospital_id` de l'utilisateur

## 🚀 Tests Recommandés

1. ✅ Connexion avec utilisateur du bon hôpital
2. ✅ Connexion avec utilisateur d'un autre hôpital (doit échouer)
3. ✅ Connexion avec utilisateur sans hospital_id (doit échouer)
4. ✅ Connexion avec hôpital inactif (doit échouer)
5. ✅ Accès aux données après connexion (doit être filtré par hospital_id)
6. ✅ Tentative d'accès avec token valide mais mauvais tenant (doit échouer)
