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

                            // mv-127
                            if ($object_type=='propaldet' || $object_type=='commandedet') {
                                // arrange new file mapping for product
                                $object_type=='propaldet' ? $object_mode=false : $object_mode=true;
                                $product_id = getProductIDViaPositionID($elbfilemap->object_id, $object_mode);
                                if($product_id) {
                                    $new_fm_object = dol_clone($elbfilemap);
                                    $new_fm_object->object_type = 'product';
                                    $new_fm_object->object_id = $product_id;
                                    $res = $new_fm_object->create();
                                    $elbfilemap_to_index=$new_fm_object;
                                    if ($res > 0) {
                                        setEventMessage($langs->trans('FileAddedToTheProduct'), 'mesgs');
                                        // delete file map from propaldet or commandedet because it will be created from the product trigger
                                        $elbfilemap->delete();
                                    } else {
                                        setEventMessage($langs->trans('FileNotAddedToTheProduct'), 'warnings');
                                    }
                                }
                            }
                            // end mv-127

                            // mv-228
                            if ($object_type=='elb_stock_mouvement') {
                                // arrange new file mapping for linked supplier order
                                $fetch_movement = new ElbMouvementStock($db);
                                $res_fetch_mov = $fetch_movement->fetch($elbfilemap->object_id);
                                if ($res_fetch_mov > 0 &&
                                    $fetch_movement->origintype == 'supplier_delivery' &&
                                    $fetch_movement->fk_origin > 0)
                                {
                                    $supp_order_id = ElbCommandeSupplierDelivery::returnSupplierOrderIdForDeliveryId($fetch_movement->fk_origin);
                                    if ($supp_order_id > 0) {
                                        $new_fm_object = dol_clone($elbfilemap);
                                        $new_fm_object->object_type = 'order_supplier';
                                        $new_fm_object->object_id = $supp_order_id;
                                        $new_fm_object->clone_of_fmap_id = $fmid;
                                        $res = $new_fm_object->create();
                                        if ($res > 0) {
                                            setEventMessage($langs->trans('FileAddedToLinkedSupplierOrder'), 'mesgs');
                                        } else {
                                            setEventMessage($langs->trans('FileNotAddedToLinkedSupplierOrder'), 'warnings');
                                        }
                                    }
                                }
                            }
                            // end mv-228


                            $db->commit();

                            if ($object_type=='product') {
                                $elbSoap = new ElbSoapClient();

                                $fileid=$elbfile->id;
                                $productfile['name']=$elbfile->name;
                                $productfile['type']=$elbfile->type;
                                $productfile['md5']=$elbfile->md5;
                                $original_file="uploadedfiles/".$fileid.".".$elbfile->type;
                                $original_file = str_replace("../","/", $original_file);
                                $refname=basename(dirname($original_file)."/");
                                $check_access = dol_check_secure_access_document('elb',$original_file,$conf->entity,$refname);
                                $original_file = $check_access['original_file'];
                                //$productfile['file']=base64_encode(file_get_contents($original_file));

                                $product_id=$elbfilemap->object_id;
                                $product=new Product($db);
                                $product->fetch($product_id);
                                $productfile['product_ref']=$product->ref;

                                $protocol=get_server_protocol();
                                $download_path=$protocol."://".$_SERVER['SERVER_NAME'].DOL_URL_ROOT;
                                $download_path.="/document.php?modulepart=elb";
                                $download_path.="&file=".urlencode("uploadedfiles/".$fileid.".".$elbfile->type);
                                $download_path.="&fmapid={$elbfilemap->id}";
                                //$download_path.="&XDEBUG_SESSION_START=ECLIPSE_DBGP";

                                $productfile['download_path']=$download_path;
                                $elbSoap->call('productFileSync',array('productfile'=>$productfile));
                            }

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
// Linked documents (Strato server)
else if (!empty(GETPOST("linkit"))) {
    $elbfile->linkFile();
}
