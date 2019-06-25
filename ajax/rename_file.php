<?php 

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.common.manager.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

$file_id = GETPOST('file_id');

$full_file_name = ElbCommonManager::fetchField('elb_file', 'name', $file_id);
$file_ext = strrchr($full_file_name, '.');
$file_name = strstr($full_file_name, $file_ext, true);

print '<input type="hidden" id="file_id" name="file_id" value="'. $file_id. '">';
print '<input type="hidden" id="file_ext" name="file_ext" value="'. $file_ext. '">';

print '<table class="nobordernopadding t-v-align">';

print '<tr>';
print '<td>';
$doleditor = new DolEditor('renamefile', $file_name, 100, 15, 'renamefile', '', false, false, false, 0, 0);
$doleditor->Create();
print '</td>';

print '</tr>';
print '<tr>';
print '<td>';
print '<a class="button elbbtn mt-10" onclick="renamefile()">'.$langs->trans('Save').'</a>';
print '</td>';
print '</tr>';

print '</table>';

print '<script type="text/javascript">';
print '
		function renamefile() {
			var file_id = $("#file_id").val();
			var file_ext = $("#file_ext").val();
			var new_file_name = $("#renamefile").val();

			var params = {
				file_id : file_id,
				new_file_name : new_file_name,
				file_ext : file_ext
			};

			elb_ajax_action("'.DOL_URL_ROOT.'/elbmultiupload/ajax/ajax.php","globalcard","rename_file",params);
		}';
print '</script>';

llxFooter();
$db->close();