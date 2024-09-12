
ALTER TABLE `llx_user_employment`
ADD COLUMN IF NOT EXISTS `workdaysnb` DECIMAL(4,2) NULL DEFAULT NULL AFTER `weeklyhours`,
ADD COLUMN IF NOT EXISTS `dailyhours` DECIMAL(4,2) NULL DEFAULT NULL AFTER `workdaysnb`,
ADD COLUMN IF NOT EXISTS `workdays` SET('1','2','3','4','5','6') NULL DEFAULT NULL AFTER `workdaysnb`,
ADD COLUMN IF NOT EXISTS `workdays2` SET('1','2','3','4','5','6') NULL DEFAULT NULL AFTER `workdays`;
