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
 *	\file       htdocs/elbmultiupload/class/elb.file.class.php
 *	\ingroup    elbmultiupload
 *	\brief      Contains methods for uploading/versioning files
 */
 
class ELbFile extends CommonObject
{
	public $id;
    public $name;
    public $type;
    public $md5;
    public $error;

    public $tbl_name='elb_file';
	static $_tbl_name='elb_file';

    /**
     * ELbFile constructor.
     * @param $db
     */
    function __construct($db)
    {
        $this->db = $db;
    }

    /**
     *  Load file info from database to memory
     *
     * @param   int     $id     ID of file to load
     * @return  int             <0 if KO, >0 if OK
     * @throws  Exception
     */
	function fetch($id=0)
	{
		dol_syslog(get_class($this)."::fetch id=".$id);
	
		// Check parameters
		if ( empty($id) || !is_numeric($id)) {
			$this->error='ErrorWrongParameters';
			dol_print_error(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= ' WHERE rowid = '.$id;
	
		dol_syslog(get_class($this)."::fetch sql=".$sql);
	
		$resql = $this->db->query($sql);
	
		if ($resql)	{
			if ($this->db->num_rows($resql) > 0) {

				$obj = $this->db->fetch_object($resql);
				
				// set properties
				$this->id	  		= $obj->rowid;
				$this->name 		= $obj->name;
				$this->type 		= $obj->type;
				$this->md5			= $obj->md5;
                $this->db->free($resql);
				return 1;
			}
		}

        dol_syslog(get_class($this)."::fetch ERROR sql=".$sql);
        return -1;
	}

    /**
     * Insert file info into database
     *
     * @return int        id of a row if OK or if error < 0
     * @throws Exception
     */
	function create()
    {
        $this->db->begin();

        dol_syslog(get_class($this) . "::create", LOG_DEBUG);

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . $this->tbl_name . " (";
        $sql .= "name";
        $sql .= ", type";
        $sql .= ", md5";
        $sql .= ") VALUES (";
        $sql .= "'" . $this->db->escape($this->name) . "'";
        $sql .= ", '" . $this->db->escape($this->type) . "'";
        $sql .= ", " . ($this->md5 == null ? 'null' : "'" . $this->db->escape($this->md5) . "'");
        $sql .= ")";

        dol_syslog(get_class($this) . "::create sql=" . $sql);

        $result = $this->db->query($sql);

        if ($result) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->tbl_name);
            $this->db->commit();
            dol_syslog(get_class($this) . "::create done id=" . $this->id);
            return $this->id;
        }

        $this->db->rollback();
        dol_syslog(get_class($this) . "::create Error id=" . $this->id);
        return -1;
    }

