-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.

CREATE TABLE IF NOT EXISTS `llx_elb_file` (
    `rowid` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(256) NOT NULL COLLATE 'utf8_general_ci',
    `type` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `md5` VARCHAR(60) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    PRIMARY KEY (`rowid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `llx_elb_file_mapping` (
    `rowid` INT(11) NOT NULL AUTO_INCREMENT,
    `fk_fileid` INT(11) NULL DEFAULT NULL,
    `object_type` VARCHAR(30) NOT NULL COLLATE 'utf8_general_ci',
    `object_id` INT(11) NOT NULL,
    `created_date` DATETIME NULL DEFAULT NULL,
    `user` INT(8) NOT NULL,
    `description` TEXT NULL COLLATE 'utf8_general_ci',
    `path` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `parent_file` INT(11) NULL DEFAULT NULL,
    `revision` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `active` TINYINT(4) NOT NULL DEFAULT '1',
    `clone_of_fmap_id` INT(11) NULL DEFAULT NULL,
    `tags` VARCHAR(1000) NULL DEFAULT NULL,
    PRIMARY KEY (`rowid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `llx_categorie_elb_file` (
    `fk_categorie` INT(11) NOT NULL,
    `fk_elb_file` INT(11) NOT NULL,
    `import_key` VARCHAR(14) NULL DEFAULT NULL,
    PRIMARY KEY (`fk_categorie`, `fk_elb_file`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;