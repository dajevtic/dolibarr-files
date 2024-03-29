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
        $elbFileMapping = new ELbFileMapping($db);
        $fetch_all_files = $elbFileMapping->fetchUploadedFiles($objectElement, $objectID);

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

                        $fmapIDWithTag = [];

                        // populate categorized array
                        foreach ($tag_map as $tag_name => $arr_assigned_fmaps) {
                            foreach ($arr_assigned_fmaps as $assigned_fmap_id) {
                                foreach ($fetch_all_files as $ind => $files) {
                                    if ($files->fmrowid == $assigned_fmap_id) {
                                        $file_with_rev_categ[$tag_name][] = $files;
                                        if (!in_array($assigned_fmap_id, $fmapIDWithTag)) {
                                            $fmapIDWithTag[] = $assigned_fmap_id;
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($fetch_all_files as $key => $resobject) {
                            if (!in_array($resobject->fmrowid, $fmapIDWithTag)) {
                                $file_without_rev_categ['no_assigned_rev_categ'][] = $resobject;
                            }
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

        $elbFileMapping = new ELbFileMapping($db);

        $subFiles = $elbFileMapping->getFileVersions($fileMapID);

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

    /**
     * Method renders multiupload button
     *
     * @param   string  $object_type    Object's element
     * @param   int     $object_id      ID of object
     * @return  void
     */
    static function showMultiUploadButton($object_type, $object_id)
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
        $out .= '<div id="mulitplefileuploader">'.$langs->trans("UploadFiles").'</div><br/>';
        $out .= '<script>
					$(document).ready(function()
					{
					var formData = $("form").serializeArray();
					var settings = {
						url: "'.$upload_handler.'",
						method: "POST",
						//allowedTypes:"jpg,jpeg,png,gif,doc,pdf,zip",
						fileName: "elb_file",';
        $out .= '		multiple: true,';
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
				$("#mulitplefileuploader").uploadFile(settings);
			});
			</script>';
        print $out;
    }

}