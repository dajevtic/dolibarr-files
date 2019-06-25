<?php
//use ELBClass\solr\ElbSolrUtil;

/* Copyright (C) 2014 ELB Solutions
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
 
class ELbFileMapping extends CommonObject {
	
	const FILE_REVISION = 0;
	const FILE_ACTIVE = 1;
	
	const ACTION_NONE 		 = 0;
	const ACTION_CREATE  	 = 1;
	const ACTION_READ 		 = 2;
	const ACTION_UPDATE 	 = 3;
	const ACTION_DELETE 	 = 4;
	const ACTION_NEW_VERSION = 5;
	const ACTION_ACTIVATE	 = 6;
	
	var $id;
	var $fk_fileid;
	var $object_type;
	var $object_id;
	var $created_date;
	var $user;
	var $description;
	var $path;
	var $parent_file;
	var $revision;
	var $active;
	var $clone_of_fmap_id;
	// holds and action type
	var $action;
	var $tags;
	
	var $tbl_name='elb_file_mapping';
	static $_tbl_name='elb_file_mapping';
	
	
	function __construct($db)
	{
		$this->db = $db;
		return 1;
	}

	
	function fetch($id='')
	{
		global $langs, $conf, $db;
	
		dol_syslog(get_class($this)."::fetch id=".$id);
	
		// Check parameters
		if ( empty($id) || !is_numeric($id))
		{
			$this->error='ErrorWrongParameters';
			dol_print_error(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= ' WHERE rowid = '.$id;
	
		dol_syslog(get_class($this)."::fetch sql=".$sql);
	
		$resql = $db->query($sql);
	
		if ( $resql )
		{
			if ($db->num_rows($resql) > 0)
			{
				$obj = $db->fetch_object($resql);
	
				// set properties
				$this->id	  			= $obj->rowid;
				$this->fk_fileid 		= $obj->fk_fileid;
				$this->object_type 		= $obj->object_type;
				$this->object_id 		= $obj->object_id;
				$this->created_date		= $obj->created_date;
				$this->user				= $obj->user;
				$this->description		= $obj->description;
				$this->path				= $obj->path;
				$this->parent_file		= $obj->parent_file;
				$this->revision			= $obj->revision;
				$this->active			= $obj->active;
				$this->clone_of_fmap_id	= $obj->clone_of_fmap_id;
				$this->action			= self::ACTION_READ;
				$this->tags				= $obj->tags;
				
				$db->free($resql);
				return 1;
			}
		}
		else
		{
			dol_print_error($db);
			return -1;
		}
	}

	function fetchByFileID($fileid='')
	{
		global $langs, $conf, $db;
	
		dol_syslog(get_class($this)."::fetchByFileID id=".$fileid);
	
		// Check parameters
		if ( empty($fileid) || !is_numeric($fileid))
		{	
			return -1;
		}
	
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= ' WHERE fk_fileid = '.$fileid;
	
		dol_syslog(get_class($this)."::fetchByFileID sql=".$sql);
	
		$resql = $db->query($sql);
	
		if ( $resql )
		{
			if ($db->num_rows($resql) > 0)
			{
				$obj = $db->fetch_object($resql);
	
				// set properties
				$this->id	  		= $obj->rowid;
				$this->fk_fileid 	= $obj->fk_fileid;
				$this->object_type 	= $obj->object_type;
				$this->object_id 	= $obj->object_id;
				$this->created_date	= $obj->created_date;
				$this->user			= $obj->user;
				$this->description	= $obj->description;
				$this->path			= $obj->path;
				$this->parent_file	= $obj->parent_file;
				$this->revision		= $obj->revision;
				$this->active		= $obj->active;
				$this->clone_of_fmap_id	= $obj->clone_of_fmap_id;
				$db->free($resql);
				return 1;
			}
		}
		else
		{
			dol_print_error($db);
			return -1;
		}
	}
	
	function create($activate_trigger=true) 
	{
		global $user, $langs, $conf, $db;
		
		$this->db->begin();
		
		$error = 0;
		dol_syslog(get_class($this)."::create", LOG_DEBUG);
	
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " (";
		$sql.= "fk_fileid";
		$sql.= ", object_type";
		$sql.= ", object_id";
		$sql.= ", created_date";
		$sql.= ", user";
		$sql.= ", description";
		$sql.= ", path";
		$sql.= ", parent_file";
		$sql.= ", revision";
		$sql.= ", active";
		$sql.= ", clone_of_fmap_id";
		$sql.= ", tags";
		$sql.= ") VALUES (";
		$sql.= "".($this->fk_fileid==null ? 'null' : $this->db->escape($this->fk_fileid));
		$sql.= ", '".$this->db->escape($this->object_type)."'";
		$sql.= ", ".$this->db->escape($this->object_id);
		$sql.= ", ".($this->created_date==null ? 'null' : "'".$this->db->idate($this->created_date)."'");
		$sql.= ", ".($this->user==null ? 'null' : "'".$this->db->escape($this->user)."'");
		$sql.= ", ".($this->description==null ? 'null' : "'".$this->db->escape($this->description)."'");
		$sql.= ", ".($this->path==null ? 'null' : "'".$this->db->escape($this->path)."'");
		$sql.= ", ".($this->parent_file==null ? 'null' : $this->db->escape($this->parent_file));
		$sql.= ", ".($this->revision==null ? 'null' : "'".$this->db->escape($this->revision)."'");
		$sql.= ", ".(empty($this->active) ? 0 : 1);
		$sql.= ", ".($this->clone_of_fmap_id==null ? 'null' : "'".$this->db->escape($this->clone_of_fmap_id)."'");
		$sql.= ", ".($this->tags==null ? 'null' : "'".$this->db->escape($this->tags)."'");
		$sql.= ")";
	
		dol_syslog(get_class($this)."::create sql=".$sql);
	
		$result = $this->db->query($sql);
			
		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->tbl_name);

			// Trigger
			if ($activate_trigger) {
				$this->action = self::ACTION_CREATE;
				$result = $this->call_trigger('ELB_FILE_ACTION', $user);
			}
						
			$this->db->commit();
			dol_syslog(get_class($this)."::create done id=".$this->id);
			return $this->id;
		}
		else
		{
			$this->db->rollback();
			dol_syslog(get_class($this)."::create Error id=".$this->id);
			return -1;
		}
	}
	
	function update($activate_trigger=true) {
	
		global $db, $user;
	
		$db->begin();

		if ($activate_trigger) {
			$clone = new ELbFileMapping($db);
			$clone->fetch($this->id);
			$this->oldcopy = $clone;
		}
	
		$sql="UPDATE ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.=" SET fk_fileid = ".($this->fk_fileid==null ? 'null' : $db->escape($this->fk_fileid)).", ";
		$sql.=" object_type = '".$db->escape($this->object_type)."', ";
		$sql.=" object_id = ".$db->escape($this->object_id).", ";
		$sql.=" created_date = ".($this->created_date==null ? 'null' : "'".$db->idate($this->created_date)."'").", ";
		$sql.=" user = ".($this->user==null ? 'null' : $db->escape($this->user)).", ";
		$sql.=" description = ".($this->description==null ? 'null' : "'".$db->escape($this->description)."'").", ";
		$sql.=" path = ".($this->path==null ? 'null' : "'".$db->escape($this->path)."'").", ";
		$sql.=" parent_file = ".($this->parent_file==null ? 'null' : $db->escape($this->parent_file)).", ";
		$sql.=" revision = ".($this->revision==null ? 'null' : "'".$db->escape($this->revision)."'").", ";
		$sql.=" active = ".(empty($this->active) ? self::FILE_REVISION : self::FILE_ACTIVE).", ";
		$sql.=" clone_of_fmap_id = ".($this->clone_of_fmap_id==null ? 'null' : "'".$db->escape($this->clone_of_fmap_id)."'").", ";
		$sql.=" tags = ".($this->tags==null ? 'null' : "'".$db->escape($this->tags)."'");
		$sql.=" WHERE rowid=".$db->escape($this->id);
	
		dol_syslog(get_class($this)."::update id=".$this->id, LOG_DEBUG);
	
		$resql=$db->query($sql);
	
		if($resql) {
			
			//if (!empty($this->action)) {
			if (in_array($this->object_type, self::arrayWithObjectTypesForActivatingTrigger()) && $activate_trigger) {
				$this->action = self::ACTION_UPDATE;
			}			
							
			// Trigger
			if ($activate_trigger) {
				$result = $this->call_trigger('ELB_FILE_ACTION', $user);
			}
			
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}
	}

	function delete($activate_trigger=true) {
	
		global $db, $user, $conf;
	
		$db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " WHERE rowid = ".$this->id;
	
		dol_syslog(get_class($this)."::delete id=".$this->id, LOG_DEBUG);
	
		$resql=$db->query($sql);
	
		if($resql) {
			
			//if (!empty($this->action)) {
			if (in_array($this->object_type, self::arrayWithObjectTypesForActivatingTrigger()) && $this->action !== self::ACTION_NONE && $activate_trigger) {
				$this->action = self::ACTION_DELETE;
			}			
			
			// Trigger
			if ($activate_trigger) {
				$result = $this->call_trigger('ELB_FILE_ACTION', $user);
			}
			
			//delete fie in solr
			if(!empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
				$file = new ELbFile();
				$file->fetch($this->fk_fileid);
				$resql = ElbSolrUtil::remove_from_index($file->md5."_".$this->id);
			}
			
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}
	}
	
	static function arrayWithObjectTypesForActivatingTrigger() {
		return array('product', 'elb_stock_mouvement', 'order_supplier','tech_procedure');
	}
	
	function countLinkedFilesByFkFileID($fk_fileid) {
		
		global $db;
		
		$sql="SELECT COUNT(*) AS cnt ";
		$sql.=" FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.=" WHERE fk_fileid=".$db->escape($fk_fileid);
			
		dol_syslog(get_class($this).'::countLinkedFilesByFkFileID sql='.$sql, LOG_DEBUG);
			
		if ($resql=$db->query($sql))
		{
			$obj = $this->db->fetch_object($resql);
			$count=$obj->cnt;
			if($count>0) {
				return $count;
			} else {
				return 0;
			}
		}
		else
		{
			dol_syslog(get_class($this).'::countLinkedFilesByFkFileID ERROR sql='.$sql, LOG_DEBUG);
			return 0;
		}
	}
	
	static function countLinkedFilesByObjectType($object_type, $object_id, $only_attached=true) {
	
		global $db;
	
		$sql ="SELECT COUNT(*) AS cnt ";
		$sql.=" FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.=" WHERE object_type='".$object_type."'";
		$sql.=" AND object_id=".$object_id;
		if ($only_attached) {
			$sql.=" AND path IS NULL";
		}
			
		dol_syslog(get_called_class().'::countLinkedFilesByObjectType sql='.$sql, LOG_DEBUG);
			
		if ($resql=$db->query($sql))
		{
			$obj = $db->fetch_object($resql);
			$count=$obj->cnt;
			if($count>0) {
				return $count;
			} else {
				return;
			}
		}
		else
		{
			dol_syslog(get_called_class().'::countLinkedFilesByObjectType ERROR sql='.$sql, LOG_DEBUG);
			return;
		}
	}

	/**
	 * 
	 * @param string 	$object_type
	 * @param int 		$object_id
	 * @param int 		$map_type (0 -> all, 1 - only active, 2 - only revisions)
	 * @return array of objects
	 */
	static function sqlSelectFilesAndFileMappings($object_type, $object_id, $map_type=0) {
		global $db;
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.clone_of_fmap_id";
		$sql.= " FROM ".MAIN_DB_PREFIX.self::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.ELbFile::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.object_type = '".$db->escape($object_type)."'";
		$sql.= " AND fm.object_id = ".$db->escape($object_id);
		if ($map_type==1) {
			$sql.= " AND fm.active=1";
		} elseif ($map_type==2) {
			$sql.= " AND fm.active=0";
		}
		return $sql;
	}

	static function getFilesAndFileMappingsForObjectList($object_type, $object_ids, $map_type=0) {
		global $db;
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.clone_of_fmap_id";
		$sql.= " FROM ".MAIN_DB_PREFIX.self::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.ELbFile::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.object_type = '".$db->escape($object_type)."'";
		$sql.= " AND fm.object_id in (".implode(",", $object_ids).")";
		if ($map_type==1) {
			$sql.= " AND fm.active=1";
		} elseif ($map_type==2) {
			$sql.= " AND fm.active=0";
		}
		return ElbCommonManager::queryList($sql);
	}

	static function getAttachedFilesSize($object_type, $object_id)
    {
		global $db, $conf;
		
		$sql = self::sqlSelectFilesAndFileMappings($object_type, $object_id);
		
		dol_syslog(get_called_class()."::getAttachedFilesSize sql=".$sql);
		
		$resql = $db->query($sql);
		
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			$totalsize = 0;
			
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				$fileid = $obj->frowid;
				$filetype = $obj->ftype;
				$filepath = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$fileid.'.'.$filetype;
				
				$size = dol_filesize($filepath);
				$totalsize += $size;
				$i++;
			}
			return $totalsize;
		}
		return;
	}
	
	static function fetchLinkedFileMapsForObject($object_type, $object_id) {
	
		global $db;
		
		$fetch = false;
	
		$sql = "SELECT fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.clone_of_fmap_id ";
		$sql.= " FROM ".MAIN_DB_PREFIX.self::$_tbl_name." as fm";
		$sql.= " WHERE fm.object_type = '".$db->escape($object_type)."'";
		$sql.= " AND fm.object_id = ".$db->escape($object_id);
		$sql.= " ORDER BY fm.rowid ASC";
			
		dol_syslog(get_called_class().'::fetchLinkedFileMapsForObject sql='.$sql, LOG_DEBUG);
			
		if ($resql=$db->query($sql))
		{	
			$i = 0;
			$num = $db->num_rows($resql);
			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				$fetch[$i] = $obj;
				$i++;
			}
		}
		else
		{
			dol_syslog(get_called_class().'::fetchLinkedFileMapsForObject ERROR sql='.$sql, LOG_DEBUG);
		}
		return $fetch;
	}
	
	static function cloneLinkedFileMaps($old_object_type, $old_object_id, $new_object_type, $new_object_id) {
		
		global $conf, $db, $user;
		
		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.class.php';
		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file_mapping.class.php';
		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.common.manager.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		
		$db->begin();
		
		$count_lf = self::countLinkedFilesByObjectType($old_object_type, $old_object_id, false);
		
		if ($count_lf > 0) {
			$fm_new = $fm_rev_new = new ELbFileMapping($db);
			$fm_arr = $fm_arr2 = self::fetchLinkedFileMapsForObject($old_object_type, $old_object_id);
			$active_ids=array();
			$error = 0;
			foreach ($fm_arr as $fm_obj) {
				// first clone active files from old object
				if ($fm_obj->fmactive==self::FILE_ACTIVE) {
					$clone_fields = array( 'fk_fileid', 'object_type', 'object_id', 'created_date', 'user', 'description',
										   'path', 'parent_file', 'revision', 'active', 'clone_of_fmap_id' );
					$clone_fm_id = ElbCommonManager::cloneTableRow(self::$_tbl_name, $clone_fields, $fm_obj->fmrowid);
					if ($clone_fm_id > 0) {
							
						$fm_new->fetch($clone_fm_id);
						$fm_new->object_type=$new_object_type;
						$fm_new->object_id=$new_object_id;
						$fm_new->user=$fm_obj->fmuser;
						$fm_new->created_date=dol_now();
						if ($old_object_type == 'product') {
							$fm_new->clone_of_fmap_id=$fm_obj->fmrowid;
						} else {
							$fm_new->clone_of_fmap_id=$fm_obj->clone_of_fmap_id;
						}
						$fm_new_update = $fm_new->update();
							
						if ($fm_new_update > 0) {
							$active_ids[] = $fm_obj->fmrowid;
							foreach ($fm_arr2 as $fm_obj2) {
								// second clone revision files from old object for the current object
								if ( $fm_obj2->fmactive==self::FILE_REVISION   &&
									 $fm_obj2->fmparent_file==$fm_obj->fmrowid &&
									 !in_array($fm_obj2->fmrowid, $active_ids)) 
								{			
									$clone_fm_rev_id = ElbCommonManager::cloneTableRow(self::$_tbl_name, $clone_fields, $fm_obj2->fmrowid);
									if ($clone_fm_rev_id > 0) {
										$fm_rev_new->fetch($clone_fm_rev_id);
										$fm_new->object_type=$new_object_type;
										$fm_new->object_id=$new_object_id;
										$fm_rev_new->user=$fm_obj2->fmuser;
										$fm_rev_new->created_date=dol_now();
										$fm_rev_new->parent_file=$clone_fm_id;
										if ($old_object_type == 'product') {
											$fm_rev_new->clone_of_fmap_id=$fm_obj2->fmrowid;
										} else {
											$fm_rev_new->clone_of_fmap_id=$fm_obj2->clone_of_fmap_id;
										}
										$fm_rev_new_update = $fm_rev_new->update();
										
										if ($fm_rev_new_update < 0) {
											$error++;
										}
									} else {
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
				}
			}
		}
		if ($error==0) {
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}		
	}
	
	static function deleteLinkedFileMapsForObject($object_type, $object_id) {
		
		global $db;
		
		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.class.php';
		$elbfile = new Elbfile($db);
		
		$fetch_objs = self::fetchLinkedFileMapsForObject($object_type, $object_id);
		if ($fetch_objs) {
			foreach ($fetch_objs as $obj) {
				$get_fmid = $obj->fmrowid;
				$ret = $elbfile->deleteLinked($get_fmid);
				if($ret<0) return false;
			}
		}
		return true;
	}
	
	
	static function printAttachedFilesToGeneratedDoc($object_type, $object_id) {
	
		global $db;
				
		$ret = "";
		$sql = self::sqlSelectFilesAndFileMappings($object_type, $object_id);
		$resql = $db->query($sql);
		
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				if ($obj->fmactive==self::FILE_ACTIVE) {
					$ret.= "\r\n";
					($obj->fmpath) ? $ret.= $obj->fmpath : $ret.= $obj->fname;
					
					if (strlen($obj->fmdescription) > 0) {
						$ret.= ": ".$obj->fmdescription;
					}
				}
				$i++;
			}
		}
		return $ret;
	}
	
	static function findClones($fmap_id) {
		global $db;
		$sql = " SELECT rowid, clone_of_fmap_id";
		$sql.= " FROM ".MAIN_DB_PREFIX.self::$_tbl_name;
		$sql.= " WHERE clone_of_fmap_id=".$db->escape($fmap_id);
		return ElbCommonManager::queryList($sql);
	}
	
	function deleteFileMapsById($fmap_id) {
		global $db;
		$elbfile = new Elbfile($db);
		$elbfile_map = new self($db);
		$elbfile_map->id = $fmap_id;
		$res2 = $elbfile->deleteLinked($fmap_id);
		if ($res2 > 0) {
			return 1;
		}
		return -1;
	}
	
	/**
	 * Use method when user uploads new file version in the: propaldet, commandedet or product type of object
	 */
	static function actionForNewVersionOnSomeObjects($uploaded_file_id, $fmap_object, $description, $fsubrev, $product_type_change) {
		
		global $db, $user;

		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.common.manager.class.php';
		
		if ($product_type_change) 
		{	
			$product_id = $fmap_object->object_id;
			$fmap_initial_id = $fmap_object->id;
			$fmaps_in_product = ElbCommonManager::queryList(self::sqlSelectFilesAndFileMappings('product', $product_id, 2));
			$fmap_clone_id = $fmap_initial_id;
				
			// create new file map in the product tab
			$fm_new = new ELbFileMapping($db);
			$fm_new->fk_fileid 		= $uploaded_file_id;
			$fm_new->object_type 	= 'product';
			$fm_new->object_id 		= $product_id;
			$fm_new->created_date	= dol_now();
			$fm_new->user			= $user->id;
			$fm_new->description	= $description;
			$fm_new->revision		= $fsubrev;
			$fm_new->active			= ELbFileMapping::FILE_ACTIVE;
			$fm_new->tags			= $fmap_object->tags;
			$fm_new_create = $fm_new->create();
				
			if (is_array($fmaps_in_product) && !empty($fmaps_in_product))
			{
				foreach ($fmaps_in_product as $fmap_prod){
					if ($fmap_prod->fmparent_file==$fmap_initial_id)
					{
						$fmap_product = new ELbFileMapping($db);
						$fmap_product->fetch($fmap_prod->fmrowid);
						$fmap_product->active	   = null;
						$fmap_product->parent_file = $fm_new_create;
						$fmap_product->created_date	= $db->jdate($fmap_product->created_date);
						$fmap_product->user	= $fmap_product->user;
						$fmap_product_update = $fmap_product->update();
					}
				}
			}
				
			// set up initial fmap from product to become revision
			$fmap_initial_product = new ELbFileMapping($db);
			$fmap_initial_product->fetch($fmap_initial_id);
			$fmap_initial_product->active	   = null;
			$fmap_initial_product->parent_file = $fm_new_create;
			$fmap_initial_product->created_date	= $db->jdate($fmap_initial_product->created_date);
			$fmap_initial_product->user	= $fmap_initial_product->user;
			$fmap_initial_product_update = $fmap_initial_product->update();			
			
		} 
		else 
		{
			$fmap_object->object_type=='propaldet' ? $object_mode=false : $object_mode=true;
			$product_id = getProductIDViaPositionID($fmap_object->object_id, $object_mode);
			$fmap_clone_id = $fmap_object->clone_of_fmap_id;
			
			$fmaps_in_product = ElbCommonManager::queryList(self::sqlSelectFilesAndFileMappings('product', $product_id, 2));
			
			// create new file map in the product tab
			$fm_new = new ELbFileMapping($db);
			$fm_new->fk_fileid 		= $uploaded_file_id;
			$fm_new->object_type 	= 'product';
			$fm_new->object_id 		= $product_id;
			$fm_new->created_date	= dol_now();
			$fm_new->user			= $user->id;
			$fm_new->description	= $description;
			$fm_new->revision		= $fsubrev;
			$fm_new->active			= ELbFileMapping::FILE_ACTIVE;
			$fm_new->tags			= $fmap_object->tags;
			$fm_new_create = $fm_new->create();
			
			if (is_array($fmaps_in_product) && !empty($fmaps_in_product)) 
			{
				foreach ($fmaps_in_product as $fmap_prod){
					if ($fmap_prod->fmparent_file==$fmap_clone_id) 
					{
						$fmap_product = new ELbFileMapping($db);
						$fmap_product->fetch($fmap_prod->fmrowid);
						$fmap_product->active	   = null;
						$fmap_product->parent_file = $fm_new_create;
						$fmap_product->created_date	= $db->jdate($fmap_product->created_date);
						$fmap_product->user	= $fmap_product->user;
						$fmap_product_update = $fmap_product->update();
					}
				}
			}
			
			// set up clone from product to become revision
			$fmap_clone_product = new ELbFileMapping($db);
			$fmap_clone_product->fetch($fmap_clone_id);
			$fmap_clone_product->active	   = null;
			$fmap_clone_product->parent_file = $fm_new_create;
			$fmap_clone_product->created_date	= $db->jdate($fmap_clone_product->created_date);
			$fmap_clone_product->user	= $fmap_clone_product->user;
			$fmap_clone_product_update = $fmap_clone_product->update();
		}
		

		
		// remove file maps and its subversions from the positions of offer/order linked files
		$fmremoves = new ELbFile();
		$cdfmaps = ElbCommande::orderPositionFileMapCloneOfProductFileMapIdNotOldVersion($fmap_clone_id);
		if (!empty($cdfmaps))
		{
			foreach ($cdfmaps as $cdfmap)
			{
				$res = $fmremoves->deleteLinked($cdfmap->rowid, false);
			}
		}
		$cdfmaps = ElbCommande::orderPositionFileMapCloneOfProductFileMapIdNotOldVersion($fm_new_create);
		if (!empty($cdfmaps))
		{
			foreach ($cdfmaps as $cdfmap)
			{
				$res = $fmremoves->deleteLinked($cdfmap->rowid, false);
			}
		}		
		$pdfmaps = ElbPropal::offerPositionFileMapCloneOfProductFileMapIdNotOldVersion($fmap_clone_id);
		if (!empty($pdfmaps))
		{
			foreach ($pdfmaps as $pdfmap)
			{
				$res = $fmremoves->deleteLinked($pdfmap->rowid, false);
			}
		}
		$pdfmaps = ElbPropal::offerPositionFileMapCloneOfProductFileMapIdNotOldVersion($fm_new_create);
		if (!empty($pdfmaps))
		{
			foreach ($pdfmaps as $pdfmap)
			{
				$res = $fmremoves->deleteLinked($pdfmap->rowid, false);
			}
		}
		// end remove file maps
		
		$fmaps_in_product_revisions = ElbCommonManager::queryList(self::sqlSelectFilesAndFileMappings('product', $product_id, 2));
		
		// set up new file maps in the commandedet
		$cdets = ElbCommande::orderPositionIdWithProductIdNotOldVersion($product_id);
		if (!empty($cdets)) {
			foreach ($cdets as $cdet) {
				$orderline_fm = new ELbFileMapping($db);
				$orderline_fm->fk_fileid 		= $uploaded_file_id;
				$orderline_fm->object_type 		= 'commandedet';
				$orderline_fm->object_id 		= $cdet->rowid;
				$orderline_fm->created_date		= dol_now();
				$orderline_fm->user				= $user->id;
				$orderline_fm->description		= $description;
				$orderline_fm->path				= null;
				$orderline_fm->parent_file		= null;
				$orderline_fm->revision			= $fsubrev;
				$orderline_fm->active			= ELbFileMapping::FILE_ACTIVE;
				$orderline_fm->clone_of_fmap_id	= $fm_new_create;
				$orderline_fm_create = $orderline_fm->create();
				
				// set up subversions from product to become subversions in commandedet
				if (is_array($fmaps_in_product_revisions) && !empty($fmaps_in_product_revisions))
				{
					foreach($fmaps_in_product_revisions as $prod_fmap_rev) 
					{
						if ($prod_fmap_rev->fmparent_file==$fm_new_create)
						{
							$orderline_svn_fm = new ELbFileMapping($db);
							$orderline_svn_fm->fk_fileid = $prod_fmap_rev->frowid;
							$orderline_svn_fm->object_type = 'commandedet';
							$orderline_svn_fm->object_id = $cdet->rowid;
							$orderline_svn_fm->created_date = $db->jdate($prod_fmap_rev->fmcreated_date);
							$orderline_svn_fm->user = $prod_fmap_rev->fmuser;
							$orderline_svn_fm->description = $prod_fmap_rev->fmdescription;
							$orderline_svn_fm->path = $prod_fmap_rev->fmpath;
							$orderline_svn_fm->parent_file = $orderline_fm_create;
							$orderline_svn_fm->revision = $prod_fmap_rev->fmrevision;
							$orderline_svn_fm->active	   = ELbFileMapping::FILE_REVISION;
							$orderline_svn_fm->clone_of_fmap_id = $prod_fmap_rev->fmrowid;
							$orderline_svn_fm_create = $orderline_svn_fm->create();
						}
					}
				}
			}
		}

		// set up new file maps in the propaldet
		$pdets = ElbCommande::offerPositionIdWithProductIdNotOldVersion($product_id);
		if (!empty($pdets)) {
			foreach ($pdets as $pdet) {
				$offerline_fm = new ELbFileMapping($db);
				$offerline_fm->fk_fileid 		= $uploaded_file_id;
				$offerline_fm->object_type 		= 'propaldet';
				$offerline_fm->object_id 		= $pdet->rowid;
				$offerline_fm->created_date		= dol_now();
				$offerline_fm->user				= $user->id;
				$offerline_fm->description		= $description;
				$offerline_fm->path				= null;
				$offerline_fm->parent_file		= null;
				$offerline_fm->revision			= $fsubrev;
				$offerline_fm->active			= ELbFileMapping::FILE_ACTIVE;
				$offerline_fm->clone_of_fmap_id	= $fm_new_create;
				$offerline_fm_create = $offerline_fm->create();
		
				// set up subversions from product to become subversions in commandedet
				if (is_array($fmaps_in_product_revisions) && !empty($fmaps_in_product_revisions))
				{
					foreach($fmaps_in_product_revisions as $prod_fmap_rev)
					{
						if ($prod_fmap_rev->fmparent_file==$fm_new_create)
						{
							$offerline_svn_fm = new ELbFileMapping($db);
							$offerline_svn_fm->fk_fileid = $prod_fmap_rev->frowid;
							$offerline_svn_fm->object_type = 'propaldet';
							$offerline_svn_fm->object_id = $pdet->rowid;
							$offerline_svn_fm->created_date = $db->jdate($prod_fmap_rev->fmcreated_date);
							$offerline_svn_fm->user = $prod_fmap_rev->fmuser;
							$offerline_svn_fm->description = $prod_fmap_rev->fmdescription;
							$offerline_svn_fm->path = $prod_fmap_rev->fmpath;
							$offerline_svn_fm->parent_file = $offerline_fm_create;
							$offerline_svn_fm->revision = $prod_fmap_rev->fmrevision;
							$offerline_svn_fm->active	   = ELbFileMapping::FILE_REVISION;
							$offerline_svn_fm->clone_of_fmap_id = $prod_fmap_rev->fmrowid;
							$offerline_svn_fm_create = $offerline_svn_fm->create();
						}
					}
				}
			}
		}	
		return 1;
	}
	
	static function actionForActivateRevisionOnSomeObjects($fmap_object, $product_type_change) 
	{
		global $db, $user;
		
		// fmap id of product to activate
		if ($product_type_change) {
			$fmap_id_prod_to_activate=$fmap_object->id;
		} else {
			$fmap_id_prod_to_activate=$fmap_object->clone_of_fmap_id;
		}
		$fetch_fmprod_to_activate = new ELbFileMapping($db);

		// fetch fmap of product to activate
		$fetch_fmprod_to_activate->fetch($fmap_id_prod_to_activate);
		
		// get parent file of fmap of product to activate
		$fetch_fmprod_pf = $fetch_fmprod_to_activate->parent_file;
		
		// activate fmap of product for activation
		$fetch_fmprod_to_activate->parent_file = null;
		$fetch_fmprod_to_activate->active=ELbFileMapping::FILE_ACTIVE;
		$fetch_fmprod_to_activate->created_date	= dol_now();
		$fetch_fmprod_to_activate->user	= $user->id;
		$fetch_fmprod_to_activate_update = $fetch_fmprod_to_activate->update();
		
		// fetch product to deactivate
		$fetch_fmprod_to_deactivate = new ELbFileMapping($db);
		$fetch_fmprod_to_deactivate->fetch($fetch_fmprod_pf);

		// update product for deactivation
		$fetch_fmprod_to_deactivate->parent_file = $fetch_fmprod_to_activate->id;
		$fetch_fmprod_to_deactivate->active = ELbFileMapping::FILE_REVISION;
		$fetch_fmprod_to_deactivate->created_date = $db->jdate($fetch_fmprod_to_deactivate->created_date);
		$fetch_fmprod_to_deactivate->user = $fetch_fmprod_to_deactivate->user;
		$fetch_fmprod_to_deactivate_update = $fetch_fmprod_to_deactivate->update();
		
		if ($fetch_fmprod_to_deactivate_update > 0) 
		{
			// fetch all subversions of product which initial was active
			$subvers = new ELbFile(); 
			$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($fetch_fmprod_to_deactivate->id);
			if (!empty($fetch_all_subversions)) {
				foreach ($fetch_all_subversions as $fmid) {
					$change_pfm = new ELbFileMapping($db);
					$change_pfm->fetch($fmid);
					$change_pfm->parent_file = $fetch_fmprod_to_activate->id;
					$change_pfm->active = ELbFileMapping::FILE_REVISION;
					$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
					$res = $change_pfm->update();
				}
			}
		}
		
		// update all filemap clones of product in not old version of propaldet/commandedet
		// update clones in commandedet which are not old version
		$cdfmaps = ElbCommande::orderPositionFileMapCloneOfProductFileMapIdNotOldVersion($fetch_fmprod_to_activate->id);
		if (!empty($cdfmaps))
		{
			foreach ($cdfmaps as $cdfmap)
			{
				$orderline_fm = new ELbFileMapping($db);
				$orderline_fm->fetch($cdfmap->rowid);
				$parent_file = $orderline_fm->parent_file;
				
				// activate fmap for orderline
				$orderline_fm->parent_file = null;
				$orderline_fm->active=ELbFileMapping::FILE_ACTIVE;
				$orderline_fm->created_date	= dol_now();
				$orderline_fm->user	= $user->id;
				$orderline_fm_update = $orderline_fm->update();
				
				// deactivate previous active file from ordeline
				$orderline_fm_to_deactivate = new ELbFileMapping($db);
				$orderline_fm_to_deactivate->fetch($parent_file);
				$orderline_fm_to_deactivate->parent_file = $orderline_fm->id;
				$orderline_fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
				$orderline_fm_to_deactivate->created_date = $db->jdate($cdfmap->created_date);
				$orderline_fm_to_deactivate->user = $cdfmap->user;
				$orderline_fm_to_deactivate_update = $orderline_fm_to_deactivate->update();
				
				if ($orderline_fm_to_deactivate_update > 0)
				{
					// fetch all subversions of product which initial was active
					$subvers = new ELbFile();
					$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($orderline_fm_to_deactivate->id);
					if (!empty($fetch_all_subversions)) {
						foreach ($fetch_all_subversions as $fmid) {
							$change_pfm = new ELbFileMapping($db);
							$change_pfm->fetch($fmid);
							$change_pfm->parent_file = $orderline_fm->id;
							$change_pfm->active = ELbFileMapping::FILE_REVISION;
							$change_pfm->created_date = $db->jdate($change_pfm->created_date);
							$res = $change_pfm->update();
						}
					}
				}
			}
		}
		// update clones in propaldet which are not old version
		$pdfmaps = ElbPropal::offerPositionFileMapCloneOfProductFileMapIdNotOldVersion($fetch_fmprod_to_activate->id);
		if (!empty($pdfmaps))
		{
			foreach ($pdfmaps as $pdfmap)
			{
				$offerline_fm = new ELbFileMapping($db);
				$offerline_fm->fetch($pdfmap->rowid);
				$parent_file = $offerline_fm->parent_file;
		
				// activate fmap for orderline
				$offerline_fm->parent_file = null;
				$offerline_fm->active=ELbFileMapping::FILE_ACTIVE;
				$offerline_fm->created_date	= dol_now();
				$orderline_fm->user	= $user->id;
				$offerline_fm_update = $offerline_fm->update();
		
				// deactivate previous active file from offerline
				$offerline_fm_to_deactivate = new ELbFileMapping($db);
				$offerline_fm_to_deactivate->fetch($parent_file);
				$offerline_fm_to_deactivate->parent_file = $offerline_fm->id;
				$offerline_fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
				$offerline_fm_to_deactivate->created_date = $db->jdate($pdfmap->created_date);
				$offerline_fm_to_deactivate->user = $cdfmap->user;
				$offerline_fm_to_deactivate_update = $offerline_fm_to_deactivate->update();
		
				if ($offerline_fm_to_deactivate_update > 0)
				{
					// fetch all subversions of product which initial was active
					$subvers = new ELbFile();
					$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($offerline_fm_to_deactivate->id);
					if (!empty($fetch_all_subversions)) {
						foreach ($fetch_all_subversions as $fmid) {
							$change_pfm = new ELbFileMapping($db);
							$change_pfm->fetch($fmid);
							$change_pfm->parent_file = $offerline_fm->id;
							$change_pfm->active = ELbFileMapping::FILE_REVISION;
							$change_pfm->created_date = $db->jdate($change_pfm->created_date);
							$res = $change_pfm->update();
						}
					}
				}
			}
		}		
		return 1;
	}
	
	static function returnAttachedFilesAsHyperLinks($object_type, $object_id, $map_type, $filter_by_name=false, $filter_by_rev=false, $separator=', ', $attachment=true, $short_fname=true, $fileData=null) {
		$out = '';
		if($fileData==null) {
			$res = ElbCommonManager::queryList(self::sqlSelectFilesAndFileMappings($object_type, $object_id, $map_type));
		} else {
			$res = $fileData;
		}
		if (!empty($res)) {
			foreach ($res as $index => $obj) {
				if (!$filter_by_name || (!empty($filter_by_name) && strpos($obj->fname, $filter_by_name) !== false)) {
					if (empty($filter_by_rev) || (!empty($filter_by_rev) && strpos($obj->fname, '_'.$filter_by_rev.'.'.$obj->ftype) !== false)) {
						if ($obj->fmpath) {
							$href_download = $obj->fmpath;
							$out .= '<a href="'.$href_download.'">';
                            ($short_fname) ? $out.=dol_trunc($obj->fmpath, 30) : $out.=$obj->fmpath;
                            $out .='</a>'. $separator;
						} else {
							$file_relpath = ELB_UPLOAD_FILES_DIRECTORY.'/'.$obj->frowid.'.'.$obj->ftype;
                            ($attachment) ? $link_as_attachment='&amp;attachment=true' : $link_as_attachment='';
							$href_download = DOL_URL_ROOT . '/document.php?modulepart=elb'.$link_as_attachment.'&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$obj->fmrowid;
							$out .= '<a href="'.$href_download.'">';
                            ($short_fname) ? $out.=dol_trunc($obj->fname, 30) : $out.=$obj->fname;
                            $out .='</a>'.$separator;
						}
					}
				}
			}
		}
		if ($separator==', ') $out = trim($out, $separator);
		return $out;
	}
	
	static function moveSupplyingFilesFromBufferToLinkedObject($buffer_object_type, $buffer_object_id, $link_with_object_type, $link_with_object_id) {
		
		global $db, $conf, $user, $langs;
		
		$output_buffer = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/'.$buffer_object_type.'/'.$buffer_object_id.'/';
		$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';
		
		$files_in_buffer = dol_dir_list($output_buffer, 'files');
		if (is_array($files_in_buffer) && count($files_in_buffer) > 0) {
			
			$elbfile = new ELbFile($db);
			$elbfilemap = new ELbFileMapping($db);
			
			$now = dol_now();
			$error = 0;
			
			$db->begin();
			
			foreach ($files_in_buffer as $file_bf) {
				
				// set file properties
				$elbfile->name = $file_bf['name'];
				$ext  = (new SplFileInfo($elbfile->name))->getExtension();
				$elbfile->type = $ext;
				$elbfile->md5 =  md5_file($output_buffer.$elbfile->name);
				$fileid = $elbfile->create();
				
				if ($fileid > 0) 
				{	 
					// set file mapping properties for stock movement
					$elbfilemap->fk_fileid=$fileid;
					$elbfilemap->object_type=$link_with_object_type;
					$elbfilemap->object_id=$link_with_object_id;
					$elbfilemap->created_date=$now;
					$elbfilemap->user=$user->id;
					$elbfilemap->active=ELbFileMapping::FILE_ACTIVE;
					$fmid = $elbfilemap->create();
					
					if ($fmid > 0) 
					{	
						// move file from buffer to files storage directory
						$move_res = dol_move($output_buffer.$elbfile->name, $output_dir.$fileid.'.'.$ext);
						
						// set file mapping for supplier order
						$elbfilemap_supp_ord = new ELbFileMapping($db);
						$elbCommandeSupplier = new CommandeFournisseur($db);
						$get_supporder_id = ElbCommandeSupplierDelivery::returnSupplierOrderIdForDeliveryId($buffer_object_id);
						$check_if_file_is_linked = self::checkIfFileWithMD5ExistsForObject($get_supporder_id, 'order_supplier', $elbfile->md5);
						if (!$check_if_file_is_linked) { // MV-232
							$elbfilemap_supp_ord->fk_fileid=$fileid;
							$elbfilemap_supp_ord->object_type= $elbCommandeSupplier->element;
							$elbfilemap_supp_ord->object_id = $get_supporder_id;
							$elbfilemap_supp_ord->created_date = $now;
							$elbfilemap_supp_ord->user=$user->id;
							$elbfilemap_supp_ord->active=ELbFileMapping::FILE_ACTIVE;
							$elbfilemap_supp_ord->clone_of_fmap_id = $fmid;
							$fmid_supp_ord = $elbfilemap_supp_ord->create();
						}
					} else {
						$error++;
						break;
					}
				} else {
					$error++;
					break;
				}			
			}
			if ($error==0) {
				$db->commit();
			} else {
				$db->rollback();
				setEventMessage($langs->trans('ErrorMovingFilesToLinkedStockMovement'), 'errors');
			}
		}
		return 1;
	}
	
	static function moveCommonSupplyingFilesFromBufferToLinkedObjects($buffer_object_type, $buffer_object_id, $link_with_object_type, $array) {
	
		global $db, $conf, $user;
		
		// $array - variable holds keys of dlvids and values as stock movements id
	
		$output_buffer = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/'.$buffer_object_type.'/'.$buffer_object_id.'/';
		$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';
	
		$files_in_buffer = dol_dir_list($output_buffer, 'files');
		if (is_array($files_in_buffer) && count($files_in_buffer) > 0) {
				
			$elbfile = new ELbFile($db);
			$elbCommandeSupplier = new CommandeFournisseur($db);
				
			$now = dol_now();
			$error = 0;
				
			$db->begin();
				
			foreach ($files_in_buffer as $file_bf) {
	
				// set file properties
				$elbfile->name = $file_bf['name'];
				$ext  = (new SplFileInfo($elbfile->name))->getExtension();
				$elbfile->type = $ext;
				$elbfile->md5 =  md5_file($output_buffer.$elbfile->name);
				$fileid = $elbfile->create();
	
				if ($fileid > 0)
				{	
					$supporderid_fmapid = array();
					
					// copy file from buffer to files storage directory
					$copy_res = dol_copy($output_buffer.$elbfile->name, $output_dir.$fileid.'.'.$ext);
					
					if (!($copy_res < 0)) 
					{
						foreach ($array as $suppdlvid => $movid) {
							
							$elbfilemap = new ELbFileMapping($db);
							
							// first create file mapping for supplier order (because fmapid in supp. order will be used as clone_of_fmap_id in linked stock movement fmaps)
							$get_supporder_id = ElbCommandeSupplierDelivery::returnSupplierOrderIdForDeliveryId($suppdlvid);
							$check_if_file_is_linked = self::checkIfFileWithMD5ExistsForObject($get_supporder_id, 'order_supplier', $elbfile->md5);
							if (!$check_if_file_is_linked) { // MV-232
								$elbfilemap_supp_ord = new ELbFileMapping($db);
								$elbfilemap_supp_ord->fk_fileid=$fileid;
								$elbfilemap_supp_ord->object_type= $elbCommandeSupplier->element;
								$elbfilemap_supp_ord->object_id = $get_supporder_id;
								$elbfilemap_supp_ord->created_date = $now;
								$elbfilemap_supp_ord->user=$user->id;
								$elbfilemap_supp_ord->active=ELbFileMapping::FILE_ACTIVE;
								$elbfilemap_supp_ord->clone_of_fmap_id = null;
								$fmid_supp_ord = $elbfilemap_supp_ord->create();
								if ($fmid_supp_ord < 0) {
									$error++;
									break 2;
								}
								$supporderid_fmapid[$get_supporder_id] = $fmid_supp_ord;
							}
							
							// second create file mapping for each  movement and assign clone_of_fmap_id to be fmapid of it's linked fmap from supp. order						$elbfilemap->fk_fileid=$fileid;
							$elbfilemap->fk_fileid=$fileid;
							$elbfilemap->object_type=$link_with_object_type;
							$elbfilemap->object_id=$movid;
							$elbfilemap->created_date=$now;
							$elbfilemap->user=$user->id;
							$elbfilemap->active=ELbFileMapping::FILE_ACTIVE;
							$elbfilemap->clone_of_fmap_id = $supporderid_fmapid[$get_supporder_id];
							$fmid = $elbfilemap->create();
							if ($fmid < 0) {
								$error++;
								break 2;
							}
						}
					} else {
						$error++;
						break;
					}
				} else {
					$error++;
					break;
				}
			}
			if ($error==0) {
				$db->commit();
			} else {
				$db->rollback();
				return -1;
			}
		}
		return 1;
	}	
	
	static function deleteFilesFromStorageFolder($path) {
		dol_delete_dir_recursive($path);
	}
	
	static function actionForNewVersionOnStockMovementLinkedSuppOrder($uploaded_file_id, $fmap_object, $description, $fsubrev) {
		
		global $db, $user;
		
		$now = dol_now();
		
		$error = 0;
		$db->begin();
		
		$stock_movement_id = $fmap_object->object_id;
		$fmap_initial_id = $fmap_object->id;
		$fmaps_revs_in_stock_mov = ElbCommonManager::queryList(self::sqlSelectFilesAndFileMappings('elb_stock_mouvement', $stock_movement_id, 2));
		
		$fetch_movement = new ElbMouvementStock($db);
		$res_fetch_mov = $fetch_movement->fetch($stock_movement_id);
		
		if ($res_fetch_mov < 0) $error++; 
		
		// create new file map for stock movement
		$fm_new = new ELbFileMapping($db);
		$fm_new->fk_fileid 		= $uploaded_file_id;
		$fm_new->object_type 	= 'elb_stock_mouvement';
		$fm_new->object_id 		= $stock_movement_id;
		$fm_new->created_date	= $now;
		$fm_new->user			= $user->id;
		$fm_new->description	= $description;
		$fm_new->revision		= $fsubrev;
		$fm_new->active			= ELbFileMapping::FILE_ACTIVE;
		$fm_new_create 			= $fm_new->create();
		
		if ($fm_new_create < 0) $error++;
		
		// find clones for initial (stock movement) file map
		$res = ELbFileMapping::findClones($fmap_initial_id);
		if (is_array($res) && count($res) > 0) 
		{	
			// create new file map for linked supplier order with movement
			$fm_new_supp_order = new ELbFileMapping($db);
			$get_supporder_id = ElbCommandeSupplierDelivery::returnSupplierOrderIdForDeliveryId($fetch_movement->fk_origin);
			$fm_new_supp_order->fk_fileid 		= $uploaded_file_id;
			$fm_new_supp_order->object_type 	= 'order_supplier';
			$fm_new_supp_order->object_id 		= $get_supporder_id;
			$fm_new_supp_order->created_date	= $now;
			$fm_new_supp_order->user			= $user->id;
			$fm_new_supp_order->description		= $description;
			$fm_new_supp_order->revision		= $fsubrev;
			$fm_new_supp_order->active			= ELbFileMapping::FILE_ACTIVE;
			$fm_new_supp_order->clone_of_fmap_id= $fm_new_create;
			$fm_new_supp_order_create 			= $fm_new_supp_order->create();
			
			if ($fm_new_supp_order_create < 0) $error++;
			
			// set up fmap from order supplier to become revision
			$fmap_supp_order = new ELbFileMapping($db);
			$fmap_supp_order->fetch($res[0]->rowid);
			$fmap_supp_order->active	   = null;
			$fmap_supp_order->parent_file = $fm_new_supp_order_create;
			$fmap_supp_order->created_date	= $db->jdate($fmap_supp_order->created_date);
			$fmap_supp_order->user	= $fmap_supp_order->user;
			$fmap_supp_order_update = $fmap_supp_order->update(false);

			if ($fmap_supp_order_update < 0) $error++;
		}		
		
		// update revisions in stock movement (and in linked supplier order)
		if (is_array($fmaps_revs_in_stock_mov) && count($fmaps_revs_in_stock_mov) > 0)
		{
			foreach ($fmaps_revs_in_stock_mov as $fmap_rev){
				if ($fmap_rev->fmparent_file==$fmap_initial_id)
				{
					$fmap_revision = new ELbFileMapping($db);
					$fmap_revision->fetch($fmap_rev->fmrowid);
					$fmap_revision->active	   = null;
					$fmap_revision->parent_file = $fm_new_create;
					$fmap_revision->created_date = $db->jdate($fmap_revision->created_date);
					$fmap_revision->user	= $fmap_revision->user;
					$fmap_revision_update = $fmap_revision->update(false);
					
					if ($fmap_revision_update < 0) $error++;
					
					$supp_order_subver = ELbFileMapping::findClones($fmap_rev->fmrowid);
					if (is_array($supp_order_subver) && count($supp_order_subver) > 0)
					{
						foreach ($supp_order_subver as $supp_obj) {
							$supporder_fm = new ELbFileMapping($db);
							$res = $supporder_fm->fetch($supp_obj->rowid);
							$supporder_fm->created_date = $db->jdate($supporder_fm->created_date);
							$supporder_fm->user			= $supporder_fm->user;
							$supporder_fm->parent_file	= $fm_new_supp_order_create;
							$res_update = $supporder_fm->update(false);
							if ($res_update < 0) $error++;
						}
					}					
				}
			}
		}
		
		// set up initial fmap from stock movement to become revision
		$fmap_initial_mov = new ELbFileMapping($db);
		$fmap_initial_mov->fetch($fmap_initial_id);
		$fmap_initial_mov->active	   = null;
		$fmap_initial_mov->parent_file = $fm_new_create;
		$fmap_initial_mov->created_date	= $db->jdate($fmap_initial_mov->created_date);
		$fmap_initial_mov->user	= $fmap_initial_mov->user;
		$fmap_initial_mov_update = $fmap_initial_mov->update(false);
		if ($fmap_initial_mov_update < 0) $error++;
		
		if ($error == 0) {
			$db->commit();
			elb_common_action_result(true);
		} else {
			$db->rollback();
			elb_common_action_result(false);
			return -1;
		}
		
		return 1;
	}
	
	static function actionForActivateRevisionOnStockMovementLinkedSuppOrder($fmap_object)
	{
		global $db, $user;
		
		$now = dol_now();
		
		$error = 0;
		$db->begin();

		// fmap to activate
		$fmap_id_to_activate = $fmap_object->id;
		// get parent file of fmap to activate
		$fetch_fm_pf = $fmap_object->parent_file;
		
		// fetch clone of fmap id to activate
		$supp_order_clones = ELbFileMapping::findClones($fmap_object->id);
		if (is_array($supp_order_clones) && count($supp_order_clones) > 0)
		{
			foreach ($supp_order_clones as $supp_obj) 
			{
				$clone_to_activate = new ELbFileMapping($db);
				$clone_to_activate->fetch($supp_obj->rowid);
				$clone_fm_pf = $clone_to_activate->parent_file;
				
				// activate clone
				$clone_to_activate->parent_file = null;
				$clone_to_activate->active = ELbFileMapping::FILE_ACTIVE;
				$clone_to_activate->created_date	= $now;
				$clone_to_activate->user	= $user->id;
				$clone_to_activate_update = $clone_to_activate->update(false);
				
				if ($clone_to_activate_update < 0) $error++;
				
				// fetch clone file map to deactivate
				$fetch_clone_to_deactivate = new ELbFileMapping($db);
				$fetch_clone_to_deactivate->fetch($clone_fm_pf);
				
				if ($fetch_clone_to_deactivate < 0) $error++;
				
				// update clone file map for deactivation
				$fetch_clone_to_deactivate->parent_file = $clone_to_activate->id;
				$fetch_clone_to_deactivate->active = ELbFileMapping::FILE_REVISION;
				$fetch_clone_to_deactivate->created_date = $db->jdate($fetch_clone_to_deactivate->created_date);
				$fetch_clone_to_deactivate->user = $fetch_clone_to_deactivate->user;
				$fetch_clone_to_deactivate_update = $fetch_clone_to_deactivate->update(false);
				
				if ($fetch_clone_to_deactivate_update < 0) $error++;
				
				// fetch all subversions of product which initial was active
				$subvers = new ELbFile();
				$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($clone_fm_pf);
				if (!empty($fetch_all_subversions)) {
					foreach ($fetch_all_subversions as $fmid) {
						$change_pfm = new ELbFileMapping($db);
						$change_pfm->fetch($fmid);
						$change_pfm->parent_file = $clone_to_activate->id;
						$change_pfm->active = ELbFileMapping::FILE_REVISION;
						$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
						$res = $change_pfm->update(false);
						if ($res < 0) $error++;
					}
				}
			}
		}
		
		// activate fmap for activation
		$fmap_object->parent_file = null;
		$fmap_object->active = ELbFileMapping::FILE_ACTIVE;
		$fmap_object->created_date	= $now;
		$fmap_object->user	= $user->id;
		$fmap_object_update = $fmap_object->update(false);
		if ($fmap_object_update < 0) $error++;
		
		// fetch file map to deactivate
		$fetch_fm_to_deactivate = new ELbFileMapping($db);
		$fetch_fm_to_deactivate->fetch($fetch_fm_pf);
		// update product for deactivation
		$fetch_fm_to_deactivate->parent_file = $fmap_id_to_activate;
		$fetch_fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
		$fetch_fm_to_deactivate->created_date = $db->jdate($fetch_fm_to_deactivate->created_date);
		$fetch_fm_to_deactivate->user = $fetch_fm_to_deactivate->user;
		$fetch_fm_to_deactivate_update = $fetch_fm_to_deactivate->update(false);
		if ($fetch_fm_to_deactivate_update < 0) $error++;
		
		// fetch all subversions of product which initial was active
		$subvers = new ELbFile();
		$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($fetch_fm_pf);
		if (!empty($fetch_all_subversions)) {
			foreach ($fetch_all_subversions as $fmid) {
				$change_pfm = new ELbFileMapping($db);
				$change_pfm->fetch($fmid);
				$change_pfm->parent_file = $fmap_id_to_activate;
				$change_pfm->active = ELbFileMapping::FILE_REVISION;
				$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
				$res = $change_pfm->update(false);
				if ($res < 0) $error++;
			}
		}
		
		if ($error ==0) {
			$db->commit();
			elb_common_action_result(true);
		} else {
			$db->rollback();
			elb_common_action_result(false);
			return -1;
		}		
		
		return 1;
	}
	
	static function actionForActivateRevisionOnSuppOrderLinkedStockMovement($fmap_object) {
		
		global $db, $user;
		
		$now = dol_now();
		
		$error = 0;
		$db->begin();
		
		// fmap to activate
		$fmap_id_to_activate = $fmap_object->id;
		// get parent file of fmap to activate
		$fetch_fm_pf = $fmap_object->parent_file;
		
		// fetch clone of fmap id to activate
		if ($fmap_object->clone_of_fmap_id) 
		{
			$clone_to_activate = new ELbFileMapping($db);
			$clone_to_activate->fetch($fmap_object->clone_of_fmap_id);
			$clone_fm_pf = $clone_to_activate->parent_file;
	
			// activate clone
			$clone_to_activate->parent_file = null;
			$clone_to_activate->active = ELbFileMapping::FILE_ACTIVE;
			$clone_to_activate->created_date	= $now;
			$clone_to_activate->user	= $user->id;
			$clone_to_activate_update = $clone_to_activate->update(false);
			
			if ($clone_to_activate_update < 0) $error++;
	
			// fetch clone file map to deactivate
			$fetch_clone_to_deactivate = new ELbFileMapping($db);
			$res = $fetch_clone_to_deactivate->fetch($clone_fm_pf);
			
			if ($res < 0) $error++;
	
			// update clone file map for deactivation
			$fetch_clone_to_deactivate->parent_file = $clone_to_activate->id;
			$fetch_clone_to_deactivate->active = ELbFileMapping::FILE_REVISION;
			$fetch_clone_to_deactivate->created_date = $db->jdate($fetch_clone_to_deactivate->created_date);
			$fetch_clone_to_deactivate->user = $fetch_clone_to_deactivate->user;
			$fetch_clone_to_deactivate_update = $fetch_clone_to_deactivate->update(false);
			
			if ($fetch_clone_to_deactivate_update < 0) $error++;

			// fetch all subversions of product which initial was active
			$subvers = new ELbFile();
			$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($clone_fm_pf);
			if (!empty($fetch_all_subversions)) {
				foreach ($fetch_all_subversions as $fmid) {
					$change_pfm = new ELbFileMapping($db);
					$change_pfm->fetch($fmid);
					$change_pfm->parent_file = $clone_to_activate->id;
					$change_pfm->active = ELbFileMapping::FILE_REVISION;
					$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
					$res = $change_pfm->update(false);
					
					if ($res < 0) $error++;
				}
			}	
		}
		
		// activate fmap for activation
		$fmap_object->parent_file = null;
		$fmap_object->active = ELbFileMapping::FILE_ACTIVE;
		$fmap_object->created_date	= $now;
		$fmap_object->user	= $user->id;
		$fmap_object_update = $fmap_object->update(false);
		
		if ($fmap_object_update < 0) $error++;
		
		// fetch file map to deactivate
		$fetch_fm_to_deactivate = new ELbFileMapping($db);
		$fetch_fm_to_deactivate->fetch($fetch_fm_pf);
		// update product for deactivation
		$fetch_fm_to_deactivate->parent_file = $fmap_id_to_activate;
		$fetch_fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
		$fetch_fm_to_deactivate->created_date = $db->jdate($fetch_fm_to_deactivate->created_date);
		$fetch_fm_to_deactivate->user = $fetch_fm_to_deactivate->user;
		$fetch_fm_to_deactivate_update = $fetch_fm_to_deactivate->update(false);
		
		if ($fetch_fm_to_deactivate_update < 0) $error++;

		// fetch all subversions of product which initial was active
		$subvers = new ELbFile();
		$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($fetch_fm_pf);
		if (!empty($fetch_all_subversions)) {
			foreach ($fetch_all_subversions as $fmid) {
				$change_pfm = new ELbFileMapping($db);
				$change_pfm->fetch($fmid);
				$change_pfm->parent_file = $fmap_id_to_activate;
				$change_pfm->active = ELbFileMapping::FILE_REVISION;
				$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
				$res = $change_pfm->update(false);
				if ($res < 0) $error++;
			}
		}
		
		if ($error ==0) {
			$db->commit();
			elb_common_action_result(true);
		} else {
			$db->rollback();
			elb_common_action_result(false);
			return -1;
		}		

		return 1;
	}
	
	static function actionForNewVersionOnSuppOrderLinkedStockMovement($uploaded_file_id, $fmap_object, $description, $fsubrev) {
	
		global $db, $user;
	
		$now = dol_now();
		
		$error = 0;
		$db->begin();
	
		$fmap_object_id = $fmap_object->object_id;
		$fmap_initial_id = $fmap_object->id;
		$fmap_initial_clone_id = $fmap_object->clone_of_fmap_id; // clone of fmap id (stock movement id)

		if ($fmap_initial_clone_id) 
		{				
			// fetch initial clone (stock movement)
			$fetch_initial_clone = new ELbFileMapping($db);
			$fetch_initial_clone->fetch($fmap_initial_clone_id);
			//$fetch_initial_clone_fm_pf = $fetch_initial_clone->parent_file;
			
			// create new file map in stock movement
			$fm_stock_mov_new = new ELbFileMapping($db);
			$fm_stock_mov_new->fk_fileid = $uploaded_file_id;
			$fm_stock_mov_new->object_type = $fetch_initial_clone->object_type;
			$fm_stock_mov_new->object_id = $fetch_initial_clone->object_id;
			$fm_stock_mov_new->created_date	= $now;
			$fm_stock_mov_new->user	= $user->id;
			$fm_stock_mov_new->description = $description;
			$fm_stock_mov_new->revision	= $fsubrev;
			$fm_stock_mov_new->active = ELbFileMapping::FILE_ACTIVE;
			$fm_stock_mov_new_create = $fm_stock_mov_new->create();
			
			if ($fm_stock_mov_new_create < 0) $error++;

			// update initial clone (stock movement) 
			$fetch_initial_clone->active	   = null;
			$fetch_initial_clone->parent_file = $fm_stock_mov_new_create;
			$fetch_initial_clone->created_date = $db->jdate($fetch_initial_clone->created_date);
			$fetch_initial_clone->user	= $fetch_initial_clone->user;
			$fetch_initial_clone_update = $fetch_initial_clone->update();
			
			if ($fetch_initial_clone_update < 0) $error++;
			
			// update stock movement subversions
			$initial_clone_subvers = new ELbFile();
			$fetch_all_subversions = $initial_clone_subvers->fetchFileVersionByParentFile($fmap_initial_clone_id);
			if (!empty($fetch_all_subversions)) {
				foreach ($fetch_all_subversions as $fmid) {
					$change_pfm = new ELbFileMapping($db);
					$change_pfm->fetch($fmid);
					$change_pfm->parent_file = $fm_stock_mov_new_create;
					$change_pfm->active = ELbFileMapping::FILE_REVISION;
					$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
					$res = $change_pfm->update(false);
					
					if ($res < 0) $error++;
				}
			}
			
			// create new file map in supplier order
			$fm_new = new ELbFileMapping($db);
			$fm_new->fk_fileid 		= $uploaded_file_id;
			$fm_new->object_type 	= 'order_supplier';
			$fm_new->object_id 		= $fmap_object_id;
			$fm_new->created_date	= $now;
			$fm_new->user			= $user->id;
			$fm_new->description	= $description;
			$fm_new->revision		= $fsubrev;
			$fm_new->active			= ELbFileMapping::FILE_ACTIVE;
			$fm_new->clone_of_fmap_id= $fm_stock_mov_new_create;
			$fm_new_create 			= $fm_new->create();
			
			if ($fm_new_create < 0) $error++;

			// update initial file map (supplier order)
			$fmap_object->active	   = null;
			$fmap_object->parent_file = $fm_new_create;
			$fmap_object->created_date = $db->jdate($fmap_object->created_date);
			$fmap_object->user	= $fmap_object->user;
			$fmap_object_update = $fmap_object->update();
			
			if ($fmap_object_update < 0) $error++;
			
			// fetch all subversions from supplier order
			$subvers = new ELbFile();
			$fetch_all_subversions = $subvers->fetchFileVersionByParentFile($fmap_initial_id);
			if (!empty($fetch_all_subversions)) {
				foreach ($fetch_all_subversions as $fmid) {
					$change_pfm = new ELbFileMapping($db);
					$change_pfm->fetch($fmid);
					$change_pfm->parent_file = $fm_new_create;
					$change_pfm->active = ELbFileMapping::FILE_REVISION;
					$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
					$res = $change_pfm->update(false);
					if ($res < 0) $error++;
				}
			}
		}
		
		if ($error ==0) {
			$db->commit();
			elb_common_action_result(true);
		} else {
			$db->rollback();
			elb_common_action_result(false);
			return -1;
		}		
			
		return 1;
	}
	
	static function checkIfFileWithMD5ExistsForObject($object_id, $object_type, $md5_hash) {
		global $db;
		$sql = " SELECT fm.fk_fileid, fm.object_type, fm.object_id, ";
		$sql.= " f.rowid, f.md5 ";
		$sql.= " FROM ".MAIN_DB_PREFIX.self::$_tbl_name." fm ";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.ELbFile::$_tbl_name." f ON (f.rowid=fm.fk_fileid) ";
		$sql.= " WHERE fm.object_id=".$db->escape($object_id);
		$sql.= " AND fm.object_type='".$db->escape($object_type)."' ";
		$sql.= " AND f.md5='".$db->escape($md5_hash)."' ";
		$res = ElbCommonManager::queryList($sql);
		if (count($res) > 0) {
			return true;
		}
		return false;
	}
	
	static function getObjectTags($object_type, $object_id) {
		global $db;
		$sql="SELECT * FROM ".MAIN_DB_PREFIX.self::$_tbl_name." fm ";
		$sql.= " WHERE fm.object_id=".$db->escape($object_id);
		$sql.= " AND fm.object_type='".$db->escape($object_type)."' ";
		$rows = ElbCommonManager::queryList($sql);
		$data=array();
		foreach($rows as $row) {
			$id = $row->rowid;
			$tags = $row->tags;
			if(!empty($tags)) {
				$tags = json_decode($tags, true);
				if($tags!==false) {
					foreach($tags as $tag) {
						$data[$tag][]=$id;
					}
				}
			}
		}
		return $data;
	}
	

	function getNomUrl() {
		global $langs;
		$file_relpath = '';
		if (empty($this->fmpath)) {
			$file_relpath = ELB_UPLOAD_FILES_DIRECTORY.'/'.$this->frowid.'.'.$this->ftype;
		} else {
			$file_relpath .= $this->fmpath;
		}
		$href = DOL_URL_ROOT . '/document.php?modulepart=elb&attachment=true&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$this->id;
		$label=img_mime($this->fname,$langs->trans("File").': '.$this->fname);
		$label.=" ".$this->fname;
		return '<a href="'.$href.'">'.$label.'</a>';
	}
		
}