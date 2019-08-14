<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
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
 *      \file       htdocs/elbmultiupload/categories/index.php
 *      \ingroup    elbmultiupload
 *      \brief      Home page of additional files category area
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/treeview.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.category.class.php';

// Load translation files required by the page
$langs->load("categories");

if (! $user->rights->categorie->lire) accessforbidden();

$id=GETPOST('id','int');
$type=(GETPOST('type','aZ09') ? GETPOST('type','aZ09') : Categorie::TYPE_PRODUCT);
$catname=GETPOST('catname','alpha');


/*
 * View
 */

$categstatic = new Categorie($db);
$form = new Form($db);

$title=$langs->trans("AdditionalFilesCategoriesArea");

$type = $typetext = ElbFileCategory::getFileCategoryID();

$arrayofjs=array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.js', '/includes/jquery/plugins/jquerytreeview/lib/jquery.cookie.js');
$arrayofcss=array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.css');

llxHeader('',$title,'','',0,0,$arrayofjs,$arrayofcss);

if ($type) {
    $newcardbutton = '<a class="butActionNew" href="' . DOL_URL_ROOT . '/categories/card.php?action=create&type=' . $type . '&backtopage=' . urlencode($_SERVER["PHP_SELF"] . '?type=' . $type) . '"><span class="valignmiddle">' . $langs->trans("NewCategory") . '</span>';
    $newcardbutton .= '<span class="fa fa-plus-circle valignmiddle"></span>';
    $newcardbutton .= '</a>';
}

print load_fiche_titre($title, $newcardbutton);

if (empty($type)) {
    die($langs->trans('SetUpCategoryIDInElbmultiuploadModule'));
}

print '<div class="fichecenter"><br>';

// Charge tableau des categories
$cate_arbo = $categstatic->get_full_arbo($typetext);

// Define fulltree array
$fulltree=$cate_arbo;

// Define data (format for treeview)
$data=array();
$data[] = array('rowid'=>0,'fk_menu'=>-1,'title'=>"racine",'mainmenu'=>'','leftmenu'=>'','fk_mainmenu'=>'','fk_leftmenu'=>'');
foreach($fulltree as $key => $val)
{
    $categstatic->id=$val['id'];
    $categstatic->ref=$val['label'];
    $categstatic->color=$val['color'];
    $categstatic->type=$type;
    $li=$categstatic->getNomUrl(1,'',60);
    $desc=dol_htmlcleanlastbr($val['description']);

    $data[] = array(
        'rowid'=>$val['rowid'],
        'fk_menu'=>$val['fk_parent'],
        'entry'=>'<table class="nobordernopadding centpercent"><tr><td><span class="noborderoncategories" '.($categstatic->color?' style="background: #'.$categstatic->color.';"':' style="background: #aaa"').'>'.$li.'</span></td>'.
            //'<td width="50%">'.dolGetFirstLineOfText($desc).'</td>'.
            '<td align="right" width="20px;"><a href="'.DOL_URL_ROOT.'/categories/viewcat.php?id='.$val['id'].'&type='.$type.'">'.img_view().'</a></td>'.
            '</tr></table>'
    );
}


print '<table class="liste nohover" width="100%">';
print '<tr class="liste_titre"><td>'.$langs->trans("Categories").'</td><td></td><td align="right">';
if (! empty($conf->use_javascript_ajax))
{
    print '<div id="iddivjstreecontrol"><a class="notasortlink" href="#">'.img_picto('','object_category').' '.$langs->trans("UndoExpandAll").'</a> | <a class="notasortlink" href="#">'.img_picto('','object_category-expanded').' '.$langs->trans("ExpandAll").'</a></div>';
}
print '</td></tr>';

$nbofentries=(count($data) - 1);

if ($nbofentries > 0)
{
    print '<tr class="pair"><td colspan="3">';
    tree_recur($data,$data[0],0);
    print '</td></tr>';
}
else
{
    print '<tr class="pair">';
    print '<td colspan="3"><table class="nobordernopadding"><tr class="nobordernopadding"><td>'.img_picto_common('','treemenu/branchbottom.gif').'</td>';
    print '<td valign="middle">';
    print $langs->trans("NoCategoryYet");
    print '</td>';
    print '<td>&nbsp;</td>';
    print '</table></td>';
    print '</tr>';
}

print "</table>";

print '</div>';

// End of page
llxFooter();
$db->close();