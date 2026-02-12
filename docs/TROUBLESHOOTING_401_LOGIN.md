# Résolution de l'erreur 401 (Unauthorized) lors de la connexion

## ✅ Progrès

L'erreur 401 est un **bon signe** ! Cela signifie que :
- ✅ Le serveur Laravel fonctionne
- ✅ La route `/api/v1/login` est accessible
- ✅ Le problème CORS est résolu
- ✅ Le problème est maintenant au niveau de l'authentification

## 🔍 Causes Possibles

### 1. Identifiants incorrects (Email ou mot de passe)

**Vérification :**
- Vérifiez que l'email et le mot de passe sont corrects
- Vérifiez qu'il n'y a pas d'espaces avant/après les identifiants

**Solution :**
- Utilisez des identifiants valides qui existent dans votre base de données

---

### 2. Utilisateur n'existe pas dans la base de données

**Vérification :**
```bash
cd back-medpay
php artisan tinker
```

Puis dans tinker :
```php
$user = \Modules\Acl\Entities\User::where('email', 'votre-email@exemple.com')->first();
if ($user) {
    echo "Utilisateur trouvé: " . $user->email . "\n";
    echo "Hospital ID: " . $user->hospital_id . "\n";
} else {
    echo "Utilisateur non trouvé\n";
}
```

**Solution :**
- Créez un utilisateur dans la base de données
- Ou utilisez un utilisateur existant

---

### 3. Utilisateur sans `hospital_id`

**Vérification :**
```php
$user = \Modules\Acl\Entities\User::where('email', 'votre-email@exemple.com')->first();
if ($user && $user->hospital_id === null) {
    echo "ERREUR: L'utilisateur n'a pas de hospital_id\n";
}
```

**Solution :**
- Assurez-vous que tous les utilisateurs ont un `hospital_id` valide
- Mettez à jour l'utilisateur :
```php
$user->hospital_id = 1; // ID de l'hôpital
$user->save();
```

---

### 4. Problème de connexion à la base de données tenant

**Vérification :**
```bash
php artisan tinker
```

```php
// Vérifier la connexion par défaut
DB::connection()->getPdo();
echo "Connexion par défaut OK\n";

// Vérifier qu'il y a des utilisateurs
$count = \Modules\Acl\Entities\User::count();
echo "Nombre d'utilisateurs: $count\n";
```

**Solution :**
- Vérifiez votre fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votre_base_de_donnees
DB_USERNAME=root
DB_PASSWORD=
```

---

### 5. Problème avec le middleware TenantMiddleware

Le `TenantMiddleware` exclut la route `/api/v1/login`, donc aucun tenant n'est défini lors de la connexion. C'est **normal** et **attendu**.

Le `AuthController` gère cela en :
1. Trouvant l'utilisateur par email
2. Vérifiant le mot de passe
3. Récupérant le `hospital_id` de l'utilisateur
4. Définissant le tenant à partir de l'utilisateur

**Si cela ne fonctionne pas**, vérifiez les logs Laravel :
```bash
tail -f storage/logs/laravel.log
```

---

## 🧪 Test de Connexion

### Test 1 : Vérifier qu'un utilisateur existe

```bash
cd back-medpay
php artisan tinker
```

```php
$email = 'votre-email@exemple.com';
$user = \Modules\Acl\Entities\User::where('email', $email)->first();

if ($user) {
    echo "✅ Utilisateur trouvé\n";
    echo "   ID: {$user->id}\n";
    echo "   Email: {$user->email}\n";
    echo "   Hospital ID: " . ($user->hospital_id ?? 'NULL') . "\n";
    echo "   Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
} else {
    echo "❌ Utilisateur non trouvé\n";
}
```

---

### Test 2 : Vérifier le mot de passe

```php
$email = 'votre-email@exemple.com';
$password = 'votre-mot-de-passe';
$user = \Modules\Acl\Entities\User::where('email', $email)->first();

if ($user) {
    if (\Hash::check($password, $user->password)) {
        echo "✅ Mot de passe correct\n";
    } else {
        echo "❌ Mot de passe incorrect\n";
    }
}
```

---

### Test 3 : Créer un utilisateur de test

```php
$user = new \Modules\Acl\Entities\User();
$user->email = 'test@exemple.com';
$user->password = \Hash::make('password123');
$user->name = 'Utilisateur Test';
$user->hospital_id = 1; // ID de votre hôpital
$user->is_active = true;
$user->save();

echo "✅ Utilisateur créé: {$user->email}\n";
```

---

### Test 4 : Tester la connexion via curl

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:8080" \
  -d '{
    "email": "votre-email@exemple.com",
    "password": "votre-mot-de-passe"
  }'
```

**Résultat attendu :**
```json
{
  "data": {
    "token": "...",
    "user": {...}
  }
}
```

**Si erreur 401 :**
```json
{
  "erreur": "Email ou mot de passe non valide!"
}
```

---

## 📋 Checklist de Vérification

- [ ] Le serveur Laravel est démarré (`php artisan serve`)
- [ ] La base de données est accessible
- [ ] Il existe au moins un utilisateur dans la base de données
- [ ] L'utilisateur a un `hospital_id` valide
- [ ] L'email et le mot de passe sont corrects
- [ ] L'utilisateur est actif (`is_active = true`)
- [ ] Les logs Laravel ne montrent pas d'erreur critique

---

## 🔧 Solutions Rapides

### Solution 1 : Réinitialiser le mot de passe d'un utilisateur

```bash
php artisan tinker
```

```php
$user = \Modules\Acl\Entities\User::where('email', 'votre-email@exemple.com')->first();
if ($user) {
    $user->password = \Hash::make('nouveau-mot-de-passe');
    $user->save();
    echo "✅ Mot de passe réinitialisé\n";
}
```

---

### Solution 2 : Créer un utilisateur administrateur

```bash
php artisan tinker
```

```php
$user = new \Modules\Acl\Entities\User();
$user->email = 'admin@exemple.com';
$user->password = \Hash::make('admin123');
$user->name = 'Administrateur';
$user->hospital_id = 1; // Remplacez par l'ID de votre hôpital
$user->is_active = true;
$user->email_verified_at = now();
$user->save();

echo "✅ Utilisateur créé: {$user->email}\n";
echo "   Mot de passe: admin123\n";
```

---

### Solution 3 : Vérifier les logs Laravel

```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50

# Linux/Mac
tail -f storage/logs/laravel.log
```

Recherchez les messages d'erreur ou d'avertissement liés à :
- "Tentative de connexion"
- "Email ou mot de passe non valide"
- "utilisateur sans hospital_id"

---

## 📝 Note Importante

Le système multi-tenant fonctionne ainsi :
1. **Lors du login**, aucun tenant n'est défini (c'est normal)
2. Le `AuthController` trouve l'utilisateur par email
3. Il vérifie le mot de passe
4. Il récupère le `hospital_id` de l'utilisateur
5. Il définit le tenant à partir de l'utilisateur
6. Il connecte à la base de données tenant

Si l'erreur 401 persiste, c'est probablement que :
- L'utilisateur n'existe pas
- Le mot de passe est incorrect
- L'utilisateur n'a pas de `hospital_id`

---

**Date** : 2025-01-20
