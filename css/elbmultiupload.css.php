<?php
/* Copyright (C) 2019-... LiveMediaGroup - Milos Petkovic <milos.petkovic@livemediagroup.de>
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

/**
 * \file    htdocs/modulebuilder/template/css/mymodule.css.php
 * \ingroup mymodule
 * \brief   CSS file for module MyModule.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);          // File must be accessed by logon page so without login
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/../main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

session_cache_limiter('public');
// false or '' = keep cache instruction added by server
// 'public'  = remove cache instruction added by server and if no cache-control added later, a default cache delay (10800) will be added by PHP.

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && ! empty($_SESSION['dol_login']))
{
    $user->fetch('',$_SESSION['dol_login']);
	$user->getrights();
}*/


// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=10800, public, must-revalidate');
else header('Cache-Control: no-cache');

?>

.ajax-file-upload-statusbar {
    border: 1px solid #ccc;
    margin-top: 10px;
    margin-right: 10px;
    margin: 5px;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
    padding: 0;
    width: 350px !important;
}
.ajax-file-upload-filename {
    width: 100%;
    height: auto;
    margin: 4px 5px 3px 10px;
    color: #444;
}
.ajax-file-upload-progress {
    margin: 0 10px 5px 10px;
    position: relative;
    width: 250px;
    border: 1px solid #ccc;
    padding: 1px;
    border-radius: 3px;
    display: inline-block
}
.ajax-file-upload-bar {
    background-color: #919FB1;
    width: 0;
    height: 8px;
    border-radius: 3px;
    color:#FFFFFF;
}
.ajax-file-upload-percent {
    position: absolute;
    display: inline-block;
    top: 3px;
    left: 48%
}
.ajax-file-upload-red,
.ajax-file-upload-red:hover {
    /*-moz-box-shadow: inset 0 39px 0 -24px #e67a73;
    -webkit-box-shadow: inset 0 39px 0 -24px #e67a73;
    box-shadow: inset 0 39px 0 -24px #e67a73;*/
    border-radius: 0 5px;
    display: inline-block;
    font-family: arial;
    font-size: 12px;
    font-weight: normal;
    padding: 1px 15px;
    text-decoration: none;
    text-shadow: 0 1px 0 #b23e35;
    cursor: pointer;
    vertical-align: top;
    margin: -6px 0 4px;
    border: 1px solid #e67a73;
    color: #444;
    background: #fbfbfb; /* Old browsers */
    /* IE9 SVG, needs conditional override of 'filter' to 'none' */
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIxJSIgc3RvcC1jb2xvcj0iI2ZiZmJmYiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9Ijk5JSIgc3RvcC1jb2xvcj0iI2RlZGVkZSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgPC9saW5lYXJHcmFkaWVudD4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBmaWxsPSJ1cmwoI2dyYWQtdWNnZy1nZW5lcmF0ZWQpIiAvPgo8L3N2Zz4=);
    background: -moz-linear-gradient(top, #fbfbfb 1%, #dedede 99%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#fbfbfb), color-stop(99%,#dedede)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* IE10+ */
    background: linear-gradient(to bottom, #fbfbfb 1%,#dedede 99%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fbfbfb', endColorstr='#dedede',GradientType=0 ); /* IE6-8 */
    background-image: url("<?php echo DOL_URL_ROOT.'/theme/eldy/img/stcomm-1.png'; ?>");
    background-position: center center;
    background-repeat: no-repeat;
    text-indent: -1000em;
}
.ajax-file-upload-red:hover {
    background-color: #DEE7EC;
}
.ajax-file-upload-green,
.ajax-file-upload-green:hover {
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 0 5px;
    box-shadow: 2px 2px 3px #DDDDDD;
    font-family: arial,tahoma,verdana,helvetica;
    vertical-align: top;
    margin: 0;
    padding: 1px 15px;
    margin: -6px 0 4px;
    height: auto;
    font-size: 12px;
    font-weight: normal;
    color: #444;
    background: #fbfbfb; /* Old browsers */
    /* IE9 SVG, needs conditional override of 'filter' to 'none' */
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIxJSIgc3RvcC1jb2xvcj0iI2ZiZmJmYiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9Ijk5JSIgc3RvcC1jb2xvcj0iI2RlZGVkZSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgPC9saW5lYXJHcmFkaWVudD4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBmaWxsPSJ1cmwoI2dyYWQtdWNnZy1nZW5lcmF0ZWQpIiAvPgo8L3N2Zz4=);
    background: -moz-linear-gradient(top, #fbfbfb 1%, #dedede 99%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#fbfbfb), color-stop(99%,#dedede)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* IE10+ */
    background: linear-gradient(to bottom, #fbfbfb 1%,#dedede 99%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fbfbfb', endColorstr='#dedede',GradientType=0 ); /* IE6-8 */
    background-image: url("<?php echo DOL_URL_ROOT.'/theme/eldy/img/tick.png'; ?>");
    background-position: center center;
    background-repeat: no-repeat;
    text-indent: -1000em;
}
.ajax-file-upload-green:hover {
    background-color: #DEE7EC;
}
.ajax-file-upload {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 16px;
    font-weight: bold;
    padding: 15px 20px;
    cursor:pointer;
    line-height:20px;
    height:25px;
    margin:0 10px 10px 0;
    display: inline-block;
    background: #fff;
    border: 1px solid #e8e8e8;
    color: #888;
    text-decoration: none;
    border-radius: 3px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    -moz-box-shadow: 0 2px 0 0 #e8e8e8;
    -webkit-box-shadow: 0 2px 0 0 #e8e8e8;
    box-shadow: 0 2px 0 0 #e8e8e8;
    padding: 6px 10px 4px 10px;
    color: #fff;
    background: #2f8ab9;
    border: none;
    -moz-box-shadow: 0 2px 0 0 #13648d;
    -webkit-box-shadow: 0 2px 0 0 #13648d;
    box-shadow: 0 2px 0 0 #13648d;
    vertical-align:middle;
    /*text-indent: -1000em;*/
}
.ajax-file-upload:hover {
    background: #3396c9;
    -moz-box-shadow: 0 2px 0 0 #15719f;
    -webkit-box-shadow: 0 2px 0 0 #15719f;
    box-shadow: 0 2px 0 0 #15719f;
}
.ajax-upload-dragdrop
{
    border:1px dotted #ccc;
    color: #DADCE3;
    text-align:left;
    vertical-align:middle;
    padding:5px 50px 0px 5px;
    width: 96% !important;
}
.ajax-upload-dragdrop > span {
    display: none;
}
.ajax-file-upload-statusbar {
    /*display: none;*/
}
.ajax-file-upload,
.ajax-file-upload:hover {
    border: 1px solid #C0C0C0;
    border-radius: 0 5px;
    box-shadow: 2px 2px 3px #DDDDDD;
    font-family: arial,tahoma,verdana,helvetica;
    margin: 0;
    padding: 1px 15px;
    margin-bottom: 4px;
    height: auto;
    font-size: 12px;
    font-weight: normal;
    color: #444;
    background: #fbfbfb; /* Old browsers */
    /* IE9 SVG, needs conditional override of 'filter' to 'none' */
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIxJSIgc3RvcC1jb2xvcj0iI2ZiZmJmYiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9Ijk5JSIgc3RvcC1jb2xvcj0iI2RlZGVkZSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgPC9saW5lYXJHcmFkaWVudD4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBmaWxsPSJ1cmwoI2dyYWQtdWNnZy1nZW5lcmF0ZWQpIiAvPgo8L3N2Zz4=);
    background: -moz-linear-gradient(top, #fbfbfb 1%, #dedede 99%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#fbfbfb), color-stop(99%,#dedede)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #fbfbfb 1%,#dedede 99%); /* IE10+ */
    background: linear-gradient(to bottom, #fbfbfb 1%,#dedede 99%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fbfbfb', endColorstr='#dedede',GradientType=0 ); /* IE6-8 */
    /*text-indent: -1000em;*/
}
.ajax-file-upload:hover {
    background: #DEE7EC;
}
.ufpuptr {
    display: inline-block;
    width: 13px;
    height: 13px;
    margin: 4px 0 0 4px;
    cursor: pointer;
    background: url("<?php echo DOL_URL_ROOT.'/theme/eldy/img/uparrow.png'; ?>") no-repeat 0 0;
    overflow: hidden;
}
.pushright {
    float: right;
}

.td-file-nr {
    width: 40px;
}
.td-file-desc {
    width: 200px;
}
.td-file-rev {
    width: 100px;
}
.td-file-size {
    width: 100px;
}
.td-file-modif {
    width: 150px;
}
.td-file-user {
    width: 80px;
}
.td-file-toolbox {
    width: 100px;
}
.object-attached-files td {
    vertical-align: top !important;
}