    /**
     *  Update file attached in the position
     *  @throws Exception
     */
	function actionPositionUpdateFile()
    {
		global $langs, $user;
	
		$elbfilemap = new ELbFileMapping($this->db);
			
		$id = GETPOST('id', 'int');
        $object_element = GETPOST('object_element');

		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
		$path = GETPOST('path', 'alpha');
		$description = GETPOST('description', 'alpha');
		$rev = $this->sanitizeText(GETPOST('frev'));
		$filemapid = GETPOST('filemapid', 'int');
		$tags = GETPOST('tags'.$filemapid);

		$error = 0;

        $this->db->begin();
		
		$res = $elbfilemap->fetch($filemapid);
		if (!($res > 0)) {
		    $error++;
        }
			
		if (!$error) {

            $elbfilemap->oldcopy = clone $elbfilemap;
			
			if (!empty($elbfilemap->path) && empty($path)) {
                $error++;
			}
				
			// update properties
			$elbfilemap->path = $path;
			$elbfilemap->description = $description;
			$elbfilemap->revision = $rev;
			$elbfilemap->user = $user->id;
			$elbfilemap->created_date = dol_now();
			if(is_array($tags) && count($tags)>0) {
				$elbfilemap->tags = json_encode($tags);
			}
			
			// update file map info in the db
            $res = $elbfilemap->update();
            if (!($res > 0)) {
                $error++;
            }

			if (!$error) {

				setEventMessage($langs->trans("FileSuccessfullyUpdated"), 'mesgs');

				//check and store tags
				if(count($tags)>0) {
					$all_tags = ElbFileCategory::getFileTags();
					foreach ($tags as $tag) {
						if(!in_array($tag, $all_tags)) {
							$sql="INSERT INTO ".MAIN_DB_PREFIX."categorie SET label='".$this->db->escape($tag)."', type=".ElbFileCategory::TYPE_ELB_FILE;
							ElbCommonManager::execute($sql);
						}
					}
				}

				$elbFile = new ELbFile($this->db);
				$elbFile->fetch($elbfilemap->fk_fileid);
				$ecmfile = $elbFile->getEcmfileFromUploadedFile();
				if($ecmfile === false) {
					$error++;
				} else {
					$resultecm = $ecmfile->update($user);
					if($resultecm < 0) {
						$error++;
					}
				}
			}
		}

        (!$error) ? $this->db->commit() : $this->db->rollback();

		if ($error) {
            setEventMessage($langs->trans("FileNotUpdated"), 'errors');
        }

		self::headerLocationAfterOperation($id, $object_element, $filemapid, $facid, $socid);
		exit;
	}
	
	/**
	 * Upload new version for file in the position
     * @throws Exception
	 */
	function actionPositionUploadFileNewVersion()
    {
		global $langs, $user, $conf;
	
		$id = GETPOST('id');
		$facid = GETPOST('facid');
		$socid = GETPOST('socid');
		$ufmid = GETPOST('ufmid', 'int');
		$ufmnvfile = 'ufmnvfile'.$ufmid;
		$description = GETPOST('description', 'alpha');
		$fsubrev = $this->sanitizeText(GETPOST('fsubrev'));
        $object_element = GETPOST('object_element');

        $error = 0;

        $this->db->begin();
			
        if (empty($_FILES[$ufmnvfile]["name"])) {
            $error++;
			setEventMessage($langs->trans("FileMissing"), 'errors');
		} elseif (isset($_FILES[$ufmnvfile]))	{
				
			$fileName = $_FILES[$ufmnvfile]["name"];
			$output_buffer_dir = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/';
			$output_buffer = $output_buffer_dir.$fileName;
			$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';
			
			// delete files from buffer
			if (file_exists($output_buffer_dir)) {
				dol_delete_dir_recursive($output_buffer_dir);
			}
			
			// create buffer directory
			if (!file_exists($output_buffer_dir)) {
				mkdir($output_buffer_dir, 0775, true);
			}
				
			// move file to buffer
			$res = dol_move_uploaded_file($_FILES[$ufmnvfile]["tmp_name"],$output_buffer, 1);
	
			if ($res) {
				
				// setproperties
				$this->name = $fileName;
				$ext  = (new SplFileInfo($fileName))->getExtension();
				$this->type = $ext;				
				$this->md5 = md5_file($output_buffer);
				
				$newfileid = $this->create();
	
				if ($newfileid > 0) {
						
					$elbfilemap = new ELbFileMapping($this->db);
					$elbfilemap_fetch = $elbfilemap->fetch($ufmid);

					if ($elbfilemap_fetch > 0) {
						
						// get old file map properties
						$old_fmid = $elbfilemap->id;
						$old_object_type = $elbfilemap->object_type;
						$old_object_id = $elbfilemap->object_id;
						
						// create new file map
						$fm_new = new ELbFileMapping($this->db);
						$fm_new->fk_fileid 		= $newfileid;
						$fm_new->object_type 	= $old_object_type;
						$fm_new->object_id 		= $old_object_id;
						$fm_new->created_date	= dol_now();
						$fm_new->user			= $user->id;
						$fm_new->description	= $description;
						$fm_new->revision		= $fsubrev;
						$fm_new->active			= ELbFileMapping::FILE_ACTIVE;
						
						$files_to_reindex=array();
						$files_to_reindex[]=$fm_new;
												
						$fm_new_create = $fm_new->create();
						
						if ($fm_new_create > 0) {
							
							$fetch_all_subversions = $fm_new->fetchFileVersionByParentFile($old_fmid);
							
							if (!empty($fetch_all_subversions)) {
								foreach ($fetch_all_subversions as $fmid) {
									$change_pfm = new ELbFileMapping($this->db);
									$change_pfm->fetch($fmid);
									$change_pfm->created_date = $this->db->jdate($change_pfm->created_date);
									$change_pfm->parent_file = $fm_new_create;
									$change_pfm->active = ELbFileMapping::FILE_REVISION;
									$change_pfm->update();		
									$files_to_reindex[]=$change_pfm;
								}
							}
							
							// update old file map in the db
							$elbfilemap->active	   = null;
							$elbfilemap->created_date = $this->db->jdate($elbfilemap->created_date);
							$elbfilemap->parent_file = $fm_new_create;
							$elbfilemap_update = $elbfilemap->update();
							$files_to_reindex[]=$elbfilemap;
							
							if ($elbfilemap_update > 0) {
								
								$res = dol_move($output_buffer, $output_dir.$newfileid.'.'.$ext);
								
								if ($res) {
									setEventMessage($langs->trans("FileVersionSuccessfullyCreated"), 'mesgs');
								} else {
                                    $error++;
								}
							} else {
                                $error++;
							}	
						} else {
                            $error++;
						}
					} else {
                        $error++;
					}
				} else {
                    $error++;
				}
			} else {
                $error++;
			}
		}

        (!$error) ? $this->db->commit() : $this->db->rollback();
		
		self::headerLocationAfterOperation($id, $object_element, $ufmid, $facid, $socid);
		exit;
	}

