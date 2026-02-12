# Module de Paramètres Hôpital Complet - Documentation

## Vue d'ensemble

Ce module permet de gérer les paramètres d'apparence et de configuration pour chaque hôpital dans une application multi-tenant. Les paramètres incluent :
- Nom de l'application
- Logo (avec upload)
- Couleur principale (theme color)
- Couleur secondaire
- Texte du footer
- Titre du dashboard

**Caractéristiques principales :**
- ✅ Isolation multi-tenant stricte (chaque hôpital a ses propres paramètres)
- ✅ Application immédiate des changements (couleur, logo, nom)
- ✅ Upload de logo avec preview
- ✅ Validation côté frontend et backend
- ✅ Fallback pour paramètres manquants

---

## 1. Backend Laravel

### 1.1 Structure de la base de données

Les paramètres sont stockés dans la table `hospital_settings` avec la structure suivante :

```sql
- id
- hospital_id (FK vers hospitals)
- key (ex: 'app.name', 'app.logo', 'app.primary_color')
- value (valeur du paramètre)
- type ('string', 'integer', 'boolean', 'json', 'array')
- group ('appearance', 'general', 'modules', 'security', 'medical')
- description
- is_public (boolean)
- created_at, updated_at
```

### 1.2 Seeder des paramètres par défaut

**Fichier** : `Modules/Administration/Database/Seeders/HospitalSettingTableSeeder.php`

Le seeder crée automatiquement les paramètres suivants pour chaque hôpital actif :

- `app.name` : Nom de l'application (utilise le nom de l'hôpital par défaut)
- `app.logo` : Logo de l'hôpital (`/images/logo-default.png`)
- `app.primary_color` : Couleur principale (`#0b5d3f`)
- `app.secondary_color` : Couleur secondaire (`#6c757d`)
- `app.footer_text` : Texte du footer (`© 2025 Medkey - Tous droits réservés`)
- `app.dashboard_title` : Titre du dashboard (`Tableau de bord`)

**Exécution** :
```bash
php artisan db:seed --class=Modules\\Administration\\Database\\Seeders\\HospitalSettingTableSeeder
```

### 1.3 Endpoints API

#### GET `/api/v1/settings`
Récupère tous les paramètres de l'hôpital courant.

**Réponse** :
```json
{
  "data": [
    {
      "key": "app.name",
      "value": "Hôpital Central",
      "type": "string",
      "group": "appearance",
      "description": "Nom de l'application/hôpital",
      "is_public": true
    },
    ...
  ]
}
```

#### GET `/api/v1/settings/{key}`
Récupère un paramètre spécifique.

#### POST `/api/v1/settings`
Crée ou met à jour un paramètre.

**Body** :
```json
{
  "key": "app.name",
  "value": "Nouveau nom",
  "type": "string",
  "group": "appearance",
  "description": "Nom de l'application",
  "is_public": true
}
```

#### PATCH `/api/v1/settings/{key}`
Met à jour un paramètre spécifique.

**Body** :
```json
{
  "value": "Nouvelle valeur",
  "type": "string",
  "group": "appearance"
}
```

#### PATCH `/api/v1/settings`
Met à jour plusieurs paramètres en une seule fois.

**Body** :
```json
{
  "settings": [
    {
      "key": "app.name",
      "value": "Nouveau nom",
      "type": "string",
      "group": "appearance"
    },
    {
      "key": "app.primary_color",
      "value": "#007bff",
      "type": "string",
      "group": "appearance"
    }
  ]
}
```

#### POST `/api/v1/settings/upload-logo`
Upload le logo de l'hôpital.

**Body** : `FormData` avec champ `logo` (fichier image)

**Validation** :
- Format : JPEG, PNG, JPG, GIF, SVG, WEBP
- Taille max : 2MB

**Réponse** :
```json
{
  "data": {
    "key": "app.logo",
    "value": "/storage/hospitals/1/logos/logo_1234567890_abc123.png",
    "url": "http://localhost:8000/storage/hospitals/1/logos/logo_1234567890_abc123.png"
  }
}
```

#### GET `/api/v1/hospital-settings/public`
Récupère les paramètres publics (accessibles sans authentification).

### 1.4 Sécurité Multi-Tenant

**Isolation garantie par :**

