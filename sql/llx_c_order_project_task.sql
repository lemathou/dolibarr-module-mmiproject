CREATE TABLE IF NOT EXISTS `llx_c_order_project_task` (
  `rowid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_c` timestamp NOT NULL DEFAULT current_timestamp(),
  `tms` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `code` varchar(16) NOT NULL,
  `label` varchar(128) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `pos` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`rowid`),
  UNIQUE KEY `code` (`code`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `llx_c_order_project_task`
(`code`, `label`, `active`, `pos`)
VALUES
('REU_INT', 'Réunion d’équipe', 1, 1),
('REU_CUST', 'Réunion client', 1, 2),
('REU_OTHER', 'Réunion autre entreprise', 1, 3),
('CLEAN', 'Nettoyage / Rangement', 1, 4),
('LOAD_UNLOAD', 'Chargement / Déchargement (au dépôt)', 1, 5),
('MGMT', 'Gestion administrative', 1, 6),
('MRP', 'Formulation', 1, 7),
('OTHER', 'Aléas', 1, 8);
