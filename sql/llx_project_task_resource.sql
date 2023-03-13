CREATE TABLE `llx_projet_task_resource` (
  `rowid` int(11) NOT NULL,
  `date_c` timestamp NOT NULL DEFAULT current_timestamp(),
  `tms` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fk_projet_task` int(11) NOT NULL,
  `fk_c_type_resource` int(11) NULL DEFAULT NULL,
  `fk_resource` int(11) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `llx_projet_task_resource`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `fk_projet_task_2` (`fk_projet_task`,`fk_resource`),
  ADD KEY `fk_projet_task` (`fk_projet_task`),
  ADD KEY `fk_resource` (`fk_resource`),
  ADD KEY `fk_c_type_resource` (`fk_c_type_resource`);

ALTER TABLE `llx_projet_task_resource`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `llx_projet_task_resource`
  ADD CONSTRAINT `llx_project_task_resource_ibfk_1` FOREIGN KEY (`fk_projet_task`) REFERENCES `llx_projet_task` (`rowid`),
  ADD CONSTRAINT `llx_project_task_resource_ibfk_2` FOREIGN KEY (`fk_c_type_resource`) REFERENCES `llx_c_type_resource` (`rowid`),
  ADD CONSTRAINT `llx_project_task_resource_ibfk_3` FOREIGN KEY (`fk_resource`) REFERENCES `llx_resource` (`rowid`);
COMMIT;
