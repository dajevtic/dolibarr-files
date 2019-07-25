<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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

    /**
     * Method renders all attached files to an object
     *
     * @param   string      $objectElement      Object's element (e.g. 'propal', 'commande'...)
     * @param   int         $objectID           ID of object
     * @param   int         $toolbox            Flag if edit/delete buttons for files should be viewable
     * @param   string|null $sortFilesMethod    Sorting methods for files
     * @return  void
     */
    static function renderAttachedFilesForObject($objectElement, $objectID, $toolbox=1, $sortFilesMethod=null)
    {
        global $db, $langs;
        $elbFile = new ELbFile($db);
        $fetch_all_files = $elbFile->fetchUploadedFiles($objectElement, $objectID);

        if (is_array($fetch_all_files) && count($fetch_all_files)) {

            $action2 = GETPOST('action2');
            $fileid = GETPOST('rowid');
            $object_element = $objectElement;

            // array with revision/category
            $file_with_rev_categ = array();

            // array without revision/category
            $file_without_rev_categ = array();

            // flag if table should be collapsible
            $showTableCollapsible = true;

            // name for files table which has revision/category
            $nameForFilesWithRevisionOrCategory = '';

            // name for files table which is without revision/category
            $nameForFilesWithoutRevisionOrCategory = '';

            // sort files by revisions or categories
            if (in_array($sortFilesMethod, array(ElbFileGrouping::GROUP_FILES_BY_REV, ElbFileGrouping::GROUP_FILES_BY_TAG))) {

                // populate array of files with revisions
                if ($sortFilesMethod == ElbFileGrouping::GROUP_FILES_BY_REV) {

                    $nameForFilesWithRevisionOrCategory = $langs->trans('Revision');
                    $nameForFilesWithoutRevisionOrCategory = $langs->trans('WithoutRevision');

                    foreach ($fetch_all_files as $ind => $file) {
                        if ($file->fmrevision) {
                            $file_with_rev_categ[$file->fmrevision][] = $file;
                        }
                    }

                    // sort by latest revision
                    if (count($file_with_rev_categ)) {
                        krsort($file_with_rev_categ);
                    }

                    // populate array of files without revision
                    foreach ($fetch_all_files as $ind => $file) {
                        if (empty($file->fmrevision)) {
                            $file_without_rev_categ['no_assigned_rev_categ'][] = $file;
                        }
                    }
                }
                // populate array of files with categories
                elseif ($sortFilesMethod == ElbFileGrouping::GROUP_FILES_BY_TAG) {

                    $nameForFilesWithRevisionOrCategory = $langs->trans('Category');
                    $nameForFilesWithoutRevisionOrCategory = $langs->trans('Uncategorized');

                    $tag_map = ELbFileMapping::getObjectTags($object_element, $objectID);

                    if (is_array($tag_map)) {

                        // populate categorized array
                        foreach ($tag_map as $tag_name => $arr_assigned_fmaps) {
                            foreach ($arr_assigned_fmaps as $assigned_fmap_id) {
                                foreach ($fetch_all_files as $ind => $files) {
                                    if ($files->fmrowid == $assigned_fmap_id) {
                                        $file_with_rev_categ[$tag_name][] = $files;
                                    }
                                }
                            }
                        }

                        // populate uncategorized array
                        foreach ($fetch_all_files as $key => $resobject) {
                            foreach ($file_with_rev_categ as $tagname => $my_arr) {
                                foreach ($my_arr as $cindex => $res_key) {
                                    if ($key == $res_key) {
                                        continue 3;
                                    }
                                }
                            }
                            $file_without_rev_categ['no_assigned_rev_categ'][] = $resobject;
                        }
                    } else {
                        // populate array of files without revision
                        foreach ($fetch_all_files as $ind => $file) {
                            $file_without_rev_categ['no_assigned_rev_categ'][] = $file;
                        }
                    }
                }
            }
            // get files without sorting method
            else {
                foreach ($fetch_all_files as $ind => $file) {
                    $file_without_rev_categ['no_assigned_rev_categ'][] = $file;
                }
                $showTableCollapsible = false;
            }

            $counter=0;

            if (count($file_with_rev_categ)) {

                foreach ($file_with_rev_categ as $rev_categ => $my_arr)	{

                    $a_class = 'toggle-link expanded';
                    $span_class = 'ui-icon ui-icon-triangle-1-se';
                    $display = 'style="display: table-row-group;"';

                    print '<table class="border" width="100%">
					<tr class="position-subtable">
					<td colspan="<?php echo $coldisplay ?>">
					<table width="100%" class="elb-subtable">
					<thead>
					<tr>
					<th colspan="2" align="left">';

                    print '<a href="" onclick="toggleSubtable(this); return false;" class="' . $a_class . '">
				<span class="' . $span_class . '"></span>';
                    if ($nameForFilesWithRevisionOrCategory) {
                        print $nameForFilesWithRevisionOrCategory . ': ';
                    }
                    print $rev_categ;
                    print '</a>
					</th>
					</tr>
					</thead>		
					<tbody ' . $display . '>
						<tr>
							<td class="nobottom" colspan="2">';
                    print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';

                    include dol_buildpath('/elbmultiupload/tpl/files/table/thead.tpl.php');

                    print '<tbody>';

                    foreach ($my_arr as $key => $files_res_key) {
                        $obj = $files_res_key;

                        ($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

                        include dol_buildpath('/elbmultiupload/tpl/files/table/trow.tpl.php');

                        //$elbFile->getFileVersions($obj->fmrowid, $toolbox);
                        self::renderAttachedSubfilesObjectFile($obj->fmrowid, $objectElement, $toolbox);
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

            if (count($file_without_rev_categ) > 0) {
                foreach ($file_without_rev_categ as $without_ref => $my_arr)	{

                    if ($showTableCollapsible) {

                        $a_class = 'toggle-link expanded';
                        $span_class = 'ui-icon ui-icon-triangle-1-se';
                        $display = 'style="display: table-row-group;"';

                        print '<table class="border" width="100%">
                        <tr class="position-subtable">
                        <td colspan="<?php echo $coldisplay ?>">
                        <table width="100%" class="elb-subtable">
                        <thead>
                        <tr>
                        <th colspan="2" align="left">';
                            print '<a href="" onclick="toggleSubtable(this); return false;" class="' . $a_class . '">
                        <span class="' . $span_class . '"></span>';
                            if ($nameForFilesWithoutRevisionOrCategory) {
                                print $nameForFilesWithoutRevisionOrCategory;
                            }
                            print '</a>
                        </th>
                        </tr>
                        </thead>
                        <tbody ' . $display . '>
                            <tr>
                                <td class="nobottom" colspan="2">';
                    }

                    print '<table class="border listofdocumentstable" summary="listofdocumentstable" width="100%">';

                    include dol_buildpath('/elbmultiupload/tpl/files/table/thead.tpl.php');

                    print '<tbody>';

                    foreach ($my_arr as $key => $files_res_key) {
                        $obj = $files_res_key;

                        ($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;

                        include dol_buildpath('/elbmultiupload/tpl/files/table/trow.tpl.php');

                        self::renderAttachedSubfilesObjectFile($obj->fmrowid, $objectElement, $toolbox);
                    }

                    print '</tbody>';
                    print "</table>\n";

                    if ($showTableCollapsible) {
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

    /**
     * Method renders sub files of a file
     *
     * @param   int       $fileMapID          ID of file mapping
     * @param   string    $objectElement      Object's element
     * @param   int       $toolbox            Flag if edit/delete buttons for files should be viewable
     * @return  void
     */
    static function renderAttachedSubfilesObjectFile($fileMapID, $objectElement, $toolbox=1)
    {
        global $db, $langs;

        $elbFile = new ELbFile($db);

        $subFiles = $elbFile->getFileVersions($fileMapID);

        if (is_array($subFiles) && count($subFiles)) {

            $action2 = GETPOST('action2');
            $fileid = GETPOST('rowid');
            $object_element = $objectElement;

            print '<tr>';
            print '<td></td>';
            print '<td colspan="6">';

            $title=$langs->trans("FileVersion(s)");

            print '<div class="titre">'.$title.'</div>';
            print '<table class="border" summary="listofdocumentstable" width="100%">';

            include dol_buildpath('/elbmultiupload/tpl/files/table/thead.tpl.php');

            $i = 0;
            $var = false;
            foreach ($subFiles as $obj) {
                $var=!$var;
                ($action2 == 'editfile' &&  $fileid == $obj->fmrowid) ? $modef=true : $modef=false;
                $subfile = true;
                include dol_buildpath('/elbmultiupload/tpl/files/table/trow.tpl.php');
            }
            print "</table>\n";
            print '</td>';
            print '</tr>';
        }
    }

}