1. **Global Scope** : `HospitalScope` filtre automatiquement toutes les requêtes par `hospital_id
2. **Service** : `HospitalSettingsService` utilise toujours `currentHospitalId()` (défini par `TenantMiddleware`)
3. **Policy** : `HospitalSettingPolicy` vérifie que l'utilisateur appartient à l'hôpital courant
4. **FormRequest** : Interdit explicitement `hospital_id` depuis le frontend

**Le frontend ne doit JAMAIS envoyer `hospital_id` !**

### 1.5 Configuration Storage

Assurez-vous que le lien symbolique storage est créé :

```bash
php artisan storage:link
```

Les logos sont stockés dans : `storage/app/public/hospitals/{hospital_id}/logos/`

---

## 2. Frontend Vue.js

### 2.1 Structure des composants

```
src/
├── components/
│   └── SettingsApplier.vue          # Applique dynamiquement les paramètres
├── pages/
│   └── configuration/
│       └── settings/
│           ├── index.vue            # Page principale avec onglets
│           ├── SettingsGroup.vue    # Composant générique pour groupes
│           ├── SettingsAppearance.vue # Composant spécifique pour apparence
│           └── LogoUpload.vue      # Composant upload logo avec preview
├── _services/
│   └── settings.service.js          # Service API pour paramètres
└── store/
    └── settings.js                  # Store Vuex (optionnel)
```

### 2.2 Service API

**Fichier** : `src/_services/settings.service.js`

Méthodes disponibles :
- `getAll(group?)` : Récupère tous les paramètres
- `get(key)` : Récupère un paramètre spécifique
- `set(setting)` : Crée ou met à jour un paramètre
- `update(key, data)` : Met à jour un paramètre
- `updateMany(settings)` : Met à jour plusieurs paramètres
- `uploadLogo(file)` : Upload le logo

### 2.3 Composant SettingsApplier

**Fichier** : `src/components/SettingsApplier.vue`

Ce composant invisible :
- Charge les paramètres publics au montage
- Écoute les événements `settings:update`
- Applique immédiatement les changements :
  - Couleur principale → navbar, éléments avec couleur primaire
  - Logo → images avec alt="logo"
  - Nom → title, éléments avec classe `.app-name`
  - Footer text → éléments avec classe `.footer-text`
  - Dashboard title → éléments avec classe `.dashboard-title`

**Intégration** : Ajouté dans `App.vue` pour être actif sur toute l'application.

### 2.4 Composant SettingsAppearance

**Fichier** : `src/pages/configuration/settings/SettingsAppearance.vue`

Interface complète pour gérer les paramètres d'apparence :
- Nom de l'application (input text)
- Logo (composant LogoUpload)
- Couleur principale (input color + text avec validation hex)
- Couleur secondaire (input color + text avec validation hex)
- Texte du footer (input text)
- Titre du dashboard (input text)

**Fonctionnalités :**
- Validation côté client (format couleur hex)
- Application immédiate via événement `settings:update`
- Bouton "Appliquer immédiatement" pour tester les changements

### 2.5 Composant LogoUpload

**Fichier** : `src/pages/configuration/settings/LogoUpload.vue`

Composant pour upload de logo avec :
- Preview du logo actuel
- Sélection de fichier avec validation
- Preview avant upload
- Upload avec progression
- Gestion d'erreurs

**Validation côté client :**
- Formats acceptés : JPEG, PNG, JPG, GIF, SVG, WEBP
- Taille max : 2MB

### 2.6 Application dynamique des paramètres

#### Couleur principale

```javascript
// Appliquée via CSS variables et style inline
document.documentElement.style.setProperty('--primary-color', '#0b5d3f');
document.querySelector('.navbar').style.backgroundColor = '#0b5d3f';
```

#### Logo

```javascript
// Mise à jour des images logo
document.querySelectorAll('img[alt="logo"]').forEach(img => {
  img.src = '/storage/hospitals/1/logos/logo.png';
});
```

#### Nom de l'application

```javascript
// Mise à jour du titre
document.querySelector('title').textContent = 'Hôpital Central - Medkey';
```

---

## 3. Utilisation

### 3.1 Initialisation

1. **Exécuter le seeder** :
```bash
php artisan db:seed --class=Modules\\Administration\\Database\\Seeders\\HospitalSettingTableSeeder
```

2. **Créer le lien symbolique storage** :
```bash
php artisan storage:link
```

3. **Accéder à l'interface** :
```
/configuration/settings/index
```

### 3.2 Modifier les paramètres

1. Aller dans **Configuration > Paramètres de l'hôpital**
2. Cliquer sur l'onglet **Apparence**
3. Modifier les paramètres souhaités
4. Cliquer sur **"Appliquer immédiatement"** pour voir les changements
5. Cliquer sur **"Enregistrer les modifications"** pour sauvegarder

### 3.3 Upload de logo

1. Dans l'onglet **Apparence**, section **Logo**
2. Cliquer sur **"Choisir un nouveau logo"**
3. Sélectionner une image (max 2MB)
4. Vérifier la preview
5. Cliquer sur **"Uploader le logo"**
6. Le logo est appliqué immédiatement

### 3.4 Changer la couleur principale

1. Dans l'onglet **Apparence**, section **Couleur principale**
2. Utiliser le sélecteur de couleur ou saisir un code hex (ex: `#0b5d3f`)
3. Cliquer sur **"Appliquer immédiatement"** pour voir le changement
4. Cliquer sur **"Enregistrer les modifications"** pour sauvegarder

