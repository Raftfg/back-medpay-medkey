# Identifiants de Connexion - Multi-Tenant

## 🔐 Utilisateurs Admin Créés

Le `UserTableSeeder` crée automatiquement un utilisateur admin pour chaque hôpital actif.

### 📋 Hôpitaux Actifs et Identifiants

| Hôpital | Email | Mot de passe | Domain |
|---------|-------|--------------|--------|
| **Hôpital Central de Casablanca** | `admin@hopital-centralma-plateformecom` | `MotDePasse` | `hopital-central.ma-plateforme.com` |
| **Clinique Ibn Sina** | `admin@clinique-ibn-sinama-plateformecom` | `MotDePasse` | `clinique-ibn-sina.ma-plateforme.com` |
| **CHU Mohammed VI** | `admin@chu-mohammed6ma-plateformecom` | `MotDePasse` | `chu-mohammed6.ma-plateforme.com` |
| **Hôpital Moulay Youssef** | `admin@hopital-moulay-youssefma-plateformecom` | `MotDePasse` | `hopital-moulay-youssef.ma-plateforme.com` |

### ⚠️ Hôpital Inactif (ne peut pas se connecter)

| Hôpital | Email | Statut |
|---------|-------|--------|
| **Clinique Agdal** | `admin@clinique-agdalma-plateformecom` | `inactive` ❌ |

## 🚀 Connexion en Développement Local

### Option 1 : Utiliser le premier hôpital actif (Recommandé)

En développement local, si aucun domaine n'est configuré, le middleware utilise automatiquement le **premier hôpital actif** comme fallback.

**Identifiants recommandés :**
```
Email: admin@hopital-centralma-plateformecom
Mot de passe: MotDePasse
```

### Option 2 : Configurer le domaine local

Pour tester avec un hôpital spécifique, vous pouvez :

1. **Modifier votre fichier hosts** (`C:\Windows\System32\drivers\etc\hosts` sur Windows) :
```
127.0.0.1 hopital-central.ma-plateforme.com
127.0.0.1 clinique-ibn-sina.ma-plateforme.com
127.0.0.1 chu-mohammed6.ma-plateforme.com
127.0.0.1 hopital-moulay-youssef.ma-plateforme.com
```

2. **Accéder via le domaine** :
```
http://hopital-central.ma-plateforme.com:8080/auth-pages/login
```

3. **Se connecter avec l'admin correspondant** :
```
Email: admin@hopital-centralma-plateformecom
Mot de passe: MotDePasse
```

### Option 3 : Utiliser le header X-Tenant-Domain (Pour tests API)

Pour les tests API, vous pouvez utiliser le header personnalisé :

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Domain: hopital-central.ma-plateforme.com" \
  -d '{
    "email": "admin@hopital-centralma-plateformecom",
    "password": "MotDePasse"
  }'
```

## 📝 Notes Importantes

### 1. Format des Emails

Les emails sont générés automatiquement à partir du domaine de l'hôpital :
- Domaine : `hopital-central.ma-plateforme.com`
- Email : `admin@hopital-centralma-plateformecom` (points et espaces supprimés)

### 2. Mot de Passe

**Tous les utilisateurs admin ont le même mot de passe par défaut :**
```
MotDePasse
```

⚠️ **IMPORTANT** : Changez ce mot de passe en production !

### 3. Isolation Multi-Tenant

- Chaque utilisateur appartient à un seul hôpital
- Un utilisateur ne peut pas accéder aux données d'un autre hôpital
- Le middleware détecte automatiquement l'hôpital depuis le domaine ou l'utilisateur

### 4. Rôles et Permissions

Tous les utilisateurs admin ont :
- Rôle : `Admin`
- Toutes les permissions du système

## 🔍 Vérifier les Utilisateurs Créés

Pour vérifier quels utilisateurs ont été créés, exécutez :

```bash
php artisan tinker
```

Puis :

```php
// Lister tous les utilisateurs avec leur hôpital
\Modules\Acl\Entities\User::with('hospital')->get()->map(function($user) {
    return [
        'id' => $user->id,
        'email' => $user->email,
        'name' => $user->name . ' ' . $user->prenom,
        'hospital' => $user->hospital->name ?? 'N/A',
        'hospital_id' => $user->hospital_id,
    ];
});

// Lister les hôpitaux actifs
\Modules\Administration\Entities\Hospital::active()->get(['id', 'name', 'domain', 'status']);
```

## 🛠️ Créer un Nouvel Utilisateur

Pour créer un nouvel utilisateur pour un hôpital spécifique :

```bash
php artisan tinker
```

```php
$hospital = \Modules\Administration\Entities\Hospital::where('domain', 'hopital-central.ma-plateforme.com')->first();

$user = \Modules\Acl\Entities\User::create([
    'name' => 'John',
    'prenom' => 'Doe',
    'email' => 'john.doe@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('MotDePasse'),
    'hospital_id' => $hospital->id,
    'email_verified_at' => now(),
]);

// Assigner un rôle
$role = \Spatie\Permission\Models\Role::where(['name' => 'Admin', 'guard_name' => 'api'])->first();
$user->assignRole($role);
```

## ✅ Checklist de Connexion

- [ ] Les seeders ont été exécutés (`php artisan module:seed Administration` et `php artisan module:seed Acl`)
- [ ] Au moins un hôpital actif existe
- [ ] Au moins un utilisateur admin existe pour cet hôpital
- [ ] Le middleware `TenantMiddleware` est actif
- [ ] L'URL de connexion est correcte (ex: `http://localhost:8080/auth-pages/login`)

## 🚨 Dépannage

### Problème : "Email ou mot de passe non valide"

**Solutions :**
1. Vérifier que les seeders ont été exécutés
2. Vérifier que l'email est correct (sans points dans la partie après @)
3. Vérifier que le mot de passe est `MotDePasse` (sensible à la casse)
4. Vérifier que l'hôpital est actif

### Problème : "Aucun hôpital défini pour cette requête"

**Solutions :**
1. En développement local, le middleware utilise automatiquement le premier hôpital actif
2. Vérifier qu'au moins un hôpital actif existe
3. Vérifier que le middleware `TenantMiddleware` est enregistré dans `Kernel.php`

### Problème : "Vous n'avez pas accès aux données de cet hôpital"

**Solutions :**
1. Vérifier que l'utilisateur appartient au bon hôpital
2. Vérifier que le domaine correspond à l'hôpital de l'utilisateur
3. En développement local, le middleware utilise l'`hospital_id` de l'utilisateur
