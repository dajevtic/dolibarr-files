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
    const GROUP_FILES_PARAM = 'file-list-display';

    const GROUP_FILES_DEFAULT = '';
    const GROUP_FILES_BY_REV = 'by_rev';
    const GROUP_FILES_BY_TAG = 'by_tag';

    static function isSetGroupFiles()
    {
        return isset($_SESSION[self::GROUP_FILES_PARAM]);
    }

    static function setGroupFilesMethod($setGroupingMethod=null)
    {
        $_SESSION[self::GROUP_FILES_PARAM] = $setGroupingMethod;
    }

    static function getGroupFilesMethod()
    {
        return $_SESSION[self::GROUP_FILES_PARAM];
    }

    static function unsetGroupFilesMethod()
    {
        unset($_SESSION[self::GROUP_FILES_PARAM]);
    }

}