---

## 4. Fallback et valeurs par défaut

Si un paramètre n'existe pas, les valeurs par défaut suivantes sont utilisées :

- `app.name` : `'Medkey'`
- `app.logo` : `'/images/logo-default.png'`
- `app.primary_color` : `'#0b5d3f'`
- `app.secondary_color` : `'#6c757d'`
- `app.footer_text` : `'© 2025 Medkey - Tous droits réservés'`
- `app.dashboard_title` : `'Tableau de bord'`

Ces valeurs sont définies dans :
- Backend : `HospitalSettingsService` (getters avec `$default`)
- Frontend : `SettingsApplier.vue` et `SettingsAppearance.vue`

---

## 5. Validation

### 5.1 Backend

- **Logo upload** : Format image, max 2MB
- **Couleur** : Format hexadécimal (validé dans FormRequest si nécessaire)
- **Nom** : String, max 255 caractères

### 5.2 Frontend

- **Couleur** : Regex `/^#[0-9A-Fa-f]{6}$/`
- **Logo** : Format et taille vérifiés avant upload
- **Champs requis** : Nom et couleur principale

---

## 6. Multi-Tenancy

### 6.1 Isolation garantie

1. **TenantMiddleware** : Détecte l'hôpital depuis le domaine
2. **Global Scope** : Filtre automatiquement par `hospital_id`
3. **Service** : Utilise toujours `currentHospitalId()`
4. **Policy** : Vérifie l'appartenance à l'hôpital courant

### 6.2 Test d'isolation

Pour tester que l'isolation fonctionne :

1. Créer deux hôpitaux avec des domaines différents
2. Configurer des paramètres différents pour chaque hôpital
3. Accéder à chaque domaine
4. Vérifier que les paramètres affichés correspondent à l'hôpital courant

---

## 7. Exemples de code

### 7.1 Backend - Récupérer un paramètre

```php
use App\Services\HospitalSettingsService;

$logo = app(HospitalSettingsService::class)->get('app.logo', '/images/logo-default.png');
$primaryColor = app(HospitalSettingsService::class)->get('app.primary_color', '#0b5d3f');
```

### 7.2 Backend - Définir un paramètre

```php
app(HospitalSettingsService::class)->set(
    'app.primary_color',
    '#007bff',
    'string',
    'appearance',
    'Couleur principale de l\'interface',
    true // is_public
);
```

### 7.3 Frontend - Récupérer un paramètre

```javascript
import settingsService from '@/_services/settings.service';

const response = await settingsService.get('app.logo');
const logo = response.data.data.value;
```

### 7.4 Frontend - Mettre à jour un paramètre

```javascript
await settingsService.update('app.primary_color', {
  value: '#007bff',
  type: 'string',
  group: 'appearance'
});

// Appliquer immédiatement
this.$root.$emit('settings:update', { 'app.primary_color': '#007bff' });
```

### 7.5 Frontend - Upload de logo

```javascript
import settingsService from '@/_services/settings.service';

const file = document.querySelector('input[type="file"]').files[0];
const response = await settingsService.uploadLogo(file);
const logoUrl = response.data.data.url;
```

---

## 8. Dépannage

### 8.1 Logo ne s'affiche pas

- Vérifier que `php artisan storage:link` a été exécuté
- Vérifier les permissions du dossier `storage/app/public/hospitals/`
- Vérifier que l'URL retournée est accessible

### 8.2 Couleur ne s'applique pas

- Vérifier que le composant `SettingsApplier` est bien intégré dans `App.vue`
- Vérifier la console pour les erreurs JavaScript
- Vérifier que les sélecteurs CSS correspondent aux éléments de l'interface

### 8.3 Paramètres ne se chargent pas

- Vérifier que l'utilisateur est authentifié
- Vérifier que `TenantMiddleware` est actif
- Vérifier que `hospital_id` est défini dans la session

---

## 9. Améliorations futures

- [ ] Cache des paramètres côté frontend (localStorage)
- [ ] Prévisualisation en temps réel des changements
- [ ] Historique des modifications
- [ ] Export/Import des paramètres
- [ ] Templates de couleurs prédéfinis
- [ ] Support de favicon personnalisé

---

**Date de création** : 2025-01-15  
**Dernière mise à jour** : 2025-01-15  
**Auteur** : Équipe Medkey
