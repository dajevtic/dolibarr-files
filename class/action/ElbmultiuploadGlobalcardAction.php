<?php

/* Copyright (C) 2019-... LiveMediaGroup - Milos Petkovic <milos.petkovic@livemediagroup.de>
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

class ElbmultiuploadGlobalcardAction
{
    static function rename_file($object, $params)
    {
        if(!defined("DO_AJAX_ACTION")) exit;

        global $db;

        // start transaction
        $db->begin();

        // initiate ajax action
        $elbAjax = new ElbAjax();
        $elbAjax->start();

        // get params sent via ajax
        $file_id = $elbAjax->getParam('file_id');
        $file_ext = $elbAjax->getParam('file_ext');
        $new_file_name = $elbAjax->getParam('new_file_name');

        $error = 0;

        $oldFile = new ELbFile($db);
        $oldFile->fetch($file_id);

        if (strlen($new_file_name) > 0) {
            $result = ElbCommonManager::updateField('elb_file', 'name', $new_file_name.$file_ext, $file_id);
            if (!$result) {
                $error++;
            }
        } else {
            $error++;
        }

        // add js code (destroy ajax window on submit comment)
        $elbAjax->addCode(' $("#elb-ajax-dlg").dialog("destroy"); ');
        $elbAjax->addCode('setTimeout(function(){location.reload()},10);');

        $newFile = new ELbFile($db);
        $newFile->fetch($file_id);
        if($oldFile != $newFile) {
            $fm = new ELbFileMapping($db);
            $fm->fetchByFileID($file_id);
        }

        // close transaction
        (!$error) ? $db->commit() : $db->rollback();

        // set event result message
        elb_common_action_result(!$error);

        // show event result message
        $elbAjax->showMessages();

        // execute js code
        $ret = $elbAjax->getResponse();

        return $ret;
    }
}