CREATE TABLE `llx_user_pay` (
  `rowid` int(11) NOT NULL,
  `tms` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `datec` timestamp NOT NULL DEFAULT current_timestamp(),
  `entity` int(11) NOT NULL DEFAULT 1,
  `fk_user` int(11) NOT NULL,
  `date` date NOT NULL,
  `paid_hr` decimal(6,2) DEFAULT NULL,
  `paid_hrsup` decimal(6,2) DEFAULT NULL,
  `paid_amount` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `llx_user_pay`
  ADD PRIMARY KEY (`rowid`),
  ADD KEY `fk_user` (`fk_user`);

ALTER TABLE `llx_user_pay`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `llx_user_pay`
  ADD CONSTRAINT `lllx_user_pay_ibfk_1` FOREIGN KEY (`fk_user`) REFERENCES `lllx_user` (`rowid`);

ALTER TABLE `llx_user_pay` ADD `decal_hsup_conge` DECIMAL(6,2) NULL DEFAULT NULL AFTER `paid_amount`, ADD `recup_hsup_conge` DECIMAL(6,2) NULL DEFAULT NULL AFTER `decal_hsup_conge`;
