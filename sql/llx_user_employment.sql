
ALTER TABLE `llx_user_employment` ADD `workdaysnb` DECIMAL(4,2) NULL DEFAULT NULL AFTER `weeklyhours`, ADD `dailyhours` DECIMAL(4,2) NULL DEFAULT NULL AFTER `workdaysnb`;
