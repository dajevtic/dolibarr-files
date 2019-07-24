<?php

class ElbFileView
{
    /**
     * Render html select box
     *
     * @param   string    $selected     Selected option
     * @param   string    $htmlname     Name of select element
     * @param   array     $mapArr       Array of key and values for select options
     * @return  string
     */
    static function renderSelect($selected='', $htmlname='select_element', $mapArr=array())
    {
        $out = '';

        $out.= '<select class="flat" name="'.$htmlname.'">';
        foreach($mapArr as $key => $value) {
            ($key == $selected) ? $optionSelected=' selected="selected" ': $optionSelected='';
            $out.= '<option value="'.$key.'" '.$optionSelected.'>'.$value.'</option>';
        }
        $out.= '</select>';

        return $out;
    }

    static function renderAttachedFilesForObject($objectElement, $objectID, $toolbox=1, $sortFilesMethod=null, $objectFilesCategories=[])
    {
        global $db, $langs;
        $elbFile = new ELbFile($db);
        $fetch_all_files = $elbFile->fetchUploadedFiles($objectElement, $objectID);

        if (is_array($fetch_all_files) && count($fetch_all_files)) {

            $action2 = GETPOST('action2');
            $fileid = GETPOST('rowid');
            $object_element = $objectElement;

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
            if (ElbFileSession::getGroupFilesMethod() == ElbFileGrouping::GROUP_FILES_BY_REV) {
                foreach ($fetch_all_files as $ind => $file) {
                    if (empty($file->fmrevision)) {
                        $file_without_rev['no_revision'][] = $file;
                    }
                }
            } else {
                foreach ($fetch_all_files as $ind => $file) {
                    $file_without_rev['no_revision'][] = $file;
                }
            }

            $counter=0;

            if (count($file_with_rev) > 0 && ElbFileSession::getGroupFilesMethod() == ElbFileGrouping::GROUP_FILES_BY_REV) {

                foreach ($file_with_rev as $rev => $my_arr)	{

                    if (!$counter) {
                        $a_class = 'toggle-link expanded';
                        $span_class = 'ui-icon ui-icon-triangle-1-se';
                        $display = 'style="display: table-row-group;"';
                    } else {
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

                    foreach ($my_arr as $key => $files_res_key) {
                        $obj = $files_res_key;

                        ($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

                        include dol_buildpath('/elbmultiupload/tpl/files/position/file.tpl.php');

                        $elbFile->getFileVersions($obj->fmrowid, $toolbox);
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

            if (count($file_without_rev) > 0) {
                foreach ($file_without_rev as $without_ref => $my_arr)	{
                    if (!$counter)	{
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

                    if (ElbFileSession::getGroupFilesMethod() == ElbFileGrouping::GROUP_FILES_BY_REV) {
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
                    }

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

                        $elbFile->getFileVersions($obj->fmrowid, $toolbox);
                    }

                    print '</tbody>';
                    print "</table>\n";

                    if (ElbFileSession::getGroupFilesMethod() == ElbFileGrouping::GROUP_FILES_BY_REV) {
                        print '
							</td>
						</tr>
						</tbody>
						</table>
						</td>
						</tr>
						</table>';
                    }
                    $counter++;
                }
            }
        }
    }
}