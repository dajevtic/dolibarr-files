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
 *	\file       htdocs/elbmultiupload/class/elb.file_mapping.class.php
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
	
	public $id;
    public $fk_fileid;
    public $object_type;
    public $object_id;
    public $created_date;
    public $user;
    public $description;
    public $path;
    public $parent_file;
    public $revision;
    public $active;
    public $clone_of_fmap_id;
	// holds and action type
    public $action;
    public $tags;

    public $tbl_name='elb_file_mapping';
	static $_tbl_name='elb_file_mapping';

	function __construct($db)
	{
		$this->db = $db;
	}

	function fetch($id='')
	{
		global $db;
	
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
		global $db;
	
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
	
	function update($activate_trigger=true)
    {
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

	function delete($activate_trigger=true)
    {
		global $db, $user;
	
		$db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " WHERE rowid = ".$this->id;
	
		dol_syslog(get_class($this)."::delete id=".$this->id, LOG_DEBUG);
	
		$resql=$db->query($sql);
	
		if ($resql) {

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
			
		if ($resql=$db->query($sql)) {
			$obj = $this->db->fetch_object($resql);
			$count=$obj->cnt;
			if($count>0) {
				return $count;
			} else {
				return 0;
			}
		} else {
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
			
		if ($resql=$db->query($sql)) {
			$obj = $db->fetch_object($resql);
			$count=$obj->cnt;
			if($count>0) {
				return $count;
			} else {
				return;
			}
		} else {
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
	static function sqlSelectFilesAndFileMappings($object_type, $object_id, $map_type=0)
    {
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
	
	static function fetchLinkedFileMapsForObject($object_type, $object_id)
    {
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
			
		if ($resql=$db->query($sql)) {
			$i = 0;
			$num = $db->num_rows($resql);
			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				$fetch[$i] = $obj;
				$i++;
			}
		} else {
			dol_syslog(get_called_class().'::fetchLinkedFileMapsForObject ERROR sql='.$sql, LOG_DEBUG);
		}
		return $fetch;
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
}