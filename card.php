<?php

/**
 *    \file       htdocs/elbmultiupload/card.php
 *    \ingroup    elbmultiupload
 *    \brief      Management page of object additional files
 */

//ELB change version of select2 to newer
define("JQUERY_MULTISELECT_V4", 1);

require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';

require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';

require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/lib/elbmultiupload.lib.php';

require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';


$langs->load('companies');
$langs->load('other');

$action = GETPOST('action');
$confirm = GETPOST('confirm');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

// Security check
if ($user->societe_id) {
    $action = '';
    $socid = $user->societe_id;
}

// @TODO - recheck restrict access
$result = restrictedArea($user, 'commande', $id, '');

// Get parameters
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "name";

// @TODO - fetch object depending of object's tab..
$object = new Propal($db);

// check up and process actions on object's additional files
ELbFile::processFileActions();

/**
 * Actions
 */


/**
 * View
 */
llxHeader('', $langs->trans('AdditionalFiles'), '');

$form = new Form($db);

if ($id > 0 || !empty($ref)) {
    if ($object->fetch($id, $ref)) {

        $object->fetch_thirdparty();

        $head = propal_prepare_head($object);

        dol_fiche_head($head, 'additionalfiles', $langs->trans('AdditionalFiles'), 0, 'order');

        print '<table class="border" width="100%">';

        $linkback = '';

        // Ref of object
        print '<tr><td width="30%">' . $langs->trans('Ref') . '</td><td colspan="3">';
        print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
        print '</td></tr>';

        print '<tr><td>' . $langs->trans('Company') . '</td><td colspan="3">' . $object->thirdparty->getNomUrl(1) . '</td></tr>';
        $totalnr = ELbFileMapping::countLinkedFilesByObjectType($object->element, $object->id);
        $totalsize = ELbFileMapping::getAttachedFilesSize($object->element, $object->id);
        print '<tr><td>' . $langs->trans("NbOfAttachedFiles") . '</td><td colspan="3">' . $totalnr . '</td></tr>';
        print '<tr><td>' . $langs->trans("TotalSizeOfAttachedFiles") . '</td><td colspan="3">' . dol_print_size($totalsize, 1, 1) . '</td></tr>';

        print "</table>\n";
        print "</div>\n";

        $modulepart = 'commande';
        $permission = $user->rights->commande->creer;
        $param = '&id=' . $object->id;

        include_once DOL_DOCUMENT_ROOT . '/elbmultiupload/core/tpl/document_actions_post_headers.tpl.php';

    } else {
        dol_print_error($db);
    }
} else {
    header('Location: index.php');
    exit;
}

llxFooter();
$db->close();