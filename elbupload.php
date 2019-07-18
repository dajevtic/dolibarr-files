<?php

//use ELBClass\solr\ElbSolrUtil;

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/lib/elbmultiupload.lib.php';

require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.common.manager.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

global $conf, $db, $langs, $user;

$output_buffer = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/';
$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';

if (file_exists($output_buffer)) {
    dol_delete_dir_recursive($output_buffer);
}

if (!file_exists($output_dir)) {
    mkdir($output_dir, 0775, true);
}

if (!file_exists($output_buffer)) {
    mkdir($output_buffer, 0775, true);
}

$elbfile = new ELbFile($db);
$elbfilemap = new ELbFileMapping($db);

if(isset($_FILES["elb_file"]))
{
    $ret = array();

    $langs->load('common@elb');

    $error =$_FILES["elb_file"]["error"];

    if(!is_array($_FILES["elb_file"]['name'])) //single file
    {
        $fileName = $_FILES["elb_file"]["name"];
        //$file_search = $elbfile->searchFileByNameInSystemDir($fileName, $stored_files);
        $file_search = false;

        if(!$file_search) {

            // move uploaded file to buffer
            $fileName = str_replace("..", ".", $fileName);
            $res = dol_move_uploaded_file($_FILES["elb_file"]["tmp_name"], $output_buffer.$fileName, true);

            if ($res==1) {

                // set file properties
                $elbfile->name = $fileName;
                $ext  = (new SplFileInfo($fileName))->getExtension();
                $elbfile->type = $ext;
                $elbfile->md5 =  md5_file($output_buffer.$fileName);

                $db->begin();

                // insert file in db
                $fileid = $elbfile->create();

                if ($fileid>0) {

                    // set file mapping properties
                    $object_type = $_POST['object_type'];
                    $elbfilemap->fk_fileid=$fileid;
                    $elbfilemap->object_type=$object_type;
                    $elbfilemap->object_id=$_POST['object_id'];
                    $elbfilemap->created_date=dol_now();
                    $elbfilemap->user=$user->id;
                    $elbfilemap->active=ELbFileMapping::FILE_ACTIVE;

                    $fmid = $elbfilemap->create();

                    $elbfilemap_to_index=$elbfilemap;

                    if ($fmid > 0) {
                        $move_res = dol_move($output_buffer.$fileName, $output_dir.$fileid.'.'.$ext);
                        if ($move_res==true) {

                            $db->commit();

                            //send file to solr index
                            if(!empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
                                $res = ElbSolrUtil::add_to_search_index($elbfile, $elbfilemap_to_index);
                                if(!$res) {
                                    setEventMessage("Error uploading document to Solr: ".ElbSolrUtil::$last_error);
                                } else {
                                    setEventMessage("File succesfully added to Solr", 'mesgs');
                                }
                            }

                            setEventMessage($fileName.' - '.$langs->trans("FileSuccessfullyUploaded"), 'mesgs');
                        } else {
                            $db->rollback();
                            setEventMessage($fileName.' -1 '.$langs->trans("FileNotSuccessfullyUploaded"), 'errors');
                        }
                    } else {
                        $db->rollback();
                        setEventMessage($fileName.' -2 '.$langs->trans("FileNotSuccessfullyUploaded"), 'errors');
                    }

                } else {
                    $db->rollback();
                    setEventMessage($fileName.' -3 '.$langs->trans("FileNotSuccessfullyUploaded"), 'errors');
                }
            }
        }
        else
        {
            setEventMessage($fileName.' - '.$langs->trans("FileAlreadyExists"), 'errors');
        }
    }
    echo json_encode($ret);
}
