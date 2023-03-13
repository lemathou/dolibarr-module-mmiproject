ALTER TABLE `llx_c_type_resource` ADD `fk_parent` INT NULL DEFAULT NULL AFTER `active`;
ALTER TABLE `llx_c_type_resource` ADD FOREIGN KEY (`fk_parent`) REFERENCES `llx_c_type_resource`(`rowid`) ON DELETE RESTRICT ON UPDATE RESTRICT;
