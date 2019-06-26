<?php
//use ELBClass\solr\ElbSolrUtil;

/* Copyright (C)    2019      Milos Petkovic     <milos.petkovic@livemediagroup.de>
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
 * or see http://www.gnu.org/
 */

// ELB CHANGE
if ($conf->elbmultiupload->enabled) {
	global $db, $conf;
	$elbfile = new ELbFile();
}

$langs->load("link");
if (empty($relativepathwithnofile)) $relativepathwithnofile='';

$file_list_display = GETPOST('file-list-display');
if (empty($file_list_display)) {
	if (empty($_SESSION['file-list-display'])) {
	    $file_list_display = 'by_rev';
	} else {
		$file_list_display = $_SESSION['file-list-display'];
	}
}
$_SESSION['file-list-display'] = $file_list_display;

/*
 * Confirm form to delete
 */

if ($action == 'delete')
{
	$langs->load("companies");	// Need for string DeleteFile+ConfirmDeleteFiles
	$ret = $form->form_confirm(
			$_SERVER["PHP_SELF"] . '?id=' . $object->id . '&urlfile=' . urlencode(GETPOST("urlfile")) . '&linkid=' . GETPOST('linkid', 'int') . (empty($param)?'':$param),
			$langs->trans('DeleteFile'),
			$langs->trans('ConfirmDeleteFile'),
			'confirm_deletefile',
			'',
			0,
			1
	);
	if ($ret == 'html') print '<br>';
}


//search files
if(isset($_REQUEST['Search'])) {
	$search_object_element=GETPOST('search_object_element','alpha');
	$id=GETPOST('id','int');
	$search_name=GETPOST('search_name','alpha');
	$search_content=GETPOST('search_content','alpha');
	$search_tags=$_REQUEST['search_tags'];
	$search_rev=GETPOST('search_rev','alpha');
	
	$result = ElbSolrUtil::complex_search($search_name, $search_content, $search_object_element, $id, $search_tags, $search_rev);
	if($result) {
		$search_result = json_decode($result,true);
	}
	if($search_result && isset($search_result['response']['numFound'])) {
		$search_files = array();
		foreach($search_result['response']['docs'] as $doc) {
			$search_files[]=$doc['elb_filemapid'];
		}
		$search_highlights=array();
		foreach ($search_result['highlighting'] as $doc_id=>$matches) {
			$search_highlights[$doc_id]=$matches['attr_content'][0];
		}
	}
}

$formfile=new FormFile($db);

// show multiupload and link buttons
print ELbFile::showMultiUploadButton($object->element, $object->id);

//tag - file map
$tag_map = ELbFileMapping::getObjectTags($object->element, $object->id);

$all_tags=array();

foreach(array_keys($tag_map) as $tag) {
	$all_tags[$tag]=$tag;
}

?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
	<input type="hidden" name="id" value="<?php echo $object->id ?>"/>
	<input type="hidden" name="search_object_element" value="<?php echo $object->element ?>"/>
	<table class="border" width="100%">
		<tr class="liste_titre">
			<th colspan="6"><?php echo $langs->trans("Search") ?></th>
		</tr>
		<tr>
			<td>
				<?php echo $langs->trans("Name") ?>
			</td>
			<td>
				<input type="text" name="search_name" value="<?php echo $search_name ?>"/>
			</td>
			<td>
				<?php echo $langs->trans("Content") ?>
			</td>
			<td>
				<input type="text" name="search_content" value="<?php echo $search_content ?>"/>
			</td>
			<td>
				<?php echo $langs->trans("Revision") ?>:
			</td>
			<td>
				<input type="text" name="search_rev" value="<?php echo $search_rev ?>" size="3"/>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $langs->trans("Tag") ?>
			</td>
			<td colspan="5">
				<?php 
//				$form=new Form($db);
//				$all_tags = Categorie::getFileTags();
//				print $form->multiselectarray('search_tags', $all_tags, $search_tags, '', 0, '', 0, '100%','','',true);
				?>
			</td>
		</tr>
		<tr>
			<td colspan="6">
				<input class="button" type="submit" name="Search" value="<?php echo $langs->trans("Search") ?>"/>
				<a href="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?php echo $object->id ?>" class="button elbbtn"><?php echo $langs->trans("Cancel") ?></a>
			</td>
		</tr>
	</table>
</form>

<style>
	.select2-container .select2-selection--multiple {
		min-height: 24px;
	}
	.select2-container--default.select2-container--focus .select2-selection--multiple {
		border: solid 1px rgba(0,0,0,.3);
	}
	.select2-container--default .select2-selection--multiple .select2-selection__choice {
		margin-top:3px;
	}
</style>

<br>
<form id="file-list-display-form" action="" method="post" >
	<?php echo $langs->trans('GroupFilesBy'); ?>
	<select id="select-file-list-display" name="file-list-display" >
		<option value='by_tag' <?php if ($file_list_display == 'by_tag') { echo 'selected="selected"'; } ?> ><?php echo $langs->trans('Tag'); ?></option>
		<option value='by_rev' <?php if ($file_list_display == 'by_rev') { echo 'selected="selected"'; } ?> ><?php echo $langs->trans('Revision'); ?></option>
	</select>
</form>
<br>

<?php
if ($file_list_display == 'by_tag')
{
	$elbfile->getUploadedFiles($object->element, $object->id, 1, $tag_map, $search_files, $restictDeleteFile);
}
elseif ($file_list_display == 'by_rev')
{
	$fetch_files = $elbfile->fetchUploadedFiles($object->element,$object->id, $search_files);
    $elbfile->renderFilesByRevision($fetch_files, 1, $restictDeleteFile);
}
print "<br>";
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#select-file-list-display').on('change', function() {
			$('#file-list-display-form').submit();
		});
	});
</script>

<?php 
// ELB - add ajax modal dialog
if(!empty($conf->elbmultiupload->enabled)) {
    elbmultiupload_renderAjaxPopup();
}
?>