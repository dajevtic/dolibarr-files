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
 * or see http://www.gnu.org/
 */


global $db, $conf, $toolbox;
$elbfile = new ELbFile($db);

$langs->load("link");
if (empty($relativepathwithnofile)) $relativepathwithnofile='';

// get selected files grouping method from select box
$file_list_display = GETPOST('file-list-display', 'none', 1);

// set selected grouping method in session
if ($file_list_display && (!ElbFileSession::isSetGroupFiles() || ElbFileSession::getGroupFilesMethod() != $file_list_display)) {
    ElbFileSession::setGroupFilesMethod($file_list_display);
}
if (!ElbFileSession::isSetGroupFiles()) {
    ElbFileSession::setGroupFilesMethod(ElbFileGrouping::GROUP_FILES_DEFAULT);
}
// read files grouping method from session
$file_list_display = ElbFileSession::getGroupFilesMethod();

//search files
if(!empty($conf->global->ELB_ADD_FILES_TO_SOLR) && isset($_REQUEST['Search'])) {
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

// show multiupload button
print ELbFile::showMultiUploadButton($object->element, $object->id);

//tag - file map
$tag_map = ELbFileMapping::getObjectTags($object->element, $object->id);
$all_tags=array();
foreach(array_keys($tag_map) as $tag) {
	$all_tags[$tag]=$tag;
}
?>

<?php if (!empty($conf->global->ELB_ADD_FILES_TO_SOLR)) { ?>
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
<?php } ?>

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

<?php if ($totalnr) { ?>
    <form id="file-list-display-form" action="" method="get" >
        <?php
        echo $langs->trans('GroupFilesBy');
        echo '<input type="hidden" name="object_element" value="'.$object_element.'" >';
        echo '<input type="hidden" name="id" value="'.$id.'" >';
        echo ElbFileView::renderSelect($file_list_display, ElbFileGrouping::GROUP_FILES_PARAM, ElbFileGrouping::returnAvailableGroupingMethods())
        ?>
    </form>
<?php } ?>

<?php
// render uploaded files
if ($file_list_display == ElbFileGrouping::GROUP_FILES_BY_TAG) {
	$elbfile->getUploadedFiles($object->element, $object->id, $toolbox, $tag_map, $search_files, $restictDeleteFile);
} elseif (in_array($file_list_display, array(ElbFileGrouping::GROUP_FILES_DEFAULT, ElbFileGrouping::GROUP_FILES_BY_REV))) {
	$fetch_files = $elbfile->fetchUploadedFiles($object->element,$object->id, $search_files);
    $elbfile->renderFilesByRevision($fetch_files, $toolbox, $restictDeleteFile);
}
print "<br>";
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('select[name="<?php echo ElbFileGrouping::GROUP_FILES_PARAM ?>"]').on('change', function() {
			$('#file-list-display-form').submit();
		});
	});
</script>

<?php 

// ELB - add ajax modal dialog
if(!empty($conf->elbmultiupload->enabled)) {
    elbmultiupload_renderAjaxPopup();
}
