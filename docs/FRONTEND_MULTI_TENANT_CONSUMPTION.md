# Frontend - Consommation Multi-Tenant

## ✅ Corrections Appliquées

Tous les appels frontend ont été corrigés pour respecter strictement le principe de multi-tenancy.

### 1. `user_service.js` ✅

**Problèmes corrigés :**
- Suppression de `hospital_id` dans tous les appels API
- Protection contre l'envoi accidentel de `hospital_id`

**Méthodes corrigées :**
- `login()` : Supprime `hospital_id` avant l'envoi
- `resetPassword()` : Supprime `hospital_id` avant l'envoi
- `requestPassword()` : Supprime `hospital_id` avant l'envoi
- `updateProfilPas()` : Supprime `hospital_id` avant l'envoi
- `updateProfil()` : Supprime `hospital_id` avant l'envoi

```javascript
const login = (credentials) => {
  // IMPORTANT: Ne jamais envoyer hospital_id depuis le frontend
  const { hospital_id, ...safeCredentials } = credentials;
  return Axios.post("/login", safeCredentials);
};
```

### 2. `login.vue` ✅

**Problèmes corrigés :**
- Utilisation de `tenantStorage` pour l'isolation multi-tenant
- Stockage correct de `hospital_id` avant utilisation de `tenantStorage`
- Stockage des données avec préfixe tenant

**Améliorations :**
```javascript
// Stocker hospital_id AVANT d'utiliser tenantStorage (pour le préfixe)
const hospitalId = response.data.data.hospital?.id || response.data.data.user?.hospital_id;
if (hospitalId) {
  localStorage.setItem("hospital_id", hospitalId);
}

// Utiliser tenantStorage pour l'isolation multi-tenant
tenantStorage.setItem("user", JSON.stringify(response.data.data.user));
tenantStorage.setItem("access_token", response.data.data.access_token);
tenantStorage.setItem("permission", JSON.stringify(normalizedPermissions));
```

### 3. `forget-password.vue` ✅

**Problèmes corrigés :**
- Suppression de `hospital_id` avant l'envoi
- Gestion améliorée des messages d'erreur
- Gestion du message générique du backend (ne révèle pas si l'email existe)

**Améliorations :**
```javascript
// IMPORTANT: Ne jamais envoyer hospital_id depuis le frontend
const { hospital_id, ...safeUser } = this.user;

LoginService.requestPassword(safeUser)
  .then((response) => {
    // Le backend retourne toujours un succès même si l'email n'existe pas (sécurité)
    const message = response.data?.data?.message || response.data?.message || "Si cet email existe, un lien de réinitialisation vous sera envoyé";
    // ...
  });
```

### 4. `reset-password.vue` ✅

**Problèmes corrigés :**
- Suppression de `hospital_id` avant l'envoi
- Protection contre l'envoi accidentel de `hospital_id`

**Améliorations :**
```javascript
// IMPORTANT: Ne jamais envoyer hospital_id depuis le frontend
const requestData = {
  email: this.user.email,
  password: this.user.password,
  password_confirmation: this.user.password_confirmation,
};
const { hospital_id, ...safeRequestData } = requestData;

LoginService.resetPassword(safeRequestData)
```

## 🔒 Règles de Sécurité Frontend

### 1. Ne jamais envoyer `hospital_id` depuis le frontend

```javascript
// ❌ MAUVAIS
const data = { email: "user@example.com", hospital_id: 1 };
Axios.post("/login", data);

// ✅ BON
const { hospital_id, ...safeData } = data;
Axios.post("/login", safeData);
```

### 2. Utiliser `tenantStorage` pour l'isolation

```javascript
// ✅ BON - Isolation par tenant
import { tenantStorage } from "@/_services/caller.services";

tenantStorage.setItem("user", JSON.stringify(user));
tenantStorage.setItem("access_token", token);

// ❌ MAUVAIS - Pas d'isolation
localStorage.setItem("user", JSON.stringify(user));
localStorage.setItem("access_token", token);
```

### 3. Stocker `hospital_id` dans `localStorage` (non préfixé)

```javascript
// ✅ BON - hospital_id est stocké sans préfixe pour être accessible
localStorage.setItem("hospital_id", hospitalId);

// Les autres données utilisent tenantStorage avec préfixe
tenantStorage.setItem("user", JSON.stringify(user));
```

## 📊 Mapping Backend ↔ Frontend

| Endpoint Backend | Méthode Frontend | Fichier | Statut |
|-----------------|------------------|---------|--------|
| `POST /api/v1/login` | `LoginService.login()` | `user_service.js` | ✅ |
| `POST /api/v1/logout` | `LoginService.logout()` | `user_service.js` | ✅ |
| `GET /api/v1/auth/user_current` | `LoginService.usercurrent()` | `user_service.js` | ✅ |
| `POST /api/v1/reset-password` | `LoginService.resetPassword()` | `user_service.js` | ✅ |
| `POST /api/v1/request-password` | `LoginService.requestPassword()` | `user_service.js` | ✅ |
| `POST /api/v1/users/changepassword` | `LoginService.updateProfilPas()` | `user_service.js` | ✅ |
| `POST /api/v1/users/changeprofile` | `LoginService.updateProfil()` | `user_service.js` | ✅ |

## 🛡️ Protection Multi-Niveaux Frontend

### Niveau 1 : Suppression de `hospital_id`
- Toutes les méthodes suppriment `hospital_id` avant l'envoi
- Protection contre l'envoi accidentel

### Niveau 2 : Isolation par `tenantStorage`
- Toutes les données sensibles utilisent `tenantStorage`
- Préfixe automatique avec `hospital_id`

### Niveau 3 : Validation Backend
- Le backend rejette toujours `hospital_id` (règle `prohibited`)
- Le backend définit `hospital_id` automatiquement

## ✅ Checklist Frontend

- [x] `hospital_id` jamais envoyé depuis le frontend
- [x] `tenantStorage` utilisé pour l'isolation
- [x] `hospital_id` stocké dans `localStorage` (non préfixé)
- [x] Messages d'erreur génériques gérés correctement
- [x] Gestion des erreurs HTTP (401, 403, 404, etc.)
- [x] Timeout configuré (30 secondes)
- [x] Headers d'authentification corrects
- [x] Base URL dynamique selon l'environnement

## 🔍 Tests Recommandés

1. ✅ Tester la connexion et vérifier que `hospital_id` n'est pas envoyé
2. ✅ Tester la déconnexion et vérifier le nettoyage de `tenantStorage`
3. ✅ Tester `forget-password` et vérifier que `hospital_id` n'est pas envoyé
4. ✅ Tester `reset-password` et vérifier que `hospital_id` n'est pas envoyé
5. ✅ Tester l'isolation : se connecter avec deux hôpitaux différents et vérifier que les données sont isolées
6. ✅ Tester le changement de domaine et vérifier que les données changent

## 📝 Notes Importantes

1. **Ne jamais envoyer `hospital_id`** : Le backend le définit automatiquement
2. **Utiliser `tenantStorage`** : Pour l'isolation des données sensibles
3. **Messages génériques** : Le backend ne révèle pas si un email existe
4. **Gestion d'erreurs** : Toutes les erreurs HTTP sont gérées correctement
5. **Base URL dynamique** : S'adapte automatiquement à l'environnement et au domaine