    /**
     * Remove forbidden characters
     *
     * @param   string  $text
     * @return  string
     */
	public function sanitizeText($text)
    {
		$text = trim($text);
		$text = trim($text, '.');
		$ret = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $text);
		return $ret;
	}

    /**
     * Set revision as current version
     *
     * @param   int     $fileid     ID of file map
     * @return  int
     * @throws  Exception
     */
	function activateRevision($fileid)
    {
		global $user;

		$error = 0;

        $this->db->begin();
		
		$fm_toactivate = new ELbFileMapping($this->db);
		$fm_toactivate_fetch = $fm_toactivate->fetch($fileid);
		
		if ($fm_toactivate_fetch > 0) {
		
			$fm_toactivate_pf = $fm_toactivate->parent_file;
			$fm_toactivate->parent_file = null;
			$fm_toactivate->active=ELbFileMapping::FILE_ACTIVE;
			$fm_toactivate->created_date = dol_now();
			$fm_toactivate->user = $user->id;
			$fm_toactivate_update = $fm_toactivate->update();
			
			if ($fm_toactivate_update > 0) {
				
				$fm_to_deactivate = new ELbFileMapping($this->db);
				$fm_to_deactivate_fetch = $fm_to_deactivate->fetch($fm_toactivate_pf);
				
				if ($fm_to_deactivate_fetch > 0) {
					$fm_to_deactivate->parent_file = $fileid;
					$fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
					$fm_to_deactivate->created_date	= $this->db->jdate($fm_to_deactivate->created_date);
					$fm_to_deactivate_update = $fm_to_deactivate->update();
					
					if ($fm_to_deactivate_update > 0) {
						
						$fetch_all_subversions = $fm_to_deactivate->fetchFileVersionByParentFile($fm_to_deactivate->id);
							
						if (!empty($fetch_all_subversions)) {
							foreach ($fetch_all_subversions as $fmid) {
								$change_pfm = new ELbFileMapping($this->db);
								$change_pfm->fetch($fmid);
								$change_pfm->parent_file = $fileid;
								$change_pfm->active = ELbFileMapping::FILE_REVISION;
								$change_pfm->created_date	= $this->db->jdate($change_pfm->created_date);
								$res = $change_pfm->update();
								if (!($res >0)) {
                                    $error++;
								}
							}
						}
					} else {
                        $error++;
					}
				} else {
                    $error++;
				}
			} else {
                $error++;
			}
		} else {
            $error++;
		}

        if (!$error) {
            $this->db->commit();
            return 1;
        }

        $this->db->rollback();
        return -1;
	}

    /**
     * Activate revision (set revision as current/active version)
     *
     * @throws Exception
     */
	function actionPositionActivateFile()
    {
		global $langs;
		
		$fileid = GETPOST('fileid', 'int');
		$id = GETPOST('id', 'int');
		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
        $object_element = GETPOST('object_element');
		
		$res = $this->activateRevision($fileid);
		
		if ($res > 0) {
			setEventMessage($langs->trans("RevisionIsActivated"));
		} else {
			setEventMessage($langs->trans("RevisionNotActivated"), 'errors');
		}
		
		self::headerLocationAfterOperation($id, $object_element, $fileid, $facid, $socid);
		exit;
	}
	
	/**
	 *  Removeelete file and it's revisions (if file revisions exist)
	 */
	function actionPositionRemoveFile()
    {
		global $langs;
		
		$id = GETPOST('id', 'int');
		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
        $object_element = GETPOST('object_element');
		
		$delresp = $this->deleteLinked(GETPOST('fileid', 'int'));
		if ($delresp > 0) {
			setEventMessage($langs->trans("FileWasRemoved"));
		} else {
			setEventMessage($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), 'errors');
		}

        self::headerLocationAfterOperation($id, $object_element, '', $facid, $socid);
		exit;
	}

    /**
     * Delete file map, and file in case there're no other mapping linked to the file
     *
     * @param   int     $filemapid      ID of file map
     * @return  int
     */
	function deleteLinked($filemapid)
    {
		$sql = "SELECT rowid, parent_file";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.= " WHERE rowid = ".$filemapid;
		$sql.= " OR parent_file = ".$filemapid;
		
		dol_syslog(get_class($this)."::deleteLinked sql=".$sql, LOG_DEBUG);

        $error = 0;

        $this->db->begin();
	
		$resql = $this->db->query($sql);
	
		if ($resql) {
				
			$num = $this->db->num_rows($resql);
			$i = 0;
			$fm = new ELbFileMapping($this->db);
			
			while ($i < $num) {

				$obj = $this->db->fetch_object($resql);
				
				// fetch file map
				$fm->fetch($obj->rowid);
				$fk_fileid = $fm->fk_fileid;
				
				// delete file map
				$del_fm = $fm->delete();
				
				if ($del_fm < 0) {
				    $error++;
				    break;
				}

                if(!empty($fk_fileid)) {
                    $count_lf = $fm->countLinkedFilesByFkFileID($fk_fileid);
                    if ($count_lf === 0) {
                        $res = $this->deleteFile($fk_fileid);
                        if (!$res) {
                            $error++;
                            break;
                        }
                    }
                }
				$i++;
			}
		} else {
			$error++;
		}

		if ($error) {
            $this->db->rollback();
		    return -1;
        }

        $this->db->commit();
        return 1;

	}

    /**
     * Delete file from database
     *
     * @return int
     * @throws Exception
     */
	function delete()
    {
        $this->db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " WHERE rowid = ".$this->id;
	
		dol_syslog(get_class($this)."::delete id=".$this->id, LOG_DEBUG);
	
		$resql=$this->db->query($sql);
	
		if ($resql) {
            $this->db->commit();
			return 1;
		}

        $this->db->rollback();
		return -1;
	}

    /**
     * Redirect method depending of object id parameter
     *
     * @param bool $id
     * @param bool $object_element
     * @param bool $filemapid
     * @param bool $facid
     * @param bool $socid
     * @return void
     */
	static function headerLocationAfterOperation($id=false, $object_element=false, $filemapid=false, $facid=false, $socid=false)
	{
	    if (!empty($object_element)) {
            if (!empty($id)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&object_element=' . $object_element . '#mvfid' . $filemapid);
            } elseif (!empty($facid)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?facid=' . $facid . '&object_element=' . $object_element . '#mvfid' . $filemapid);
            } elseif (!empty($socid)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?socid=' . $socid . '&object_element=' . $object_element . '#mvfid' . $filemapid);
            }
        }
		return;
	}

    /**
     * Method handles actions for object's additional files (new file(s), update, delete, new version, activate file as new revision)
     */
	static function processFileActions()
    {
		global $db;

		// update file action
		if (GETPOST('update_file')) {
			$elbfile = new ELbFile($db);
			$elbfile->actionPositionUpdateFile();
		}

		// upload file new version
		if (GETPOST("actionufnv")){
            $ufnvfile = $_FILES['ufmnvfile'.GETPOST('ufmid', 'int')];
            if (!empty($ufnvfile)) {
                $elbfile = new ELbFile($db);
                $elbfile->actionPositionUploadFileNewVersion();
            }
		}

		// delete file
		if (GETPOST('action2') == 'remove_line_file') {
			$elbfile = new ELbFile($db);
			$elbfile->actionPositionRemoveFile();
		}

		// activate file (set file as current version)
		if (GETPOST('action2') == 'activate_file') {
			$elbfile = new ELbFile($db);
			$elbfile->actionPositionActivateFile();
		}
	}

    /**
     * Delete file from the database and from the file system
     *
     * @param  int   $fileID    ID of file
     * @return bool
     */
	function deleteFile($fileID)
    {
        $elbFile = new $this($this->db);
        $elbFile->fetch($fileID);
        $res = $elbFile->delete();
        if (!($res > 0)) {
            return false;
        }

        // get full path to the file on the server
        $fullPath = $this->getFullServerPathForFile();

        // delete file from file system
        $this->removeFileFromFileSystem($fullPath);

        return true;
    }

    /**
     * Return full system path to the file
     *
     * @param $elbFile
     * @return string
     */
    public function getFullServerPathForFile()
    {
        global $conf;
        return DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$this->id.'.'.$this->type;
    }

    /**
     * Delete file from the file system
     *
     * @param $fileFullPath
     */
    public function removeFileFromFileSystem($fileFullPath)
    {
        dol_delete_file($fileFullPath);
    }

	/**
	 * Retrieves EcmFiles instance from uploaded file by calculating path based on Dolibarr settings
	 *
	 * @param ELbFile $elbFile instance of uploaded file
	 * @return bool|EcmFiles false if not found, otherwise EcmFiles instance
	 */
    public function getEcmfileFromUploadedFile()
    {
	    include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
	    $file_path = $this->getFullServerPathForFile();
	    $rel_file_path = preg_replace('/^'.preg_quote(DOL_DATA_ROOT,'/').'/', '', $file_path);
	    $rel_file_path = preg_replace('/^[\\/]/', '', $rel_file_path);
	    $ecmfile=new EcmFiles($this->db);
	    $resultecm = $ecmfile->fetch(0, '', $rel_file_path);
	    if($resultecm<=0) {
	    	return false;
	    } else {
	    	return $ecmfile;
	    }
    }

    /**
     * Get original file name from file's full path on the file system
     *
     * @param   string      $fileFullPath       File's absolute path
     * @return  string                          Original file name
     * @throws  Exception
     */
    static function getDocumentFilename($fileFullPath)
    {
        global $db;

        $filename = basename($fileFullPath);
        if ($_GET['modulepart'] == 'elbmultiupload' && !empty($_GET['fmapid'])) {
            $elb_fmap = new ELbFileMapping($db);
            $elb_fmap->fetch($_GET['fmapid']);
            $elb_fk_file = new ELbFile($db);
            $elb_fk_file->fetch($elb_fmap->fk_fileid);
            if (!empty($elb_fk_file->name)) {
                $filename = $elb_fk_file->name;
            }
        }
        return $filename;
    }

}