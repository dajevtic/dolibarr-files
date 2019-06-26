<?php

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

        $oldFile = new ELbFile();
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

        $newFile = new ELbFile();
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