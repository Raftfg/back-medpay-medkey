# ⚡ Quick Start - Module DME

## 🎯 Démarrage Rapide (5 minutes)

### Étape 1 : Validation (1 min)

```bash
cd back-medpay
php artisan tenant:schema-validate
```

**✅ Si tout est vert :** Passer à l'Étape 3  
**⚠️ Si des problèmes :** Passer à l'Étape 2

---

### Étape 2 : Synchronisation (2 min)

```bash
# D'abord en mode simulation
php artisan tenant:schema-sync --dry-run

# Si tout est OK, appliquer
php artisan tenant:schema-sync --force
```

---

### Étape 3 : Test (2 min)

1. **Ouvrir le navigateur :**
   ```
   http://hopital1.localhost:8080/patients/dme/{patient_uuid}
   ```

2. **Tester rapidement :**
   - ✅ Vérifier que tous les onglets s'affichent
   - ✅ Ajouter un antécédent
   - ✅ Ajouter une allergie
   - ✅ Vérifier le résumé IA

**✅ Si tout fonctionne :** Le module est prêt ! 🎉

---

## 🆘 En Cas de Problème

### Problème : "Table does not exist"

```bash
php artisan tenant:schema-sync --table={table_name} --force
```

### Problème : "Column does not exist"

```bash
php artisan tenant:schema-sync --table={table_name} --force
```

### Problème : Erreur de migration

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Réessayer la migration
php artisan tenant:migrate-all --path=Modules/Movment/Database/Migrations/{migration_file} --force
```

---

## 📞 Support

- 📖 Documentation complète : `DME_IMPLEMENTATION_COMPLETE.md`
- 📋 Guide d'exécution : `GUIDE_EXECUTION_DME.md`
- 🔍 Logs : `storage/logs/laravel.log`
