-- ============================================
-- Script SQL pour créer la base de données CORE
-- ============================================
-- 
-- Ce script crée la base de données medkey_core
-- avec le charset et collation appropriés.
--
-- Utilisation :
--   1. Ouvrez votre client MySQL (phpMyAdmin, MySQL Workbench, etc.)
--   2. Exécutez ce script
--   3. Ou copiez-collez la commande dans votre terminal MySQL
--
-- ============================================

-- Créer la base de données CORE
CREATE DATABASE IF NOT EXISTS `medkey_core` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Afficher un message de confirmation
SELECT 'Base de données medkey_core créée avec succès !' AS message;

-- Vérifier que la base existe
SHOW DATABASES LIKE 'medkey_core';
