-- Mise à niveau du schéma : catalogue de formations, inscriptions et
-- produits composés (nomenclature).
--
-- Équivaut aux migrations 2026_06_26_224325 → 2026_07_05_184746.
-- Rejouable sans risque : chaque instruction est protégée par IF NOT EXISTS.
--
-- Testé sur MariaDB 10.4. Sur MySQL 8, « ADD COLUMN IF NOT EXISTS » n'existe
-- pas : retirer « IF NOT EXISTS » de l'ALTER TABLE products et ne le lancer
-- qu'une seule fois.
--
-- Import phpMyAdmin : sélectionner la base, onglet « Importer », choisir ce
-- fichier. Ne crée aucune donnée, uniquement la structure.

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Catalogue des formations géré par l'administrateur.
CREATE TABLE IF NOT EXISTS `formations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(12,2) NOT NULL DEFAULT 0.00,
  `duree` varchar(100) DEFAULT NULL,
  `session` varchar(120) DEFAULT NULL,
  `mode` enum('presentiel','en_ligne') NOT NULL DEFAULT 'presentiel',
  `image` varchar(255) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `statut` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Inscriptions du public, rattachées à une formation du catalogue.
--    formation_id reste nullable : une inscription survit à la suppression
--    de sa formation (ON DELETE SET NULL).
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `formation_id` bigint(20) unsigned DEFAULT NULL,
  `nom` varchar(120) NOT NULL,
  `prenom` varchar(120) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `type` enum('acceleree','normale') NOT NULL,
  `statut` enum('en_attente','confirme','annule') NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inscriptions_formation_id_foreign` (`formation_id`),
  CONSTRAINT `inscriptions_formation_id_foreign`
    FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Un produit est simple, ou composé d'autres produits.
ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `type` enum('simple','compose') NOT NULL DEFAULT 'simple' AFTER `price`;

-- 4. Nomenclature : lignes (produit composé, produit simple, quantité).
--    La clé unique empêche de déclarer deux fois le même composant.
CREATE TABLE IF NOT EXISTS `product_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_composite_product` bigint(20) unsigned NOT NULL,
  `id_component_product` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pc_composite_component_unique` (`id_composite_product`,`id_component_product`),
  KEY `product_components_id_component_product_foreign` (`id_component_product`),
  CONSTRAINT `product_components_id_composite_product_foreign`
    FOREIGN KEY (`id_composite_product`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_components_id_component_product_foreign`
    FOREIGN KEY (`id_component_product`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
