# Solution pour l'erreur 401 lors de la connexion

## ✅ Diagnostic Effectué

Le diagnostic a montré que :
- ✅ La base de données fonctionne
- ✅ Il y a 5 utilisateurs dans la base de données
- ✅ Tous les utilisateurs ont un `hospital_id`
- ✅ L'hôpital est actif
- ✅ La route de login existe

## 🔍 Cause Probable

L'erreur 401 est probablement due à :
1. **Mot de passe incorrect** - Le mot de passe saisi ne correspond pas au mot de passe hashé dans la base de données
2. **Email incorrect** - L'email saisi ne correspond à aucun utilisateur

## 🧪 Test de Connexion

### Option 1 : Tester avec curl

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:8080" \
  -d '{"email":"admin@medkey.com","password":"votre-mot-de-passe"}'
```

### Option 2 : Vérifier le mot de passe dans tinker

```bash
cd back-medpay
php artisan tinker
```

Puis :
```php
$user = \Modules\Acl\Entities\User::where('email', 'admin@medkey.com')->first();
if ($user) {
    // Tester un mot de passe
    $password = 'votre-mot-de-passe';
    if (\Hash::check($password, $user->password)) {
        echo "✅ Mot de passe correct\n";
    } else {
        echo "❌ Mot de passe incorrect\n";
    }
}
```

### Option 3 : Réinitialiser le mot de passe

Si vous ne connaissez pas le mot de passe, réinitialisez-le :

```bash
php artisan tinker
```

```php
$user = \Modules\Acl\Entities\User::where('email', 'admin@medkey.com')->first();
if ($user) {
    $user->password = \Hash::make('nouveau-mot-de-passe');
    $user->save();
    echo "✅ Mot de passe réinitialisé: nouveau-mot-de-passe\n";
}
```

## 📋 Utilisateurs Disponibles

D'après le diagnostic, voici les utilisateurs disponibles :

1. **admin@medkey.com** (Hospital ID: 1)
2. **admin@hopital-centralma-plateformecom** (Hospital ID: 1)
3. **admin@clinique-ibn-sinama-plateformecom** (Hospital ID: 2)
4. **admin@chu-mohammed6ma-plateformecom** (Hospital ID: 3)
5. **admin@hopital-moulay-youssefma-plateformecom** (Hospital ID: 4)

## 🔧 Solution Rapide

### Étape 1 : Réinitialiser le mot de passe

```bash
cd back-medpay
php artisan tinker
```

```php
$user = \Modules\Acl\Entities\User::where('email', 'admin@medkey.com')->first();
$user->password = \Hash::make('admin123');
$user->save();
echo "Mot de passe réinitialisé: admin123\n";
```

### Étape 2 : Tester la connexion

Dans votre frontend, utilisez :
- **Email** : `admin@medkey.com`
- **Mot de passe** : `admin123`

## 📝 Note Importante

L'erreur 401 signifie que l'authentification a échoué. Les raisons possibles sont :
- Email incorrect
- Mot de passe incorrect
- Utilisateur désactivé (si applicable)
- Problème de configuration

Dans votre cas, tout semble correctement configuré, donc le problème est probablement le mot de passe.

## 🚀 Prochaines Étapes

1. Réinitialisez le mot de passe d'un utilisateur
2. Testez la connexion avec les nouveaux identifiants
3. Si ça ne fonctionne toujours pas, vérifiez les logs Laravel :
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

**Date** : 2025-01-20
