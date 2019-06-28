<?php

/**
 *    \file       htdocs/elbmultiupload/card.php
 *    \ingroup    elbmultiupload
 *    \brief      Management page of object additional files
 */

//ELB change version of select2 to newer
define("JQUERY_MULTISELECT_V4", 1);

require_once '../main.inc.php';

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// user needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

// proposal needed files
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';

// customer order needed files
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php';

// categories needed files
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

// elbmultiupload module needed files
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/lib/elbmultiupload.lib.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';

$langs->load('companies');
$langs->load('other');

$langs->load("users");
$langs->load("propal");
$langs->load("orders");

$action = GETPOST('action');
$confirm = GETPOST('confirm');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');
$object_element = GETPOST('object_element');

if (empty($object_element)) {
    // type of object is needed
    $result = restrictedArea($user, 'noexistingobjectelement', $id, '');
} else {
    $result = restrictedArea($user, $object_element, $id, '');
}

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

// object must be fetched by id or reference
if ($id == '' && $ref == '') {
    setEventMessage($langs->trans('MissingObjectIDorRef'), 'errors');
    dol_print_error('','Bad parameter');
    header('Location: index.php');
    exit;
}

// set up object, object's name and icon depending of current object element/type
if ($object_element=='commande') {
    $typeOfObject = 'Commande';
    $tabName = 'CustomerOrder';
    $tabIcon = 'order';
} elseif ($object_element=='propal'){
    $typeOfObject = 'Propal';
    $tabName = 'Proposal';
    $tabIcon = 'propal';
} elseif ($object_element=='user'){
    $typeOfObject = 'User';
    $tabName = 'User';
    $tabIcon = 'user';
}

// fetch object
//$objectClassname = ucfirst($object_element);
$object = new $typeOfObject($db);
$resFetchObject = $object->fetch($id, $ref);
if (!($resFetchObject > 0)) {
    $errFetching = $langs->trans('ErrorFetchingObject');
    setEventMessage($errFetching, 'errors');
    dol_print_error('','Bad parameter');
    header('Location: index.php');
    exit;
}

// check up and process actions on object's additional files
ELbFile::processFileActions();

/**
 * Actions
 */


/**
 * View
 */
llxHeader('', $langs->trans($tabName).' - '.$langs->trans('AdditionalFiles'), '');

$form = new Form($db);

// fetch object's third party
$object->fetch_thirdparty();

// get tabs method of current object
$objectTabsMethod = $object_element.'_prepare_head';
$head = $objectTabsMethod($object);

dol_fiche_head($head, 'additionalfiles', $langs->trans('AdditionalFiles'), 0, $tabIcon);

print '<table class="border" width="100%">';

$linkback = '';

// Ref of object
print '<tr><td width="30%">' . $langs->trans('Ref') . '</td><td colspan="3">';
print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
print '</td></tr>';

if (is_object($object->thirdparty)) {
    print '<tr><td>' . $langs->trans('Company') . '</td><td colspan="3">' . $object->thirdparty->getNomUrl(1) . '</td></tr>';
}
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

llxFooter();
$db->close();