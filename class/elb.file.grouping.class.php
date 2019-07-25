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

class ElbFileGrouping
{
    const GROUP_FILES_PARAM     = 'file-list-display';
    const GROUP_FILES_DEFAULT   = 'none';
    const GROUP_FILES_BY_REV    = 'by_rev';
    const GROUP_FILES_BY_TAG    = 'by_tag';

    /**
     * Get available files grouping methods
     *
     * @return array
     */
    static function returnAvailableGroupingMethods()
    {
        global $langs, $conf;
        $ret = array( self::GROUP_FILES_DEFAULT => '',
                      self::GROUP_FILES_BY_REV  => $langs->trans('Revision'));
        if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
            $ret[self::GROUP_FILES_BY_TAG] = $langs->trans('Tag');
        }
        return $ret;
    }
}