<?php
//use ELBClass\solr\ElbSolrUtil;

/* Copyright (C)
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
 
class ELbFileMapping extends CommonObject
{
	
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
	
		if ($resql) {
			//if (!empty($this->action)) {
			if (in_array($this->object_type, self::arrayWithObjectTypesForActivatingTrigger()) && $this->action !== self::ACTION_NONE && $activate_trigger) {
				$this->action = self::ACTION_DELETE;
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
	
	static function arrayWithObjectTypesForActivatingTrigger() {
		return array('product', 'elb_stock_mouvement', 'order_supplier','tech_procedure');
	}

    /**
     * @param $fk_fileid
     * @return int
     * @throws Exception
     */
	function countLinkedFilesByFkFileID($fk_fileid)
    {
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
	
	static function countLinkedFilesByObjectType($object_type, $object_id, $only_attached=true)
    {
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
	

	static function deleteLinkedFileMapsForObject($object_type, $object_id)
    {
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

	static function deleteFilesFromStorageFolder($path)
    {
		dol_delete_dir_recursive($path);
	}

	static function checkIfFileWithMD5ExistsForObject($object_id, $object_type, $md5_hash)
    {
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
	
	static function getObjectTags($object_type, $object_id)
    {
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
	

	function getNomUrl()
    {
		global $conf, $langs;
		$file_relpath = '';
		if (empty($this->fmpath)) {
			$file_relpath = $conf->elbmultiupload->ELB_UPLOAD_FILES_DIRECTORY.'/'.$this->frowid.'.'.$this->ftype;
		} else {
			$file_relpath .= $this->fmpath;
		}
		$href = DOL_URL_ROOT . '/document.php?modulepart=elb&attachment=true&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$this->id;
		$label=img_mime($this->fname,$langs->trans("File").': '.$this->fname);
		$label.=" ".$this->fname;
		return '<a href="'.$href.'">'.$label.'</a>';
	}
		
}