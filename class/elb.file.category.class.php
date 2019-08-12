<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/elbmultiupload/class/elb.file.category.class.php
 *	\ingroup    elbmultiupload
 *	\brief      Manager for categories of files
 */

class ElbFileCategory
{
    /**
     * Method returns all distinct categories of elb file type
     *
     * @return array
     */
    public static function getFileTags()
    {
        $sql="SELECT distinct(c.label) from ".MAIN_DB_PREFIX."categorie c WHERE c.type=".self::getFileCategoryID()." ORDER BY c.label";
        $rows = ElbCommonManager::queryList($sql);
        $tags=array();
        foreach($rows as $row) {
            $tags[$row->label] = $row->label;
        }
        return $tags;
    }

    /**
     * Return category ID for elb additional files
     *
     * @return mixed
     */
    static function getFileCategoryID()
    {
        global $conf;
        return $conf->global->ELB_FILE_CATEGORY_TYPE;
    }

}