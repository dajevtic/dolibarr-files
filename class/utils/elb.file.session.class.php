<?php
//use ELBClass\solr\ElbSolrUtil;

/* Copyright (C) 2019-... LiveMediaGroup - Milos Petkovic <milos.petkovic@livemediagroup.de>
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
 *	\file       htdocs/elbmultiupload/class/utils/elb.file.session.class.php
 *	\ingroup    elbmultiupload
 *	\brief      Session for file uplaod
 */

class ElbFileSession
{

    static function isSetGroupFiles()
    {
        return isset($_SESSION[ElbFileGrouping::GROUP_FILES_PARAM]);
    }

    static function setGroupFilesMethod($setGroupingMethod=null)
    {
        $_SESSION[ElbFileGrouping::GROUP_FILES_PARAM] = $setGroupingMethod;
    }

    static function getGroupFilesMethod()
    {
        return $_SESSION[ElbFileGrouping::GROUP_FILES_PARAM];
    }

    static function unsetGroupFilesMethod()
    {
        unset($_SESSION[ElbFileGrouping::GROUP_FILES_PARAM]);
    }

}