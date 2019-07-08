-- Copyright (C) 2019-... LiveMediaGroup - Milos Petkovic <milos.petkovic@livemediagroup.de>---
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


-- BEGIN MODULEBUILDER INDEXES
-- ALTER TABLE llx_mymodule_myobject ADD INDEX idx_fieldobject (fieldobject);
-- END MODULEBUILDER INDEXES

-- ALTER TABLE llx_mymodule_myobject ADD UNIQUE INDEX uk_mymodule_myobject_fieldxy(fieldx, fieldy);

-- ALTER TABLE llx_mymodule_myobject ADD CONSTRAINT llx_mymodule_myobject_fk_field FOREIGN KEY (fk_field) REFERENCES llx_mymodule_myotherobject(rowid);

ALTER TABLE llx_elb_file_mapping ADD UNIQUE INDEX `uk_fk_fileid_object_type_object_id_clone_of_fmap_id_parent_file` (`fk_fileid`, `object_type`, `object_id`, `clone_of_fmap_id`, `parent_file`);

ALTER TABLE llx_categorie_elb_file ADD INDEX `idx_categorie_elb_file_fk_categorie` (`fk_categorie`);

ALTER TABLE llx_categorie_elb_file ADD INDEX `idx_categorie_elb_file_fk_elb_file` (`fk_elb_file`);