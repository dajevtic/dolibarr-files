<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    htdocs/elbmultiupload/lib/elbmultiupload.lib.php
 * \ingroup elbmultiupload
 * \brief   Library files with common functions for MyModule
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function elbmultiuploadAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("elbmultiupload@elbmultiupload");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/elbmultiupload/admin/setup.php", 1);
    $head[$h][1] = $langs->trans("Settings");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/elbmultiupload/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'mymodule');

    return $head;
}

function elbmultiupload_renderAjaxPopup()
{
    global $langs;

    $loading=$langs->trans("Loading");

    print '	<div id="elb-ajax-dlg" title="" style="display:none">
				<p>
					<div id="elb-ajax-dlg-loading">
						'.$loading.'
					</div>
					<div id="elb-ajax-dlg-body"></div>
				</p>
			</div>
	';
}

function elb_empty($var)
{
    return empty($var);
}

function elb_common_action_result($res)
{
    global $langs;
    if($res) {
        setEventMessage($langs->trans("ActionSuccess"), 'mesgs');
    } else {
        setEventMessage($langs->trans("ActionError"), 'errors');
    }
}