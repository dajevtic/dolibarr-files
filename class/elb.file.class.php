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
 *	\file       htdocs/elbmultiupload/class/elb.file.class.php
 *	\ingroup    elbmultiupload
 *	\brief      Contains methods for uploading/versioning files
 */
 
class ELbFile
{
    public $db;

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
	 *  @param	int		$id     id of file to load
	 *  @return int     		<0 if KO, >0 if OK
	 */
	function fetch($id='')
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
		$sql.=	' WHERE rowid = '.$id;
	
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
	 *	Insert file info into database
	 *
	 *	@return int		id of a row if OK or if error < 0
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

	function getFileVersions($fileid, $toolbox)
    {
		global $conf, $langs;
		
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.parent_file = '".$this->db->escape($fileid)."'";
		$sql.= " AND fm.active=".ELbFileMapping::FILE_REVISION;
		$sql.= " ORDER BY f.rowid DESC";
		
		dol_syslog(get_class($this)."::getFileVersions sql=".$sql);
	
		$resql = $this->db->query($sql);
	
		if ($resql)
			$num = $this->db->num_rows($resql);
	
		if ($num > 0) {
				
			$action2 = GETPOST('action2');
			$fileid = GETPOST('rowid');
            $object_element = GETPOST('object_element');
				
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
			$var = false;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
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
		$sql = "SELECT f.rowid as frowid, f.name as fname, f.type as ftype, f.md5 as fmd5,";
		$sql.= " fm.rowid as fmrowid, fm.fk_fileid as fmfk_fileid, fm.object_type as fmobject_type,";
		$sql.= " fm.object_id as fmobject_id, fm.created_date as fmcreated_date, fm.user as fmuser, fm.description as fmdescription,";
		$sql.= " fm.path as fmpath, fm.parent_file as fmparent_file, fm.revision as fmrevision, fm.active as fmactive, fm.tags as fmtags";
		$sql.= " FROM ".MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name." as fm";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX.self::$_tbl_name." as f ON (f.rowid=fm.fk_fileid)";
		$sql.= " WHERE fm.active=".ELbFileMapping::FILE_ACTIVE;
		if(!empty($object_type)) {
			$sql.= " AND fm.object_type = '".$this->db->escape($object_type)."'";
		}
		if(!empty($object_id)) {
			$sql.= " AND fm.object_id = ".$this->db->escape($object_id);
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

	/**
	 *  Update file attached in the position
	 */
	function actionPositionUpdateFile()
    {
		global $langs, $conf, $user;
	
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

		$elbfilemap->oldcopy = clone $elbfilemap;
			
		if ($res > 0) {
			
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
			$result = $elbfilemap->update(); 
				
			if ($result > 0) {

				setEventMessage($langs->trans("FileSuccessfullyUpdated"), 'mesgs');

				//check and store tags
				$tags_list=array();
				if(count($tags)>0) {
					$all_tags = ElbFileCategory::getFileTags();
					foreach ($tags as $tag) {
						if(!in_array($tag, $all_tags)) {
							$sql="INSERT INTO ".MAIN_DB_PREFIX."categorie SET label='".$this->db->escape($tag)."', type=".ElbFileCategory::TYPE_ELB_FILE;
							ElbCommonManager::execute($sql);
						}
					}
				}

				if(!empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
					//update file in Solr
					$elbfile = new ELbFile($this->db);
					$elbfile->fetch($elbfilemap->fk_fileid);
					ElbSolrUtil::add_to_search_index($elbfile, $elbfilemap, $tags);
				}

			} else {
                $error++;
				setEventMessage($langs->trans("FileNotUpdated"), 'errors');
			}
		} else {
			$error++;
		}

        (!$error) ? $this->db->commit() : $this->db->rollback();

		self::headerLocationAfterOperation($id, $object_element, $filemapid, $facid, $socid);
		exit;
	}
	
	/**
	 *  Upload new version for file in the position
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
						$old_object_user = $elbfilemap->user;
						$old_object_created_date = $elbfilemap->created_date;
						
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
							
							$fetch_all_subversions = $this->fetchFileVersionByParentFile($old_fmid);
							
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
								
								//send file to solr index
								if($res && !empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
									foreach($files_to_reindex as $file_to_reindex) {
										$elbfile=new ELbFile($this->db);
										$elbfile->fetch($file_to_reindex->fk_fileid);
										$res = $res && ElbSolrUtil::add_to_search_index($elbfile, $file_to_reindex);
									}
								}
								
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

	static function showMultiUploadButton($object_type, $object_id, $multiupload=true, $attach_external=true, $id_add=false)
    {
		global $langs, $user;
	
		$out = '';
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
		if ($multiupload) {
            $out .= '		multiple: true,';
        } else {
            $out .= '		multiple: false,';
        }
	
		$out .= '		formData: {';
		$out .=	 '   		object_type: "'.$object_type.'",';
		$out .=	 '   		object_id: '.$object_id.',';
		$out .=  '			user_id:  '.$user->id.',';
		$out .=  '			sendit: "ajaxUpload"
					},
					afterUploadAll:function() {';
		$out .=  '			location.reload();';
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
		// Check parameters
		if ( empty($parent_file) || !is_numeric($parent_file)) {
			return false;
		}
		
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.ELbFileMapping::$_tbl_name;
		$sql.= ' WHERE parent_file = '.$parent_file;
		
		$resql = $this->db->query($sql);
		
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			$ids = false;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
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
						
						$fetch_all_subversions = $this->fetchFileVersionByParentFile($fm_to_deactivate->id);
							
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
	 *  Remove/delete file and it's revisions (if file revisions exist)
	 */
	function actionPositionRemoveFile()
    {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		
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
	
	function deleteLinked($filemapid, $activate_trigger=true)
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
	
	function delete()
    {
		global $conf;

        $this->db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->tbl_name;
		$sql.= " WHERE rowid = ".$this->id;
	
		dol_syslog(get_class($this)."::delete id=".$this->id, LOG_DEBUG);
	
		$resql=$this->db->query($sql);
		
		//delete fie in solr
		if($resql && !empty($conf->global->ELB_ADD_FILES_TO_SOLR)) {
			$resql = ElbSolrUtil::remove_from_index($this->md5."_".$this->id);
		}
	
		if ($resql) {
            $this->db->commit();
			return 1;
		}

        $this->db->rollback();
		return -1;
	}	

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
     * @param $fileID
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
        $fullPath = $this->getFullServerPathForFile($elbFile);

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
    public function getFullServerPathForFile($elbFile)
    {
        global $conf;
        return DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$elbFile->id.'.'.$elbFile->type;
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
	
}