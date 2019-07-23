<?php 

//use ELBClass\solr\ElbSolrUtil;

global $elbfile, $conf;
global $search_highlights;
$doc_id=$obj->fmd5."_".$obj->fmrowid;

$canDeleteFile = (($restictDeleteFile && ($obj->fmuser == $user->id || $user->admin)) || !$restictDeleteFile);

?>
<tr <?php echo $bc[true] ?>>					
	<td align="center"  class="td-file-nr" id="mvfid<?php echo $obj->fmrowid; ?>"><?php echo ++$i; ?>.</td>
	<?php
		$file_relpath = '';
		if (empty($obj->fmpath)) {
			$file_relpath = $conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$obj->frowid.'.'.$obj->ftype;
		} else {		
			$file_relpath .= $obj->fmpath;
		}
		$id = GETPOST('id');
		$facid = GETPOST('facid');
		$socid = GETPOST('socid');
		$lineid = GETPOST('lineid');
	?>
	<!-- Show file name with link to download-->
	<td class="td-file-name">
	
		<?php 
			if(empty($obj->fmpath)) {
				$href_download = DOL_URL_ROOT . '/document.php?modulepart=elbmultiupload&attachment=true&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$obj->fmrowid;
				echo '<span class="pushright">
						<a href="'.$href_download.'" class="oldv-enable">'.
							 img_picto($langs->trans('DownloadFile'), 'elb-download-14x14.png@elbmultiupload') .'
						</a>
					 </span>';
			}
		?>
		
		<!-- Rename file link-->
		<span class="pushright">
			<?php
				$url = DOL_URL_ROOT . '/elbmultiupload/ajax/rename_file.php?file_id=' . $obj->frowid.'&object_element='.$object_element;
				$trans = $langs->trans('RenameFile');
				$icon = img_edit($langs->trans('RenameFile'));
				print '<a href="#" onclick="elb_ajax_dialog(\''. $url .'\',\''. $trans .'\',\'400\'); return false;">'. $icon .'</a>&nbsp;&nbsp;';
			?>
		</span>
		
		<?php if ($modef) { ?>
		    <?php 
		    	if(empty($obj->fmpath)) {
					echo $obj->fname;
				} else {
					echo '<input type="text" name="path" value="'.$obj->fmpath.'" />';
				}
		     ?>
		     
		     <br/>
		     <?php
                if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
                    $all_tags = ElbFileCategory::getFileTags();

                    $file_id = $obj->fmd5 . "_" . $obj->fmrowid;
                    $tags = json_decode($obj->fmtags, true);

                    $form = new ElbForm($db);
                    print $form->multiselectarray('tags', $all_tags, $tags, '', 0, '', 0, '100%', '', '', true);
                }
		     ?>
		     
		<?php } else { ?>
			<?php 
				if(empty($obj->fmpath)) {
					$href=DOL_URL_ROOT . '/document.php?modulepart=elbmultiupload&amp;file='.urlencode($file_relpath).'&amp;fmapid='.$obj->fmrowid;
				} else {
					$href=$obj->fmpath;
				}
			?>
			<a href="<?php echo $href; ?>" <?php if(!empty($obj->fmpath)) { ?>target="_blank"<?php } ?>  class="oldv-enable">
				<?php 
					if (empty($obj->fmpath)) {
						echo img_mime($obj->fname,$langs->trans("File").': '.$obj->fname); 
						echo dol_trunc($obj->fname,$maxfilenamelength);
					} else {
						echo img_mime($obj->fmpath,$langs->trans("File").': '.$obj->fname);
						echo dol_trunc($obj->fmpath,$maxfilenamelength);
					} 
				?>
			</a>

            <br/>
            <?php
            if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
                $all_tags = ElbFileCategory::getFileTags();

                $file_id = $obj->fmd5 . "_" . $obj->fmrowid;
                $tags = json_decode($obj->fmtags, true);

                $form = new ElbForm($db);
                if (is_array($tags) && count($tags)) {
                    print $form->multiselectarray('tags' . $obj->fmrowid, $all_tags, $tags, '', 0, '', 0, '100%', 'disabled', '', true);
                }
            }
            ?>
			
		<?php } ?>
	</td>
	
	<td class="td-file-desc">
		<?php if ($modef) { ?>
			<input type="text" name="description" value="<?php echo $obj->fmdescription; ?>" />
		<?php } else { ?>
		<?php echo $obj->fmdescription; } ?>
	</td>
				
	<td class="td-file-rev nowrap">
		<?php if ($modef) { ?>
			<input type="text" size="6" name="frev" value="<?php echo $obj->fmrevision; ?>" />
		<?php } else { ?>
		<?php echo $obj->fmrevision; } ?>
	</td>
	
	<?php 
		// Show file size
		if(empty($obj->fmpath)) {
			$module = $remove_module[0];
			$filepath=DOL_DATA_ROOT.'/elbmultiupload/'.$conf->global->ELB_UPLOAD_FILES_DIRECTORY.'/'.$obj->frowid.'.'.$obj->ftype;
			if (file_exists($filepath)) {
				$size= dol_filesize($filepath);
				$size= dol_print_size($size,1,1);
			}
		}
	?>
	<td align="left" class="td-file-size nowrap"><?php echo $size; ?></td>
	
	<?php 			
		// Show file date
	    $cdate='';
		if(empty($obj->fmpath)) {
			$cdate=  dol_print_date(dol_filemtime($filepath), 'dayhour');
			$mdate= dol_print_date($obj->fmcreated_date, 'dayhour', 'gmt');
		}
	?>
	<td align="left" class="td-file-modif nowrap">
		<?php
			if (!empty($mdate)) { 
				echo $cdate;
			} 
		?>
	</td>
	
	<?php 			
		// Show user who created file
		$userObj = new User($this->db);
		$userObj->fetch($obj->fmuser, '', '',1);
	?>						
	<td align="left" class="td-file-user nowrap">
		<?php if (is_object($userObj)) {
			$href=DOL_URL_ROOT.'/user/card.php?id='.$obj->fmuser;
		?>
			<a href="<?php echo $href;?>"><?php echo $userObj->login; ?></a>
		<?php }	?>		
	</td>
		
	<?php if ($toolbox) { ?>
		<td align="right" class="td-file-toolbox nowrap">
			<?php if ($modef) { ?>
				<input type="hidden" name="id" value="<?php echo $id ?>" />
				<input type="hidden" name="facid" value="<?php echo $facid ?>" />
				<input type="hidden" name="socid" value="<?php echo $socid ?>" />
				<input type="hidden" name="lineid" value="<?php echo $lineid ?>" />		
				<input type="hidden" name="filemapid" value="<?php echo $obj->fmrowid; ?>" />
                <input type="hidden" name="object_element" value="<?php echo $object_element ?>" />
				<input type="submit" name="update_file" class="button" value="<?php echo $langs->trans("Update") ?>" />	
				<?php
					if (!empty($id)) {
						$cancel_url=$_SERVER["PHP_SELF"].'?id='.$id;
					} elseif (!empty($facid)) {
						$cancel_url=$_SERVER["PHP_SELF"].'?facid='.$facid;
					} elseif (!empty($socid)) {
						$cancel_url=$_SERVER["PHP_SELF"].'?socid='.$socid;
					}
					
					if (GETPOST('action')) {
                        $cancel_url .= '&action=' . GETPOST('action');
                    }
					if (GETPOST('lineid')) {
                        $cancel_url .= '&lineid=' . $lineid;
                    }
                    $cancel_url.='&object_element='.$object_element;
					$cancel_url.='#mvfid'.$obj->fmrowid;
				?>
				<a class="button elbbtn" href="<?php echo $cancel_url ?>">
					<?php echo $langs->trans("Cancel"); ?>
				</a>
							
			<?php } else { ?>
				<?php
					if (!empty($id)) {
						$edit_href = $_SERVER["PHP_SELF"].'?id='.$id;
					} elseif (!empty($facid)) {
						$edit_href = $_SERVER["PHP_SELF"].'?facid='.$facid;
					} elseif (!empty($socid)) {
						$edit_href=$_SERVER["PHP_SELF"].'?socid='.$socid;
					}
					if (GETPOST('action')) $edit_href.='&action='.GETPOST('action');
					if (GETPOST('lineid')) $edit_href.='&lineid='.$lineid;					
					$edit_href.='&action2=editfile';
					$edit_href.='&rowid='.$obj->fmrowid;
					$edit_href.='&object_element='.$object_element;
					$edit_href.='#mvfid'.$obj->fmrowid;
					
					if (!empty($id)) {
						$delete_href = $_SERVER["PHP_SELF"].'?id='.$id;
					} elseif (!empty($facid)) {
						$delete_href = $_SERVER["PHP_SELF"].'?facid='.$facid;
					} elseif (!empty($socid)) {
						$delete_href = $_SERVER["PHP_SELF"].'?socid='.$socid;
					}
					if (GETPOST('action')) $delete_href.='&action='.GETPOST('action');
					if (GETPOST('lineid')) $delete_href.='&lineid='.$lineid;
					$delete_href.='&action2=remove_line_file';
					$delete_href.='&fileid='.$obj->fmrowid;
                    $delete_href.='&object_element='.$object_element;
					$delete_href.='#mvfid'.$obj->fmrowid;					
				?>
				<a href="<?php echo $edit_href;?>">
	        		<?php echo img_edit($langs->trans('Edit')); ?>
				</a>
				
				<?php 
					$trigger = 'puptr-'.$obj->fmrowid;
					$popup 	 = 'popw-'.$obj->fmrowid;
				?>			
				
				<?php //if (empty($obj->fmpath)) { ?>
					<div class="<?php echo $trigger;?> ufpuptr" title="<?php echo $langs->trans("UploadNewVersion");?>">
						<div class="<?php echo $popup;?>" title="<?php echo $langs->trans("UploadNewVersion");?>">
							<label for="fsubrev-<?php echo $obj->fmrowid;?>">
								<?php echo $langs->trans("Revision");?>:<br/>
								<input type="text" size="10" id="fsubrev-<?php echo $obj->fmrowid;?>" name="fsubrev" />
							</label>
							
							<label for="fdesc-<?php echo $obj->fmrowid;?>">
								<br/><br/><?php echo $langs->trans("Description");?>: <br/>
								<input type="text" name="description" value="" />
							</label>
							
							<label for="ufmnvfile<?php echo $obj->fmrowid;?>">
								<br/><br/><?php echo $langs->trans("ChooseFile");?>: *<br/>							
								<input type="file" name="ufmnvfile<?php echo $obj->fmrowid;?>" id="ufmnvfile<?php echo $obj->fmrowid;?>" class="unv" />
							</label>
							<input type="hidden" name="ufmid" value="<?php echo $obj->fmrowid;?>" />
							<input type="hidden" name="id" value="<?php echo $id ?>" />
							<input type="hidden" name="socid" value="<?php echo $socid ?>" />
							<input type="hidden" name="facid" value="<?php echo $facid ?>" />
							<input type="hidden" name="lineid" value="<?php echo $lineid ?>" />
                            <input type="hidden" name="object_element" value="<?php echo $object_element ?>" />
                            <br/><br/>
							<input class="button" type="submit" name="actionufnv" value="<?php echo $langs->trans("Send");?>" />
						</div>				
					</div>
				<?php //} else { echo '&nbsp;'; } ?>
				
				<!--  popup - upload new file version  -->
				<script>
					$(function() {
						$( ".<?php echo $popup;?>" ).dialog({
							autoOpen: false,
							modal: true,
							width: 450,		                    
							show: {
								effect: "puff",
								duration: 350
							},
							hide: {
								effect: "explode",
								duration: 350
							}
						});	
						$( ".<?php echo $trigger;?>" ).click(function() {
							// wrap popup with form tag to avoid nested form problem
							var form_html = "<form name='ufnv' class='wrapform' method='POST' enctype='multipart/form-data'></div>";

							 if ( $( ".<?php echo $popup;?>" ).parent().is( ".wrapform" ) ) {
								$(".<?php echo $popup;?>").unwrap();
								$(".<?php echo $popup;?>").wrap(form_html);
							} else {
								$(".<?php echo $popup;?>").wrap(form_html);
							}
														
							$( ".<?php echo $popup;?>" ).dialog( "open" );
						});
					});
				</script>					
				<?php if($canDeleteFile) { ?>
                    <a class="ufdel" onclick="if (!confirm('<?php echo $langs->trans('ReallyDeleteFileAndItsVersions');?>?')) return false;" href="<?php echo $delete_href ?>" >
                        <?php echo img_picto($langs->trans("Delete"), 'delete.png');?>
                    </a>
                <?php } ?>
			<?php } ?>		
		</td>
	<?php } ?>
</tr>			