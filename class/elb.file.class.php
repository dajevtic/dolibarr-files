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
 
class ELbFile
{
	public $id;
    public $name;
    public $type;
    public $md5;

    public $tbl_name='elb_file';
	static $_tbl_name='elb_file';
	
	/**
	 *  Load file info from database to memory
	 *
	 *  @param	int		$id     id of file to load
	 *  @return int     		<0 if KO, >0 if OK
	 */
	function fetch($id='')
	{
		global $langs, $conf, $db;
	
		dol_syslog(get_class($this)."::fetch id=".$id);
	
		// Check parameters
		if ( empty($id) || !is_numeric($id)) {
			$this->error='ErrorWrongParameters';
			dol_print_error(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->tbl_name;
		$sql.=	' WHERE rowid = '.$id;
	
		dol_syslog(get_class($this)."::fetch sql=".$sql);
	
		$resql = $db->query($sql);
	
		if ($resql)	{
			if ($db->num_rows($resql) > 0) {

				$obj = $db->fetch_object($resql);
				
				// set properties
				$this->id	  		= $obj->rowid;
				$this->name 		= $obj->name;
				$this->type 		= $obj->type;
				$this->md5			= $obj->md5;
				$db->free($resql);	
				return 1;
			}
		}

        dol_syslog(get_class($this)."::fetch ERROR sql=".$sql);
        return -1;
	}
	
	/**
	 *	Insert file info into database
	 *
	 *	@return int		id of a row if OK or if error < 0
	 */
	function create()
    {
        global $db;

        $db->begin();

        $error = 0;

        dol_syslog(get_class($this) . "::create", LOG_DEBUG);

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . $this->tbl_name . " (";
        $sql .= "name";
        $sql .= ", type";
        $sql .= ", md5";
        $sql .= ") VALUES (";
        $sql .= "'" . $db->escape($this->name) . "'";
        $sql .= ", '" . $db->escape($this->type) . "'";
        $sql .= ", " . ($this->md5 == null ? 'null' : "'" . $db->escape($this->md5) . "'");
        $sql .= ")";

        dol_syslog(get_class($this) . "::create sql=" . $sql);

        $result = $db->query($sql);

        if ($result) {
            $this->id = $db->last_insert_id(MAIN_DB_PREFIX . $this->tbl_name);
            $db->commit();
            dol_syslog(get_class($this) . "::create done id=" . $this->id);
            return $this->id;
        }

        $db->rollback();
        dol_syslog(get_class($this) . "::create Error id=" . $this->id);
        return -1;
    }

    function update()
    {
		global $db;
	
		$db->begin();
	
		$sql="UPDATE ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.=" SET name = '".$db->escape($this->name)."', ";
		$sql.=" type = ".($this->type==null ? "null" : "'".$db->escape($this->type)."'").", ";
		$sql.=" description = ".($this->description==null ? "null" : "'".$db->escape($this->description)."'").", ";
		$sql.=" path = ".($this->path==null ? "null" : "'".$db->escape($this->path)."'").", ";
		$sql.=" md5 = '".$db->escape($this->md5)."', ";
		$sql.=" parent_file = ".($this->parent_file==null ? "null" : $db->escape($this->parent_file)).", ";
		$sql.=" revision = ".($this->revision==null ? "null" : "'".$db->escape($this->revision)."'").", ";
		$sql.=" active = ".($this->active==null ? "null" : $db->escape($this->active));
		$sql.=" WHERE rowid=".$db->escape($this->id);
		
		dol_syslog(get_class($this)."::update id=".$this->id, LOG_DEBUG);
	
		$resql=$db->query($sql);
	
		if($resql) {
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}
	}

	function fetchFileVersions($fileid)
    {
		global $db;
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.parent_file = '".$db->escape($fileid)."'";
		$sql.= " AND fm.active=".ELbFileMapping::FILE_REVISION;
		$sql.= " ORDER BY f.rowid DESC";

		dol_syslog(get_class($this)."::fetchFileVersions sql=".$sql);

		return ElbCommonManager::queryList($sql);
	}

	function printFileVersions($files, $toolbox)
    {
		global $langs,$conf,$db,$user;
		global $bc;

		$action2 = GETPOST('action2');
		$fileid = GETPOST('rowid');

		print '<tr>';
		print '<td></td>';
		print '<td colspan="6">';
		$title=$langs->trans("FileVersion(s)");

		print '<div class="titre">'.$title.'</div>';
		print '<table class="border" summary="listofdocumentstable" width="100%">';
		print '<tr class="liste_titre malign" width="100%">';
		print '<td width="30" align="center">'.$langs->trans("Nr").'</td>';
		print '<td>'.$langs->trans("File").'</td>';
		print '<td>'.$langs->trans("Description").'</td>';
		print '<td>'.$langs->trans("Revision").'</td>';
		print '<td>'.$langs->trans("Size").'</td>';
		print '<td width="110">'.$langs->trans("Modified").'</td>';
		print '<td>'.$langs->trans("User").'</td>';
		if ($toolbox){
			print '<td align="right">';
			print '</td>';
		}
		print '</tr>';

		//$maxfilenamelength = 30; //define max length of a file name
		$var = false;
		foreach($files as $obj) {
			$var=!$var;

			($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

			include dol_buildpath('/elbmultiupload/tpl/files/position/file_revision.tpl.php');
		}
		print "</table>\n";
		print '</td>';
		print '</tr>';
	}
	
	function getFileVersions($fileid, $toolbox, $restictDelete=false)
    {
		global $langs,$conf,$db,$user;
		global $bc;
		
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.parent_file = '".$db->escape($fileid)."'";
		$sql.= " AND fm.active=".ELbFileMapping::FILE_REVISION;
		$sql.= " ORDER BY f.rowid DESC";
		
		dol_syslog(get_class($this)."::getFileVersions sql=".$sql);
	
		$resql = $db->query($sql);
	
		if ($resql)
			$num = $db->num_rows($resql);
	
		if ($num > 0) {
				
			$action2 = GETPOST('action2');
			$fileid = GETPOST('rowid');
				
			print '<tr>';
			print '<td></td>';
			print '<td colspan="6">';
			$title=$langs->trans("FileVersion(s)");
				
			print '<div class="titre">'.$title.'</div>';
			print '<table class="border" summary="listofdocumentstable" width="100%">';
			print '<tr class="liste_titre malign" width="100%">';
			print '<td width="30" align="center">'.$langs->trans("Nr").'</td>';
			print '<td>'.$langs->trans("File").'</td>';
			print '<td>'.$langs->trans("Description").'</td>';
			print '<td>'.$langs->trans("Revision").'</td>';
			print '<td>'.$langs->trans("Size").'</td>';
			print '<td width="110">'.$langs->trans("Modified").'</td>';
			print '<td>'.$langs->trans("User").'</td>';
			if ($toolbox){
				print '<td align="right">';
				print '</td>';
			}
			print '</tr>';
				
			$i = 0;
			//$maxfilenamelength = 30; //define max length of a file name
			$var = false;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				$var=!$var;
	
				($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;
	
				include dol_buildpath('/elbmultiupload/tpl/files/position/file_revision.tpl.php');
			}
			print "</table>\n";
			print '</td>';
			print '</tr>';
		}
	}

	function fetchUploadedFiles($object_type=null,$object_id=null, $search_files=null)
    {
		global $db;

		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.tags as fmtags";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.active=".ELbFileMapping::FILE_ACTIVE;
		if(!empty($object_type)) {
			$sql.= " AND fm.object_type = '".$db->escape($object_type)."'";
		}
		if(!empty($object_id)) {
			$sql.= " AND fm.object_id = ".$db->escape($object_id);
		}
		if(isset($search_files)) {
			if(count($search_files)==0) {
				$list="0";
			} else {
				$list=implode(",", $search_files);
			}
			$sql.= " AND fm.rowid IN (".$list.")";
		}
		$sql.= " ORDER BY fm.rowid DESC";

		dol_syslog(get_class($this)."::fetchUploadedFiles sql=".$sql);

		$fetch_files = ElbCommonManager::queryList($sql);

		return $fetch_files;

	}
	
	function getUploadedFiles($object_type=null,$object_id=null, $toolbox=1, $tag_map=false, $search_files=null, $restictDeleteFile=false)
	{
		global $bc, $langs, $conf, $db, $user;

		$fetch_files = $this->fetchUploadedFiles($object_type,$object_id, $search_files);

		$this->printUploadedFiles($fetch_files, $tag_map, $toolbox);
	}

	function printUploadedFiles($fetch_files, $tag_map=false, $toolbox=1, $restictDeleteFile=false)
    {
		global $bc, $langs, $conf, $db, $user;

		$action2 = GETPOST('action2');
		$fileid = GETPOST('rowid');

		if (is_array($tag_map)) {

			// array of categorized  (array(tag name => array( custom_index =>  value_is_key_of_$fetch_files)))
			$tag_assigned_fmap = array();

			// array of uncategorized files (
			$tag_uncategorized_fmap = array();

			$uncateg_name = $langs->trans("Uncategorized");

			// populate categorized array
			foreach ($tag_map as $tag_name => $arr_assigned_fmaps) {
				foreach ($arr_assigned_fmaps as $assigned_fmap_id) {
					foreach ($fetch_files as $ind => $files) {
						if ($files->fmrowid == $assigned_fmap_id) {
							$tag_assigned_fmap[$tag_name][] = $ind;
						}
					}
				}
			}

			// populate uncategorized array
			foreach ($fetch_files as $key => $resobject) {
				foreach ($tag_assigned_fmap as $tagname => $my_arr) {
					foreach ($my_arr as $cindex => $res_key) {
						if ($key == $res_key)  {
							continue 3;
						}
					}
				}
				$tag_uncategorized_fmap[$uncateg_name][] = $key;
			}

			if (count($tag_assigned_fmap) > 0) {

				foreach ($tag_assigned_fmap as $tagname => $my_arr) {

					//print $tagname;

					print '<table class="border" width="100%">
						<tr class="position-subtable">
						<td colspan="<?php echo $coldisplay ?>">
						<table width="100%" class="elb-subtable">
						<thead>
						<tr>
						<th colspan="2" align="left">
						<a href="" onclick="toggleSubtable(this); return false;" class="toggle-link expanded">
						<span class="ui-icon ui-icon-triangle-1-se"></span>';
					print $tagname;
					print '</a>
						</th>
						</tr>
						</thead>		
						<tbody>
							<tr>
								<td class="nobottom" colspan="2">';

					print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';
					print '<thead>';
					print '<tr class="liste_titre malign" width="100%">';
					print '<td width="30" align="center" class="td-file-nr">'.$langs->trans("Nr").'</td>';
					print '<td class="td-file-name">'.$langs->trans("File").'</td>';
					print '<td class="td-file-desc">'.$langs->trans("Description").'</td>';
					print '<td class="td-file-rev">'.$langs->trans("Revision").'</td>';
					print '<td class="td-file-size">'.$langs->trans("Size").'</td>';
					print '<td width="110" class="td-file-modif">'.$langs->trans("Modified").'</td>';
					print '<td class="td-file-user">'.$langs->trans("User").'</td>';
					if ($toolbox){
						print '<td class="td-file-toolbox"></td>';
					}
					print '</tr>';
					print '</thead>';

					print '<tbody>';

					foreach ($my_arr as $key => $files_res_key) {

						$obj = $fetch_files[$files_res_key];

						($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

						include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');

						$this->getFileVersions($obj->fmrowid, $toolbox);

					}

					print '</tbody>';
					print "</table>\n";


					print '
								</td>
							</tr>
							</tbody>
							</table>
							</td>
							</tr>
							</table>';
				}
			}


			if (count($tag_uncategorized_fmap) > 0) {

				foreach ($tag_uncategorized_fmap as $tagname => $my_arr) {

					//print $tagname;

					print '<table class="border" width="100%">
						<tr class="position-subtable">
						<td colspan="<?php echo $coldisplay ?>">
						<table width="100%" class="elb-subtable">
						<thead>
						<tr>
						<th colspan="2" align="left">
						<a href="" onclick="toggleSubtable(this); return false;" class="toggle-link expanded">
						<span class="ui-icon ui-icon-triangle-1-se"></span>';
					print $tagname;
					print '</a>
						</th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td class="nobottom" colspan="2">';

					print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';
					print '<thead>';
					print '<tr class="liste_titre malign" width="100%">';
					print '<td width="30" align="center" class="td-file-nr">'.$langs->trans("Nr").'</td>';
					print '<td class="td-file-name">'.$langs->trans("File").'</td>';
					print '<td class="td-file-desc">'.$langs->trans("Description").'</td>';
					print '<td class="td-file-rev">'.$langs->trans("Revision").'</td>';
					print '<td class="td-file-size">'.$langs->trans("Size").'</td>';
					print '<td width="110" class="td-file-modif">'.$langs->trans("Modified").'</td>';
					print '<td class="td-file-user">'.$langs->trans("User").'</td>';
					if ($toolbox){
						print '<td class="td-file-toolbox"></td>';
					}
					print '</tr>';
					print '</thead>';

					print '<tbody>';

					foreach ($my_arr as $key => $files_res_key) {

						$obj = $fetch_files[$files_res_key];

						($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

						include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');

						$this->getFileVersions($obj->fmrowid, $toolbox);

					}

					print '</tbody>';
					print "</table>\n";


					print '
								</td>
							</tr>
							</tbody>
							</table>
							</td>
							</tr>
							</table>';
				}
			}
		} else {

			print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';
			print '<thead>';
			print '<tr class="liste_titre malign" width="100%">';
			print '<td width="30" align="center" class="td-file-nr">'.$langs->trans("Nr").'</td>';
			print '<td class="td-file-name">'.$langs->trans("File").'</td>';
			print '<td class="td-file-desc">'.$langs->trans("Description").'</td>';
			print '<td class="td-file-rev">'.$langs->trans("Revision").'</td>';
			print '<td class="td-file-size">'.$langs->trans("Size").'</td>';
			print '<td width="110" class="td-file-modif">'.$langs->trans("Modified").'</td>';
			print '<td class="td-file-user">'.$langs->trans("User").'</td>';
			if ($toolbox){
				print '<td class="td-file-toolbox"></td>';
			}
			print '</tr>';
			print '</thead>';

			print '<tbody>';
			//$maxfilenamelength = 30; //define max length of a file name
			$var = false;

			foreach ($fetch_files as $obj) {
				$var=!$var;

				if($action2 == 'editfile' &&  $fileid == $obj->fmrowid) {
					static $single_modif;
					if($single_modif) {
						$modef=false;
					} else {
						$single_modif = true;
						$modef=true;
					}
				} else {
					$modef=false;
				}

				include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');

				$this->getFileVersions($obj->fmrowid, $toolbox);
			}
			print '</tbody>';
			print "</table>\n";
		}
	}

	function getUploadedFilesbyRev($object_type=null, $object_id=null, $toolbox=1, $tag_map=false, $search_files=null)
	{
		global $bc, $langs, $conf, $db, $user;
	
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.tags as fmtags";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE 1=1 ";
		if(!empty($object_type)) {
			$sql.= " AND fm.object_type = '".$db->escape($object_type)."'";
		}
		if(!empty($object_id)) {
			$sql.= " AND fm.object_id = ".$db->escape($object_id);
		}
		if(isset($search_files)) {
			if(count($search_files)==0) {
// 				$list="0";
			} else {
				$list=implode(",", $search_files);
				$sql.= " AND fm.rowid IN (".$list.")";
			}
		}
		$sql.= " ORDER BY f.name";
	
		dol_syslog(get_class($this)."::getUploadedFilesByRev sql=".$sql);

		$fetch_all_files = ElbCommonManager::queryList($sql);

        if ($fetch_all_files !==false) {
        	self::renderFilesByRevision($fetch_all_files, $toolbox);
        }
	}

	function renderFilesByRevision($fetch_all_files,$toolbox=1,$restictDeleteFile=false)
    {
		global $bc, $langs, $conf, $db, $user;

		$action2 = GETPOST('action2');
		$fileid = GETPOST('rowid');

		// array with revision
		$file_with_rev = array();

		// array without revision
		$file_without_rev = array();

		// populate array of files with revision
		foreach ($fetch_all_files as $ind => $file) {
			if ($file->fmrevision) {
				$file_with_rev[$file->fmrevision][] = $file;
			}
		}

		// sort by latest revision
		if (count($file_with_rev)) {
			krsort($file_with_rev);
		}

		// populate array of files without revision
		foreach ($fetch_all_files as $ind => $file) {
			if (empty($file->fmrevision)) {
				$file_without_rev['no_revision'][] = $file;
			}
		}

		$counter=0;

		if (count($file_with_rev) > 0)
		{
			foreach ($file_with_rev as $rev => $my_arr)
			{
				if (!$counter)
				{
					$a_class = 'toggle-link expanded';
					$span_class = 'ui-icon ui-icon-triangle-1-se';
					$display = 'style="display: table-row-group;"';
				}
				else
				{
					$a_class = 'toggle-link';
					$span_class = 'ui-icon ui-icon-triangle-1-e';
					$display = ' style="display: none;"';
				}

				print '<table class="border" width="100%">
					<tr class="position-subtable">
					<td colspan="<?php echo $coldisplay ?>">
					<table width="100%" class="elb-subtable">
					<thead>
					<tr>
					<th colspan="2" align="left" class="oldv-enable">';

				print '<a href="" onclick="toggleSubtable(this); return false;" class="' . $a_class . '">
				<span class="' . $span_class . '"></span>';
				print $langs->trans('Revision').' '.$rev;
				print '</a>
					</th>
					</tr>
					</thead>		
					<tbody ' . $display . '>
						<tr>
							<td class="nobottom" colspan="2">';
				print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';
				print '<thead>';
				print '<tr class="liste_titre malign" width="100%">';
				print '<td width="30" align="center" class="td-file-nr">'.$langs->trans("Nr").'</td>';
				print '<td class="td-file-name">'.$langs->trans("File").'</td>';
				print '<td class="td-file-desc">'.$langs->trans("Description").'</td>';
				print '<td class="td-file-rev">'.$langs->trans("Revision").'</td>';
				print '<td class="td-file-size">'.$langs->trans("Size").'</td>';
				print '<td width="110" class="td-file-modif">'.$langs->trans("Modified").'</td>';
				print '<td class="td-file-user">'.$langs->trans("User").'</td>';
				if ($toolbox){
					print '<td class="td-file-toolbox"></td>';
				}
				print '</tr>';
				print '</thead>';

				print '<tbody>';

				foreach ($my_arr as $key => $files_res_key)
				{
					$obj = $files_res_key;

					($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

					include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');


					$this->getFileVersions($obj->fmrowid, $toolbox);
				}

				print '</tbody>';
				print "</table>\n";

				print '	</td>
						</tr>
						</tbody>
						</table>
						</td>
						</tr>
						</table>';
				$counter++;
			}
		}

		if (count($file_without_rev) > 0)
		{
			foreach ($file_without_rev as $without_ref => $my_arr)
			{
				if (!$counter)
				{
					$a_class = 'toggle-link expanded';
					$span_class = 'ui-icon ui-icon-triangle-1-se';
					$display = 'style="display: table-row-group;"';
				}
				else
				{
					$a_class = 'toggle-link';
					$span_class = 'ui-icon ui-icon-triangle-1-e';
					$display = ' style="display: none;"';
				}

				print '<table class="border" width="100%">
					<tr class="position-subtable">
					<td colspan="<?php echo $coldisplay ?>">
					<table width="100%" class="elb-subtable">
					<thead>
					<tr>
					<th colspan="2" align="left" class="oldv-enable">';
				print '<a href="" onclick="toggleSubtable(this); return false;" class="' . $a_class . '">
					<span class="' . $span_class . '"></span>';
				print $langs->trans('WithoutRevision');
				print '</a>
					</th>
					</tr>
					</thead>
					<tbody ' . $display . '>
						<tr>
							<td class="nobottom" colspan="2">';

				print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';
				print '<thead>';
				print '<tr class="liste_titre malign" width="100%">';
				print '<td width="30" align="center" class="td-file-nr">'.$langs->trans("Nr").'</td>';
				print '<td class="td-file-name">'.$langs->trans("File").'</td>';
				print '<td class="td-file-desc">'.$langs->trans("Description").'</td>';
				print '<td class="td-file-rev">'.$langs->trans("Revision").'</td>';
				print '<td class="td-file-size">'.$langs->trans("Size").'</td>';
				print '<td width="110" class="td-file-modif">'.$langs->trans("Modified").'</td>';
				print '<td class="td-file-user">'.$langs->trans("User").'</td>';
				if ($toolbox){
					print '<td class="td-file-toolbox"></td>';
				}
				print '</tr>';
				print '</thead>';

				print '<tbody>';

				foreach ($my_arr as $key => $files_res_key)
				{
					$obj = $files_res_key;

					($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

					include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');

					$this->getFileVersions($obj->fmrowid, $toolbox);
				}

				print '</tbody>';
				print "</table>\n";

				print '
							</td>
						</tr>
						</tbody>
						</table>
						</td>
						</tr>
						</table>';
				$counter++;
			}
		}
	}
	
	/**
	 *  Update file attached in the position
	 */
	function actionPositionUpdateFile($linemode=true)
    {
		global $langs, $conf, $db, $user;
	
		$elbfilemap = new ELbFileMapping($db);
			
		$id = GETPOST('id', 'int');
		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
		if ($linemode){
			$lineid = GETPOST('lineid');
		}
		$path = GETPOST('path', 'alpha');
		$description = GETPOST('description', 'alpha');
		$rev = $this->sanitizeText(GETPOST('frev'));
		$filemapid = GETPOST('filemapid', 'int');
		$tags = GETPOST('tags');
		
		$db->begin();
		
		$res = $elbfilemap->fetch($filemapid);

		$elbfilemap->oldcopy = clone $elbfilemap;
			
		if ($res > 0) {
			
			if (!empty($elbfilemap->path) && empty($path)) {
				$db->rollback();
				setEventMessage($langs->trans("FileNotUpdated"), 'errors');
				self::headerLocationAfterOperation($linemode, $id, $lineid, $filemapid, $facid, $socid);
				exit;
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
			$result = $elbfilemap->update(); 
				
			if ($result > 0) {
				$db->commit();
				setEventMessage($langs->trans("FileSuccessfullyUpdated"), 'mesgs');

				//check and store tags
				$tags_list=array();
//				if(count($tags)>0) {
//					$all_tags = Categorie::getFileTags();
//					foreach ($tags as $tag) {
//						if(!in_array($tag, $all_tags)) {
//							$sql="INSERT INTO ".MAIN_DB_PREFIX."categorie SET label='".$db->escape($tag)."', type=".Categorie::TYPE_ELB_FILE;
//							ElbCommonManager::execute($sql);
//						}
//					}
//				}
				
//				if(!empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
//					//update file in Solr
//					$elbfile = new ELbFile();
//					$elbfile->fetch($elbfilemap->fk_fileid);
//					ElbSolrUtil::add_to_search_index($elbfile, $elbfilemap, $tags);
//				}

			} else {
				$db->rollback();
				setEventMessage($langs->trans("FileNotUpdated"), 'errors');
				unset($_SESSION['dol_events']['mesgs']);
				self::headerLocationAfterOperation($linemode, $id, $lineid, $filemapid, $facid, $socid);
				exit;
			}
		} else {
			$db->rollback();
			setEventMessage($langs->trans("FileNotUpdated"), 'errors');
			unset($_SESSION['dol_events']['mesgs']);
			self::headerLocationAfterOperation($linemode, $id, $lineid, $filemapid, $facid, $socid);
			exit;
		}
	
		self::headerLocationAfterOperation($linemode, $id, $lineid, $filemapid, $facid, $socid);
		exit;
	}
	
	/**
	 *  Upload new version for file in the position
	 */
	function actionPositionUploadFileNewVersion($linemode=true)
    {
		global $db, $langs, $user, $conf;
	
		$id = GETPOST('id');
		$facid = GETPOST('facid');
		$socid = GETPOST('socid');
		$lineid = GETPOST('lineid');
		$ufmid = GETPOST('ufmid', 'int');
		$ufmnvfile = 'ufmnvfile'.$ufmid;
		$description = GETPOST('description', 'alpha');
		$fsubrev = $this->sanitizeText(GETPOST('fsubrev'));
			
        if (empty($_FILES[$ufmnvfile]["name"])) {
			setEventMessage($langs->trans("FileMissing"), 'errors');
		} elseif (isset($_FILES[$ufmnvfile]))	{

			$db->begin();
				
			$fileName = $_FILES[$ufmnvfile]["name"];
			$output_buffer_dir = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/';
			$output_buffer = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/'.$fileName;
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
						
					$elbfilemap = new ELbFileMapping($db);
					$elbfilemap_fetch = $elbfilemap->fetch($ufmid);

					if ($elbfilemap_fetch > 0) {
						
						// get old file map properties
						$old_fmid = $elbfilemap->id;
						$old_object_type = $elbfilemap->object_type;
						$old_object_id = $elbfilemap->object_id;
						$old_object_user = $elbfilemap->user;
						$old_object_created_date = $elbfilemap->created_date;
						
						// create new file map
						$fm_new = new ELbFileMapping($db);
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
							
							$fetch_all_subversions = $this->fetchFileVersionByParentFile($old_fmid);
							
							if (!empty($fetch_all_subversions)) {
								foreach ($fetch_all_subversions as $fmid) {
									$change_pfm = new ELbFileMapping($db);
									$change_pfm->fetch($fmid);
									$change_pfm->created_date = $db->jdate($change_pfm->created_date);
									$change_pfm->parent_file = $fm_new_create;
									$change_pfm->active = ELbFileMapping::FILE_REVISION;
									$change_pfm->update();		
									$files_to_reindex[]=$change_pfm;
								}
							}
							
							// update old file map in the db
							$elbfilemap->active	   = null;
							$elbfilemap->created_date = $db->jdate($elbfilemap->created_date);
							$elbfilemap->parent_file = $fm_new_create;
							$elbfilemap_update = $elbfilemap->update();
							$files_to_reindex[]=$elbfilemap;
							
							if ($elbfilemap_update > 0) {
								
								$res = dol_move($output_buffer, $output_dir.$newfileid.'.'.$ext);
								
								//send file to solr index
								if($res && !empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
									foreach($files_to_reindex as $file_to_reindex) {
										$elbfile=new ELbFile($this->db);
										$elbfile->fetch($file_to_reindex->fk_fileid);
										$res = $res && ElbSolrUtil::add_to_search_index($elbfile, $file_to_reindex);
									}
								}
								
								if ($res)
								{	
									$db->commit();
									setEventMessage($langs->trans("FileVersionSuccessfullyCreated"), 'mesgs');
									
								} else {
									$db->rollback();
								}
							} else {
								$db->rollback();
							}	
						} else {
							$db->rollback();
						}
					} else {
						$db->rollback();
					}
				} else {
					$db->rollback();
				}
			} else {
				$db->rollback();
			}
		}
		
		self::headerLocationAfterOperation($linemode, $id, $lineid, $ufmid, $facid, $socid);
		exit;
	}
	
	/**
	 *
	 * @param 	string 		$module 		use when link a file - module name: elb, commande, propal (in href it will be modulepart)
	 * @param 	string 		$prefix			use as a folder seperator DOL_DATA_ROOT/$module_dir/$prefix/$objectid/$lineid
	 * @param	int 		$objectid		object id
	 * @param 	int			$lineid			line id
	 * @param 	int 		$userid			user id (who uplods file)
	 * @param 	int 		$showstatus  	result of uploading files (it will not show if page reloads after success
	 * @param 	bool 		$multiupload  	allow multiupload (true/false)
	 * @return 								print out upload button with js
	 */
	static function showMultiUploadButton($object_type, $object_id, $multiupload=true, $attach_external=true, $id_add=false) {
	
		global $langs, $db, $user;
	
		$out = '';
		//$protocol = ($_SERVER['REQUEST_SCHEME'] == 'https') ? 'https' : 'http';
		//$protocol =  'https';
		(strpos('https', strtolower($_SERVER["SERVER_PROTOCOL"]) !== false)) ? $protocol='https' : $protocol='http';
		$upload_handler = DOL_URL_ROOT. '/elbmultiupload/elbupload.php';

		$out .= '<script>
				/*!
				 * jQuery Upload File Plugin
				 * version: 3.1.8
				 * @requires jQuery v1.5 or later & form plugin
				 * Copyright (c) 2013 Ravishanker Kusuma
				 * http://hayageek.com/
				 */
				(function(e){if(e.fn.ajaxForm==undefined){e.getScript("'.DOL_URL_ROOT.'/elbmultiupload/js/jquery.form.js'.'")}var t={};t.fileapi=e("<input type=\'file\'/>").get(0).files!==undefined;t.formdata=window.FormData!==undefined;
						e.fn.uploadFile=function(n){function a(){if(r.afterUploadAll&&!u){u=true;(function e(){if(s.sCounter!=0&&s.sCounter+s.fCounter==s.tCounter){r.afterUploadAll(s);u=false}else window.setTimeout(e,100)})()}}function f(t,n,r){r.on("dragenter",function(t){t.stopPropagation();t.preventDefault();e(this).css("border","2px solid #A5A5C7")});r.on("dragover",function(e){e.stopPropagation();e.preventDefault()});r.on("drop",function(r){e(this).css("border","2px dotted #A5A5C7");r.preventDefault();t.errorLog.html("");var i=r.originalEvent.dataTransfer.files;if(!n.multiple&&i.length>1){if(n.showError)e("<div style=\'color:red;\'>"+n.multiDragErrorStr+"</div>").appendTo(t.errorLog);return}if(n.onSelect(i)==false)return;h(n,t,i)});e(document).on("dragenter",function(e){e.stopPropagation();e.preventDefault()});e(document).on("dragover",function(e){e.stopPropagation();e.preventDefault();r.css("border","2px dotted #A5A5C7")});e(document).on("drop",function(e){e.stopPropagation();e.preventDefault();r.css("border","2px dotted #A5A5C7")})}function l(e){var t="";var n=e/1024;if(parseInt(n)>1024){var r=n/1024;t=r.toFixed(2)+" MB"}else{t=n.toFixed(2)+" KB"}return t}function c(t){var n=[];if(jQuery.type(t)=="string"){n=t.split("&")}else{n=e.param(t).split("&")}var r=n.length;var i=[];var s,o;for(s=0;s<r;s++){n[s]=n[s].replace(/\+/g," ");o=n[s].split("=");i.push([decodeURIComponent(o[0]),decodeURIComponent(o[1])])}return i}function h(t,n,r){for(var i=0;i<r.length;i++){if(!p(n,t,r[i].name)){if(t.showError)e("<div style=\'color:red;\'><b>"+r[i].name+"</b> "+t.extErrorStr+t.allowedTypes+"</div>").appendTo(n.errorLog);continue}if(t.maxFileSize!=-1&&r[i].size>t.maxFileSize){if(t.showError)e("<div style=\'color:red;\'><b>"+r[i].name+"</b> "+t.sizeErrorStr+l(t.maxFileSize)+"</div>").appendTo(n.errorLog);continue}if(t.maxFileCount!=-1&&n.selectedFiles>=t.maxFileCount){if(t.showError)e("<div style=\'color:red;\'><b>"+r[i].name+"</b> "+t.maxFileCountErrorStr+t.maxFileCount+"</div>").appendTo(n.errorLog);continue}n.selectedFiles++;var s=t;var o=new FormData;var u=t.fileName.replace("[]","");o.append(u,r[i]);var a=t.formData;if(a){var f=c(a);for(var h=0;h<f.length;h++){if(f[h]){o.append(f[h][0],f[h][1])}}}s.fileData=o;var d=new g(n,t);var v="";if(t.showFileCounter)v=n.fileCounter+t.fileCounterStyle+r[i].name;else v=r[i].name;d.filename.html(v);var m=e("<form style=\'display:block; position:absolute;left: 150px;\' class=\'"+n.formGroup+"\' method=\'"+t.method+"\' action=\'"+t.url+"\' enctype=\'"+t.enctype+"\'></form>");m.appendTo("body");var b=[];b.push(r[i].name);y(m,s,d,b,n,r[i]);n.fileCounter++}}function p(e,t,n){var r=t.allowedTypes.toLowerCase().split(",");var i=n.split(".").pop().toLowerCase();if(t.allowedTypes!="*"&&jQuery.inArray(i,r)<0){return false}return true}function d(e,t){if(e){t.show();var n=new FileReader;n.onload=function(e){t.attr("src",e.target.result)};n.readAsDataURL(e)}}function v(t,n){if(t.showFileCounter){var r=e(".ajax-file-upload-filename").length;n.fileCounter=r+1;e(".ajax-file-upload-filename").each(function(n,i){var s=e(this).html().split(t.fileCounterStyle);var o=parseInt(s[0])-1;var u=r+t.fileCounterStyle+s[1];e(this).html(u);r--})}}function m(n,r,i,s){var o="ajax-upload-id-"+(new Date).getTime();var u=e("<form method=\'"+i.method+"\' action=\'"+i.url+"\' enctype=\'"+i.enctype+"\'></form>");var a="<input type=\'file\' id=\'"+o+"\' name=\'"+i.fileName+"\' accept=\'"+i.acceptFiles+"\'/>";if(i.multiple){if(i.fileName.indexOf("[]")!=i.fileName.length-2){i.fileName+="[]"}a="<input type=\'file\' id=\'"+o+"\' name=\'"+i.fileName+"\' accept=\'"+i.acceptFiles+"\' multiple/>"}var f=e(a).appendTo(u);f.change(function(){n.errorLog.html("");var o=i.allowedTypes.toLowerCase().split(",");var a=[];if(this.files){for(b=0;b<this.files.length;b++){a.push(this.files[b].name)}if(i.onSelect(this.files)==false)return}else{var f=e(this).val();var l=[];a.push(f);if(!p(n,i,f)){if(i.showError)e("<div style=\'color:red;\'><b>"+f+"</b> "+i.extErrorStr+i.allowedTypes+"</div>").appendTo(n.errorLog);return}l.push({name:f,size:"NA"});if(i.onSelect(l)==false)return}v(i,n);s.unbind("click");u.hide();m(n,r,i,s);u.addClass(r);if(t.fileapi&&t.formdata){u.removeClass(r);var c=this.files;h(i,n,c)}else{var d="";for(var b=0;b<a.length;b++){if(i.showFileCounter)d+=n.fileCounter+i.fileCounterStyle+a[b]+"<br>";else d+=a[b]+"<br>";n.fileCounter++}if(i.maxFileCount!=-1&&n.selectedFiles+a.length>i.maxFileCount){if(i.showError)e("<div style=\'color:red;\'><b>"+d+"</b> "+i.maxFileCountErrorStr+i.maxFileCount+"</div>").appendTo(n.errorLog);return}n.selectedFiles+=a.length;var w=new g(n,i);w.filename.html(d);y(u,i,w,a,n,null)}});if(i.nestedForms){u.css({margin:0,padding:0});s.css({position:"relative",overflow:"hidden",cursor:"default"});f.css({position:"absolute",cursor:"pointer",top:"0px",width:"100%",height:"100%",left:"0px","z-index":"100",opacity:"0.0",filter:"alpha(opacity=0)","-ms-filter":"alpha(opacity=0)","-khtml-opacity":"0.0","-moz-opacity":"0.0"});u.appendTo(s)}else{u.appendTo(e("body"));u.css({margin:0,padding:0,display:"block",position:"absolute",left:"-250px"});if(navigator.appVersion.indexOf("MSIE ")!=-1){s.attr("for",o)}else{s.click(function(){f.click()})}}}function g(t,n){this.statusbar=e("<div class=\'ajax-file-upload-statusbar\'></div>").width(n.statusBarWidth);this.preview=e("<img class=\'ajax-file-upload-preview\'></img>").width(n.previewWidth).height(n.previewHeight).appendTo(this.statusbar).hide();this.filename=e("<div class=\'ajax-file-upload-filename\'></div>").appendTo(this.statusbar);this.progressDiv=e("<div class=\'ajax-file-upload-progress\'>").appendTo(this.statusbar).hide();this.progressbar=e("<div class=\'ajax-file-upload-bar "+t.formGroup+"\'></div>").appendTo(this.progressDiv);this.abort=e("<div class=\'ajax-file-upload-red ajax-file-upload-abort "+t.formGroup+"\'>"+n.abortStr+"</div>").appendTo(this.statusbar).hide();this.cancel=e("<div class=\'ajax-file-upload-red ajax-file-upload-cancel "+t.formGroup+"\'>"+n.cancelStr+"</div>").appendTo(this.statusbar).hide();this.done=e("<div class=\'ajax-file-upload-green\'>"+n.doneStr+"</div>").appendTo(this.statusbar).hide();this.download=e("<div class=\'ajax-file-upload-green\'>"+n.downloadStr+"</div>").appendTo(this.statusbar).hide();this.del=e("<div class=\'ajax-file-upload-red\'>"+n.deletelStr+"</div>").appendTo(this.statusbar).hide();if(n.showQueueDiv)e("#"+n.showQueueDiv).append(this.statusbar);else t.errorLog.after(this.statusbar);return this}function y(n,r,i,s,o,u){var f=null;var l={cache:false,contentType:false,processData:false,forceSync:false,type:r.method,data:r.formData,formData:r.fileData,dataType:r.returnType,beforeSubmit:function(e,t,u){if(r.onSubmit.call(this,s)!=false){var f=r.dynamicFormData();if(f){var l=c(f);if(l){for(var h=0;h<l.length;h++){if(l[h]){if(r.fileData!=undefined)u.formData.append(l[h][0],l[h][1]);else u.data[l[h][0]]=l[h][1]}}}}o.tCounter+=s.length;a();return true}i.statusbar.append("<div style=\'color:red;\'>"+r.uploadErrorStr+"</div>");i.cancel.show();n.remove();i.cancel.click(function(){i.statusbar.remove();r.onCancel.call(o,s,i);o.selectedFiles-=s.length;v(r,o)});return false},beforeSend:function(e,n){i.progressDiv.show();i.cancel.hide();i.done.hide();if(r.showAbort){i.abort.show();i.abort.click(function(){e.abort();o.selectedFiles-=s.length})}if(!t.formdata){i.progressbar.width("5%")}else i.progressbar.width("1%")},uploadProgress:function(e,t,n,s){if(s>98)s=98;var o=s+"%";if(s>1)i.progressbar.width(o);if(r.showProgress){i.progressbar.html(o);i.progressbar.css("text-align","center")}},success:function(t,u,a){if(r.returnType=="json"&&e.type(t)=="object"&&t.hasOwnProperty(r.customErrorKeyStr)){i.abort.hide();var f=t[r.customErrorKeyStr];r.onError.call(this,s,200,f,i);if(r.showStatusAfterError){i.progressDiv.hide();i.statusbar.append("<span style=\'color:red;\'>ERROR: "+f+"</span>")}else{i.statusbar.hide();i.statusbar.remove()}o.selectedFiles-=s.length;n.remove();o.fCounter+=s.length;return}o.responses.push(t);i.progressbar.width("100%");if(r.showProgress){i.progressbar.html("100%");i.progressbar.css("text-align","center")}i.abort.hide();r.onSuccess.call(this,s,t,a,i);if(r.showStatusAfterSuccess){if(r.showDone){i.done.show();i.done.click(function(){i.statusbar.hide("slow");i.statusbar.remove()})}else{i.done.hide()}if(r.showDelete){i.del.show();i.del.click(function(){i.statusbar.hide().remove();if(r.deleteCallback)r.deleteCallback.call(this,t,i);o.selectedFiles-=s.length;v(r,o)})}else{i.del.hide()}}else{i.statusbar.hide("slow");i.statusbar.remove()}if(r.showDownload){i.download.show();i.download.click(function(){if(r.downloadCallback)r.downloadCallback(t)})}n.remove();o.sCounter+=s.length},error:function(e,t,u){i.abort.hide();if(e.statusText=="abort"){i.statusbar.hide("slow").remove();v(r,o)}else{r.onError.call(this,s,t,u,i);if(r.showStatusAfterError){i.progressDiv.hide();i.statusbar.append("<span style=\'color:red;\'>ERROR: "+u+"</span>")}else{i.statusbar.hide();i.statusbar.remove()}o.selectedFiles-=s.length}n.remove();o.fCounter+=s.length}};if(r.showPreview&&u!=null){if(u.type.toLowerCase().split("/").shift()=="image")d(u,i.preview)}if(r.autoSubmit){n.ajaxSubmit(l)}else{if(r.showCancel){i.cancel.show();i.cancel.click(function(){n.remove();i.statusbar.remove();r.onCancel.call(o,s,i);o.selectedFiles-=s.length;v(r,o)})}n.ajaxForm(l)}}var r=e.extend({url:"",method:"POST",enctype:"multipart/form-data",formData:null,returnType:null,allowedTypes:"*",acceptFiles:"*",fileName:"file",formData:{},dynamicFormData:function(){return{}},maxFileSize:-1,maxFileCount:-1,multiple:true,dragDrop:true,autoSubmit:true,showCancel:true,showAbort:true,showDone:true,showDelete:false,showError:true,showStatusAfterSuccess:true,showStatusAfterError:true,showFileCounter:true,fileCounterStyle:"). ",showProgress:false,nestedForms:true,showDownload:false,onLoad:function(e){},onSelect:function(e){return true},onSubmit:function(e,t){},onSuccess:function(e,t,n,r){},onError:function(e,t,n,r){},onCancel:function(e,t){},downloadCallback:false,deleteCallback:false,afterUploadAll:false,uploadButtonClass:"ajax-file-upload",dragDropStr:"<span><b>Drag & Drop Files</b></span>",abortStr:"Abort",cancelStr:"Cancel",deletelStr:"Delete",doneStr:"Done",multiDragErrorStr:"Multiple File Drag & Drop is not allowed.",extErrorStr:"is not allowed. Allowed extensions: ",sizeErrorStr:"is not allowed. Allowed Max size: ",uploadErrorStr:"Upload is not allowed",maxFileCountErrorStr:" is not allowed. Maximum allowed files are:",downloadStr:"Download",customErrorKeyStr:"jquery-upload-file-error",showQueueDiv:false,statusBarWidth:500,dragdropWidth:500,showPreview:false,previewHeight:"auto",previewWidth:"100%"},n);this.fileCounter=1;this.selectedFiles=0;this.fCounter=0;this.sCounter=0;this.tCounter=0;var i="ajax-file-upload-"+(new Date).getTime();this.formGroup=i;this.hide();this.errorLog=e("<div></div>");this.after(this.errorLog);this.responses=[];if(!t.formdata){r.dragDrop=false}if(!t.formdata){r.multiple=false}var s=this;var o=e("<div>"+e(this).html()+"</div>");e(o).addClass(r.uploadButtonClass);(function b(){if(e.fn.ajaxForm){if(r.dragDrop){var t=e(\'<div class="ajax-upload-dragdrop" style="vertical-align:top;"></div>\').width(r.dragdropWidth);e(s).before(t);e(t).append(o);e(t).append(e(r.dragDropStr));f(s,r,t)}else{e(s).before(o)}r.onLoad.call(this,s);m(s,i,r,o)}else window.setTimeout(b,10)})();this.startUpload=function(){e("."+this.formGroup).each(function(t,n){if(e(this).is("form"))e(this).submit()})};this.getFileCount=function(){return s.selectedFiles};this.stopUpload=function(){e(".ajax-file-upload-abort").each(function(t,n){if(e(this).hasClass(s.formGroup))e(this).click()})};this.cancelAll=function(){e(".ajax-file-upload-cancel").each(function(t,n){if(e(this).hasClass(s.formGroup))e(this).click()})};this.update=function(t){r=e.extend(r,t)};this.createProgress=function(e){var t=new g(this,r);t.progressDiv.show();t.progressbar.width("100%");t.filename.html(s.fileCounter+r.fileCounterStyle+e);s.fileCounter++;s.selectedFiles++;if(r.showDownload){t.download.show();t.download.click(function(){if(r.downloadCallback)r.downloadCallback.call(s,[e])})}t.del.show();t.del.click(function(){t.statusbar.hide().remove();var n=[e];if(r.deleteCallback)r.deleteCallback.call(this,n,t);s.selectedFiles-=1;v(r,s)})};this.getResponses=function(){return this.responses};var u=false;return this}})(jQuery)
				</script>';
		$out .= '<div id="mulitplefileuploader'.$id_add.'">'.$langs->trans("UploadFiles").'</div><br/>';
		$out .= '<script>
					$(document).ready(function()
					{
					var formData = $("form").serializeArray();
					var settings = {
						url: "'.$upload_handler.'",
						method: "POST",
						//allowedTypes:"jpg,jpeg,png,gif,doc,pdf,zip",
						fileName: "elb_file",';
		if ($multiupload)
			$out .= '		multiple: true,';
		else
			$out .= '		multiple: false,';
	
		$out .= '		formData: {';
		$out .=	 '   		object_type: "'.$object_type.'",';
		$out .=	 '   		object_id: '.$object_id.',';
		$out .=  '			user_id:  '.$user->id.'
					},
					afterUploadAll:function() {';
		if ($object_type != 'suppdlvid') {		
			$out .=  '			location.reload();';
		}
		$out .=  '	},
					onSuccess:function(files,data,xhr)
					{
						$("#status").html("<font color=\'green\'>Upload is success</font>");
					},
					onError: function(files,status,errMsg)
					{
						$("#status").html("<font color=\'red\'>Upload is Failed</font>");
					}
				};
				$("#mulitplefileuploader'.$id_add.'").uploadFile(settings);
			});
			</script>';
		print $out;
	}
	
	function sanitizeText($text)
    {
		$text = trim($text);
		$text = trim($text, '.');
		$ret = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $text);
		return $ret;
	}
	
	function fetchFileVersionByParentFile($parent_file)
    {
		global $langs, $conf, $db;
				
		// Check parameters
		if ( empty($parent_file) || !is_numeric($parent_file))
		{
			return false;
		}
		
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.= ' WHERE parent_file = '.$parent_file;
		
		$resql = $db->query($sql);
		
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;
			$ids = false;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				$ids[$i] = $obj->rowid;
				$i++;
			}
			return $ids;
		} else {
			return false;
		}
	}
	
	function activateRevision($fileid)
    {
		global $db, $user;
	
		$db->begin();
		
		$fm_toactivate = new ELbFileMapping($db);
		$fm_toactivate_fetch = $fm_toactivate->fetch($fileid);
		
		if ($fm_toactivate_fetch > 0) {
		
			$fm_toactivate_pf = $fm_toactivate->parent_file;
			$fm_toactivate->parent_file = null;
			$fm_toactivate->active=ELbFileMapping::FILE_ACTIVE;
			$fm_toactivate->created_date = dol_now();
			$fm_toactivate->user = $user->id;
			$fm_toactivate_update = $fm_toactivate->update();
			
			if ($fm_toactivate_update > 0) {
				
				$fm_to_deactivate = new ELbFileMapping($db);
				$fm_to_deactivate_fetch = $fm_to_deactivate->fetch($fm_toactivate_pf);
				
				if ($fm_to_deactivate_fetch > 0) {
					$fm_to_deactivate->parent_file = $fileid;
					$fm_to_deactivate->active = ELbFileMapping::FILE_REVISION;
					$fm_to_deactivate->created_date	= $db->jdate($fm_to_deactivate->created_date);
					$fm_to_deactivate_update = $fm_to_deactivate->update();
					
					if ($fm_to_deactivate_update > 0) {
						
						$fetch_all_subversions = $this->fetchFileVersionByParentFile($fm_to_deactivate->id);
							
						if (!empty($fetch_all_subversions)) {
							foreach ($fetch_all_subversions as $fmid) {
								$change_pfm = new ELbFileMapping($db);
								$change_pfm->fetch($fmid);
								$change_pfm->parent_file = $fileid;
								$change_pfm->active = ELbFileMapping::FILE_REVISION;
								$change_pfm->created_date	= $db->jdate($change_pfm->created_date);
								$res = $change_pfm->update();
								if (!($res >0)) {
									$db->rollback();
									return -1;
								}
							}
						}
						
						// commit change
						$db->commit();
						return 1;
							
					} else {
						$db->rollback();
						return -1;
					}
				} else {
					$db->rollback();
					return -1;
				}
			} else {
				$db->rollback();
				return -1;
			}
		} else {
			$db->rollback();
			return -1;
		}
	}
	
	/**
	 * Activate revision (set revision as current/active version)
	 */
	function actionPositionActivateFile($linemode=true)
    {
		global $langs;
		
		$fileid = GETPOST('fileid', 'int');
		$id = GETPOST('id', 'int');
		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
		if ($linemode)
			$lineid = GETPOST('lineid', 'int');
		
		$res = $this->activateRevision($fileid);
		
		if ($res > 0) {
			setEventMessage($langs->trans("RevisionIsActivated"));
		} else {
			setEventMessage($langs->trans("RevisionNotActivated"), 'errors');
		}
		
		self::headerLocationAfterOperation($linemode, $id, $lineid, $fileid, $facid, $socid);
		exit;
	}
	
	/**
	 *  Remove/delete file and it's revisions (if file revisions exist)
	 */
	function actionPositionRemoveFile($linemode=true)
    {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		
		global $langs;
		
		$id = GETPOST('id', 'int');
		$facid = GETPOST('facid', 'int');
		$socid = GETPOST('socid', 'int');
		if ($linemode) {
			$lineid = GETPOST('lineid', 'int');
		}
		
		$delresp = $this->deleteLinked(GETPOST('fileid', 'int'));
		if ($delresp > 0) {
			setEventMessage($langs->trans("FileWasRemoved"));
		} else {
			setEventMessage($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), 'errors');
		}
		
		if ($linemode) {
			if(!empty($id)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?id='.$id.'&action=editline&lineid='.$lineid.'#line_'.$lineid);
			} elseif (!empty($facid)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?facid='.$facid.'&action=editline&lineid='.$lineid.'#line_'.$lineid);
			} elseif (!empty($socid)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?socid='.$socid.'&action=editline&lineid='.$lineid.'#line_'.$lineid);
			}
		} else {
			if (!empty($id)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?id='.$id);
			} elseif (!empty($facid)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?facid='.$facid);
			} elseif (!empty($socid)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?socid='.$socid);
			}
		}
		exit;
	}
	
	function deleteLinked($filemapid, $activate_trigger=true)
    {
		global $conf, $db, $langs;
	
		$sql = "SELECT rowid, parent_file";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.= " WHERE rowid = ".$filemapid;
		$sql.= " OR parent_file = ".$filemapid;
		
		dol_syslog(get_class($this)."::deleteLinked sql=".$sql, LOG_DEBUG);

        $error = 0;

        $db->begin();
	
		$resql = $db->query($sql);
	
		if ($resql) {
				
			$num = $db->num_rows($resql);
			$i = 0;
			$fm = new ELbFileMapping($db);
			
			while ($i < $num) {

				$obj = $db->fetch_object($resql);
				
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
		    $db->rollback();
		    return -1;
        }

        $db->commit();
        return 1;

	}
	
	function delete()
    {
		global $db,$conf;
	
		$db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " WHERE rowid = ".$this->id;
	
		dol_syslog(get_class($this)."::delete id=".$this->id, LOG_DEBUG);
	
		$resql=$db->query($sql);
		
		//delete fie in solr
		if($resql && !empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
			$resql = ElbSolrUtil::remove_from_index($this->md5."_".$this->id);
		}
	
		if($resql) {
			$db->commit();
			return 1;
		} else {
			$db->rollback();
			return -1;
		}
	}	
	
	function getRealPath($path)
    {
		$path="file://".urldecode($path);
		return $path;
	}
	
	static function countLinkedFiles($object) {
		
		global $db;
		
		$sql="SELECT COUNT(*) AS cnt ";
		$sql.=" FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.=" WHERE object_type=".$object->element;
			
		dol_syslog(get_called_class().'::countLinkedFiles sql='.$sql, LOG_DEBUG);
			
		if ($resql=$db->query($sql))
		{
			$obj = $db->fetch_object($resql);
			$count=$obj->cnt;
			if($count>0) {
				return $count;
			} else {
				return 0;
			}
		}
		else
		{
			dol_syslog(get_called_class().'::countLinkedFiles ERROR sql='.$sql, LOG_DEBUG);
			return 0;
		}
	}
	
	static function linkFile() {
		
		global $db, $langs, $user;
	
		$return_url = GETPOST('return_url_link');
		$link = GETPOST('link', 'alpha');
		$description = GETPOST('description', 'alpha');
		$revision = GETPOST('revision', 'alpha');
		require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file_mapping.class.php';
		$elbfilemap = new ELbFileMapping($db);
		
		if (!$link) {
			setEventMessage($langs->trans('FilePathMissing'), 'errors');
			header("Location: ". $return_url);
			exit;
		}
		
		$db->begin();
		 
		// set file mapping properties
		$elbfilemap->fk_fileid=null;
		$elbfilemap->object_type=$_POST['object_type'];
		$elbfilemap->object_id=$_POST['object_id'];
		$elbfilemap->created_date=dol_now();
		$elbfilemap->user=$user->id;
		$elbfilemap->active=ELbFileMapping::FILE_ACTIVE;
		$elbfilemap->description=$description;
		$elbfilemap->revision=$revision;
		$elbfilemap->path=$link;
		 
		$fmid = $elbfilemap->create();
		 
		if ($fmid > 0) {
			$db->commit();
			setEventMessage($langs->trans("FileSuccessfullyLInked"), 'mesgs');
			
			// link to the product tab also
			if ($elbfilemap->object_type=='propaldet' || $elbfilemap->object_type=='commandedet') {
				$link_to_product = new ELbFileMapping($db);
				$link_to_product->fk_fileid=$elbfilemap->fk_fileid;
				$link_to_product->object_type='product';
				$elbfilemap->object_type=='propaldet' ? $orderline=false : $orderline=true;
				$product_id = getProductIDViaPositionID($elbfilemap->object_id, $orderline);
				$link_to_product->object_id=$product_id;
				$link_to_product->created_date=$elbfilemap->created_date;
				$link_to_product->user=$elbfilemap->user;
				$link_to_product->active=ELbFileMapping::FILE_ACTIVE;
				$link_to_product->description=$elbfilemap->description;
				$link_to_product->revision=$elbfilemap->revision;
				$link_to_product->path=$elbfilemap->path;
				$res = $link_to_product->create();
				if ($res > 0) {
					setEventMessage($langs->trans("FileAddedToTheProduct"), 'mesgs');
					// update clone_of_fmap_id for propaldet/commnade
					//$elbfilemap->clone_of_fmap_id = $res;
					//$elbfilemap->update();
					// delete file map from propaldet or commandedet because it will be created from the product trigger
					$elbfilemap->delete();
				}
			}
		} else {
			$db->rollback();
			setEventMessage($link.' - '.$langs->trans("FileNotLinked"), 'errors');
		}
		
		header("Location: ". $return_url.'#mvfid'.$fmid);
		exit;
	}
	
	static function searchPositionAttachedFiles($object_type, $object_id, $search_filename) {
		
		global $db;
		
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.object_type = '".$db->escape($object_type)."'";
		$sql.= " AND fm.object_id = ".$db->escape($object_id);
		$sql.= " AND f.name like '%".$db->escape($search_filename)."%'";
		
		dol_syslog(get_called_class()."::searchPositionAttachedFiles sql=".$sql);
		
		$resql = $db->query($sql);
		
		if ($resql) {
			$num = $db->num_rows($resql);
			return $num;
		} else {
			return 0;
		}
	}
	
	static function headerLocationAfterOperation($linemode=false, $id=false, $lineid=false, $filemapid=false, $facid=false, $socid=false) 
	{	
		if ($linemode) {
			if(!empty($id)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?id='.$id.'&action=editline&lineid='.$lineid.'&rowid='.$filemapid.'#mvfid'.$filemapid);
			} elseif (!empty($facid)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?facid='.$facid.'&action=editline&lineid='.$lineid.'&rowid='.$filemapid.'#mvfid'.$filemapid);
			} elseif (!empty($socid)) {
				header("Location: ".$_SERVER['PHP_SELF'].'?socid='.$socid.'&action=editline&lineid='.$lineid.'&rowid='.$filemapid.'#mvfid'.$filemapid);
			}
		} else {
			if (!empty($id)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?id='.$id.'#mvfid'.$filemapid);
			} elseif (!empty($facid)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?facid='.$facid.'#mvfid'.$filemapid);
			} elseif (!empty($socid)) {
				header('Location: ' .$_SERVER['PHP_SELF'].'?socid='.$socid.'#mvfid'.$filemapid);
			}
		}
		return;
	}	
	
	static function getDocumentFilename($original_file) {
		global $db;
		if($_GET['modulepart'] == 'elb' && !empty($_GET['fmapid'])) {
			$elb_fmap = new ELbFileMapping($db);
			$elb_fmap->fetch($_GET['fmapid']);
			$elb_fk_file = new ELbFile();
			$elb_fk_file->fetch($elb_fmap->fk_fileid);
			if (!empty($elb_fk_file->name))
			{
				$filename = $elb_fk_file->name;
			}
		} else {
			return basename($original_file);
		}
		return $filename;
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

		// link file (@TODO - check and remove linking functionality!)
		if (!elb_empty(GETPOST('link'))) {
			ElbFile::linkFile();
		}
	}
	
	function uploadExtrafield($object, $key, $prev_val) {
		
		global $user, $db, $conf;
		
		if(isset($_GET['del_value'])) {
			//deleting files
			$old_value=$object->array_options["options_".$key];
			$del_value=$_GET['del_value'];
			$error=false;
			$db->begin();
			
			$elbfilemap = new ELbFileMapping($db);
			if($elbfilemap->fetch($del_value)>0){
				$elbfile=new ELbFile();
				if($elbfile->fetch($elbfilemap->fk_fileid)>0) {
					if($elbfile->delete()) {
						$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';
						$file=$output_dir.$elbfile->id.'.'.$elbfile->type;
						unlink($file);
					} else {
						$error=true;
					}
				} else {
					$error=true;
				}
			} else {
				$error=true;
			}
			elb_common_action_result(!$error);
			if(!$error) {
				$db->commit();
				$old_value_arr=explode(",", $old_value);
				if(($key = array_search($del_value, $old_value_arr)) !== false) {
					unset($old_value_arr[$key]);
				}
				return implode(",",$old_value_arr);
			} else {
				$db->rollback();
				return -1;
			}
		}
		
		if(!isset($_FILES["options_".$key])) {
			return;
		}
		
		$file=$_FILES["options_".$key];
		
		if($file['error']!=0) {
			return null;
		}
		
		$error=false;
		$db->begin();
		
		$fileName = $file["name"];
		$output_buffer_dir = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/';
		$output_buffer = DOL_DATA_ROOT.'/'.$conf->global->ELB_UPLOAD_FILES_BUFFER.'/'.$fileName;
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
		$res = dol_move_uploaded_file($file["tmp_name"],$output_buffer, 1);
		
		if ($res>0) {
			$this->name = $fileName;
			$ext  = (new SplFileInfo($fileName))->getExtension();
			$this->type = $ext;
			$this->md5 = md5_file($output_buffer);
			
			$newfileid = $this->create();
			
			if ($newfileid > 0) {
				$elbfilemap = new ELbFileMapping($db);
				$elbfilemap->fk_fileid=$newfileid;
				$elbfilemap->object_type=$object->element."_extrafield";
				$elbfilemap->object_id=$object->id;
				$elbfilemap->user=$user->id;
				$elbfilemap->created_date=dol_now();
				$elbfilemap->active=1;
				$elbfilemapid=$elbfilemap->create();
				
				if($elbfilemapid>0) {
					$move_res = dol_move($output_buffer, $output_dir.$newfileid.'.'.$ext);
				} else {
					$error=true;
				}
			} else {
				$error=true;
			}
		} else {
			$error=true;
		}
		elb_common_action_result(!$error);
		if(!$error) {
			$db->commit();
			if(!empty($prev_val)) {
				$prev_val_arr=explode(",",$prev_val);
				$prev_val_arr[]=$elbfilemapid;
				$new_val=implode(",", $prev_val_arr);
			} else {
				$new_val = $elbfilemapid;
			}
			return $new_val;
		} else {
			$db->rollback();
			return -1;
		}
		
	}
	
	static function showExtraField($value, $key=null, $edit=false) {
		global $object, $db, $langs, $conf;
		$s='';
		if(empty($value)) {
			$value_arr=array();
		} else {
			$value_arr=explode(",", $value);
		}
		if($edit) {
			$s.='<table class="border">';
		}
		foreach($value_arr as $value) {
			if($edit) {
				$s.='<tr>';
			}
			$elbfilemap = new ELbFileMapping($db);
			$ret = $elbfilemap->fetch($value);
			if($ret <=0 ) return $langs->trans('ErrorFetchingUploadedFile');
			$elbfile=new ELbFile();
			$ret = $elbfile->fetch($elbfilemap->fk_fileid);
			if($ret <=0 ) return $langs->trans('ErrorFetchingUploadedFile');
			$file_relpath = $conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$elbfile->id.'.'.$elbfile->type;
			$href_download = DOL_URL_ROOT . '/document.php?modulepart=elb&attachment=true&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$elbfilemap->id;
			if($edit) {
				$s.='<td>';
			}
			$s.= '<a href="'.$href_download.'">';
			$s.= img_mime($elbfile->name,$langs->trans("File").': '.$elbfile->name);
			$s.="&nbsp;";
			$s.= dol_trunc($elbfile->name,50);
			$s.='</a>';
			$s.='<br/>';
			if($edit) {
				$s.='</td>';
				$s.='<td>';
				$link=$_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=update_extras&attribute=' . $key.'&del_value='.$elbfilemap->id;
				$s.='<a href="'.$link.'" onclick="if(!confirm(\''.$langs->trans('ConfirmDeleteUploadedFile').'?\')) return false;">'.img_delete().'</a>';
				$s.='</td>';
			}
			if($edit) {
				$s.='</tr>';
			}
		}
		if($edit) {
			$s.='<tr>';
			$s.='<td colspan="2">';
			$s.='<br/><input class="flat" type="file" name="options_'.$key.'" />';
			$s.='<input type="submit" class="button" value="' . $langs->trans('Add') . '">';
			$s.='</td>';
			$s.='</tr>';
			$s.='</table>';
		}
		return $s;
	}
	
	static function showInputExtraField($value,$key,$keyprefix) {
		global $object, $langs;
		$out=self::showExtraField($value, $key, true);
		return $out;
	}
	
	static function getTableRowOfLinkedFiles($object_type, $object_id, $colspan=1) {
		global $langs;
		$elbfile = new ELbFile();
		print '<table width="100%" class="elb-subtable">';
		print '<thead>';
		print '<tr>';
		print '<th align="left">';
		print '<a href="" onclick="toggleSubtable(this); return false;" class="toggle-link">';
		print '<span class="ui-icon ui-icon-triangle-1-e"></span>';
		print $langs->trans("LinkedFiles");
		print '</a>';
		print '</th>';
		print '</tr>';
		print '</thead>';
		print '<tbody style="display:none">';
		print '<tr>';
		print '<td class="nobottom">';			
		$elbfile->getUploadedFiles($object_type, $object_id, 0);
		print '</td>';
		print '</tr>';
		print '</tbody>';
		print '</table>';		
	}
	
	static function fileExists($name, $type, $md5) {
		global $db;
		$sql="select count(*) as cnt from ".MAIN_DB_PREFIX.self::$_tbl_name." f
				where f.name='".$db->escape($name)."' and f.type='".$db->escape($type)."'
				and f.md5='".$db->escape($md5)."'";
		$res=ElbCommonManager::querySingle($sql);
		return ($res->cnt > 0);
	}
	
	static function getLinkedObject($object_type, $object_id) {
		global $db,$langs;
		switch($object_type) {
			case "product":
				$product=new Product($db);
				$product->fetch($object_id);
				return array("ref"=>$product->getNomUrl(1),"name"=>$langs->trans("Product"));
			case"commande":
				$commande=new Commande($db);
				$commande->fetch($object_id);
				return array("ref"=>$commande->getNomUrl(1),"name"=>$langs->trans("Order"));
			case"commandedet":
				$commande=new Commande($db);
				$commandeLine = new OrderLine($db);
				$commandeLine->fetch($object_id);
				$fk_commande=$commandeLine->fk_commande;
				$commande->fetch($fk_commande);
				return array("ref"=>$commande->getNomUrl(1),"name"=>$langs->trans("OrderLine"));
			case"propaldet":
				$propal=new Propal($db);
				$propalLine=new PropaleLigne($db);
				$propalLine->fetch($object_id);
				$fk_propal=$propalLine->fk_propal;
				$propal->fetch($fk_propal);
				return array("ref"=>$propal->getNomUrl(1),"name"=>$langs->trans("ProposalLine"));
			case"propal":
				$propal=new Propal($db);
				$propal->fetch($object_id);
				return array("ref"=>$propal->getNomUrl(1),"name"=>$langs->trans("Proposal"));
			case"societe":
				$societe=new Societe($db);
				$societe->fetch($object_id);
				return array("ref"=>$societe->getNomUrl(1),"name"=>$langs->trans("Company"));
			case"order_supplier":
				$order_supplier=new CommandeFournisseur($db);
				$order_supplier->fetch($object_id);
				return array("ref"=>$order_supplier->getNomUrl(1),"name"=>$langs->trans("SupplierOrder"));
			case"tech_procedure":
				$techProcedue = ElbTechProcedure::fetch($object_id);
				$fk_product = $techProcedue->fk_product;
				$product=new Product($db);
				$product->fetch($fk_product);
				return array("ref"=>$product->getNomUrl(1),"name"=>$langs->trans("TechProcedure"));
			case"askpricesupplier":
				$supplierOffer = new AskPriceSupplier($db);
				$supplierOffer->fetch($object_id);
				return array("ref"=>$supplierOffer->getNomUrl(1), "name"=>$langs->trans("SupplyRequest"));
			case"inquiry":
				$inquiry = ElbInquiry::fetch($object_id);
				return array("ref"=>$inquiry->getNomUrl(1),"name"=>$langs->trans("Inquiry"));
			case"process":
				$process=ElbProcess::fetchById($object_id);
				return array("ref"=>$process->getNomUrl(1),"name"=>$langs->trans("Process"));
			case"commande_fournisseurdet":
				$order_supplier=new CommandeFournisseur($db);
				$order_supplier_line = new CommandeFournisseurLigne($db);
				$order_supplier_line->fetch($object_id);
				$fk_order_supplier = $order_supplier_line->fk_commande;
				$order_supplier->fetch($fk_order_supplier);
				return array("ref"=>$order_supplier->getNomUrl(1),"name"=>$langs->trans("OrderLine"));
			case"project":
				$project=new Project($db);
				$project->fetch($object_id);
				return array("ref"=>$project->getNomUrl(1),"name"=>$langs->trans("Project"));
			case "facture":
				$facture=new Facture($db);
				$facture->fetch($object_id);
				return array("ref"=>$facture->getNomUrl(1),"name"=>$langs->trans("Invoice"));
			case "invoice_supplier":
				$facture=new FactureFournisseur($db);
				$facture->fetch($object_id);
				return array("ref"=>$facture->getNomUrl(1),"name"=>$langs->trans("SupplierInvoice"));
			case"propal_extrafield":
			case"user_extrafield":
			default:
				return array("ref"=>$object_id,"name"=>$object_type);
		}
	}
	
	/**
	 * Moves file from file system to elb upload directory
	 * Returns filemap id of file
	 * 
	 * @param 	int 	$object_id
	 * @param 	string 	$object_element
	 * @param 	string 	$exf_identf
	 * @param 	string 	$full_file_path
	 * @return 	Ambigous <boolean, number>	false KO, >0 OK
	 */
	function addFileFromFileSysToObjectExfTypeUpload($object_id, $object_element, $exf_identf, $full_file_path) 
	{
		global $db, $conf, $user;
		
		$error=false;
		
		$output_dir = DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/';
		
		// create elb file upload directory (if not exists)
		if (!file_exists($output_dir)) {
			mkdir($output_dir, 0775, true);
		}
		
		$db->begin();
	
		$fileName = basename($full_file_path);
		
		$this->name = $fileName;
		$ext  = (new SplFileInfo($fileName))->getExtension();
		$this->type = $ext;
		$this->md5 = md5_file($full_file_path);
			
		$newfileid = $this->create();
		
		$elbfilemapid = false;
			
		if ($newfileid > 0) {
			$elbfilemap = new ELbFileMapping($db);
			$elbfilemap->fk_fileid=$newfileid;
			$elbfilemap->object_type=$object_element."_extrafield";
			$elbfilemap->object_id=$object_id;
			$elbfilemap->user=$user->id;
			$elbfilemap->created_date=dol_now();
			$elbfilemap->active=1;
			$elbfilemapid=$elbfilemap->create();

			if ($elbfilemapid > 0) {
				$move_res = dol_move($full_file_path, $output_dir.$newfileid.'.'.$ext);
				if (!$move_res) $error=true;
			} else {
				$error=true;
			}
		} else {
			$error=true;
		}
		
		(!$error && $elbfilemapid > 0) ? $db->commit() : $db->rollback();
		
		return $elbfilemapid;
	}

    /**
     * Delete file from the database and from the server
     *
     * @param $fileID
     */
	function deleteFile($fileID)
    {
        $elbFile = new $this();
        $elbFile->fetch($fileID);
        $res = $elbFile->delete();
        if (!($res > 0)) {
            return false;
        }

        // get full path to the file on the server
        $fullPath = $this->getFullServerPathForFile($elbFile);

        // delete file from file system
        $this->removeFileFromFileSystem($fullPath);

        return true;
    }

    public function  getFullServerPathForFile($elbFile)
    {
        global $conf;
        return DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$elbFile->id.'.'.$elbFile->type;
    }

    public function removeFileFromFileSystem($fileFullPath)
    {
        unlink($fileFullPath);
    }

	
}