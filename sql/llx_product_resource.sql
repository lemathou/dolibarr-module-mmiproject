CREATE TABLE `llx_product_resource` (
  `rowid` int(11) NOT NULL,
  `date_c` timestamp NOT NULL DEFAULT current_timestamp(),
  `tms` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fk_product` int(11) NOT NULL,
  `pos` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `usage` varchar(128) NULL DEFAULT NULL,
  `fk_c_type_resource` int(11) NULL DEFAULT NULL,
  `fk_resource` int(11) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `llx_product_resource`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `fk_product_2` (`fk_product`,`fk_resource`),
  ADD KEY `fk_product` (`fk_product`,`pos`),
  ADD KEY `fk_resource` (`fk_resource`),
  ADD KEY `fk_c_type_resource` (`fk_c_type_resource`);

ALTER TABLE `llx_product_resource`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `llx_product_resource`
  ADD CONSTRAINT `llx_product_resource_ibfk_1` FOREIGN KEY (`fk_product`) REFERENCES `llx_product` (`rowid`),
  ADD CONSTRAINT `llx_product_resource_ibfk_2` FOREIGN KEY (`fk_c_type_resource`) REFERENCES `llx_c_type_resource` (`rowid`),
  ADD CONSTRAINT `llx_product_resource_ibfk_3` FOREIGN KEY (`fk_resource`) REFERENCES `llx_resource` (`rowid`);
COMMIT;
