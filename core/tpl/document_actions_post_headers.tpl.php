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

$formfile=new FormFile($db);

// show multiupload button
print ElbFileView::showMultiUploadButton($object->element, $object->id);

?>

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
    <br/>
<?php } ?>

<?php

// render uploaded files
if ($totalnr) {
    print '<div class="object-attached-files">';
    ElbFileView::renderAttachedFilesForObject($object->element, $object->id, $toolbox, $file_list_display);
    print '<div>';
}
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('select[name="<?php echo ElbFileGrouping::GROUP_FILES_PARAM ?>"]').on('change', function() {
			$('#file-list-display-form').submit();
		});
	});
</script>

<?php
// ajax modal dialog
if(!empty($conf->elbmultiupload->enabled)) {
    elbmultiupload_renderAjaxPopup();
}