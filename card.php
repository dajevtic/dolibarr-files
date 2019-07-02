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

// user group needed files
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

// holiday needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/holiday.lib.php';
require_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';

// expense needed files
require_once DOL_DOCUMENT_ROOT . '/core/lib/expensereport.lib.php';
require_once DOL_DOCUMENT_ROOT . '/expensereport/class/expensereport.class.php';

// member needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';

// third party needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// proposal needed files
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';

// customer order needed files
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php';

// contract needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/contract.lib.php';
require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';

// shipment needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/sendings.lib.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';

// intervention needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/fichinter.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';

// supply order needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/fourn.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';

// price request needed files
require_once DOL_DOCUMENT_ROOT . '/core/lib/supplier_proposal.lib.php';
require_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';

// invoice needed files
require_once DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

// sales tax payment needed files
require_once DOL_DOCUMENT_ROOT.'/core/lib/vat.lib.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';

// categories needed files
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

// elbmultiupload module needed files
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/lib/elbmultiupload.lib.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';

$langs->load('compta');
$langs->load('companies');
$langs->load('other');
$langs->load("users");
$langs->load("propal");
$langs->load("holiday");
$langs->load("orders");
$langs->load("contracts");
$langs->load("interventions");
$langs->load("supplier_proposal");
$langs->load("bills");

$action = GETPOST('action');
$confirm = GETPOST('confirm');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');
$object_element = GETPOST('object_element');

$tableandshare = '';
$feature2 = '';

// checking access per object can differ from the object's element/type
$checkUpAccessForObject = $object_element;
if ($object_element=='member') {
    $checkUpAccessForObject = 'adherent';
} elseif ($object_element=='shipping') {
    $checkUpAccessForObject = 'expedition';
} elseif ($object_element=='fichinter') {
    $checkUpAccessForObject = 'ficheinter';
} elseif ($object_element=='order_supplier') {
    $checkUpAccessForObject = 'fournisseur';
} elseif ($object_element=='tva') {
    $checkUpAccessForObject = 'tax';
    $feature2 = 'charges';
}

if (empty($checkUpAccessForObject)) {
    // type of object is needed
    $result = restrictedArea($user, 'dolnoexistingobjectelement', $id, '');
} else {
    $result = restrictedArea($user, $checkUpAccessForObject, $id, $tableandshare, $feature2);
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

// prefix of method for tab of current object
$objectTabsMethodPrefix = $object_element;

/* set up object, object's name and icon depending of current object element/type */
// customer order module
if ($object_element=='commande') {
    $typeOfObject = 'Commande';
    $tabName = 'CustomerOrder';
    $tabIcon = 'order';
}
// proposal module
elseif ($object_element=='propal') {
    $typeOfObject = 'Propal';
    $tabName = 'Proposal';
    $tabIcon = 'propal';
}
// user module
elseif ($object_element=='user') {
    $typeOfObject = 'User';
    $tabName = 'User';
    $tabIcon = 'user';
}
// user group module
elseif ($object_element=='usergroup') {
    $typeOfObject = 'UserGroup';
    $tabName = 'UserGroup';
    $tabIcon = 'group';
    $objectTabsMethodPrefix = 'group';
}
// holiday/leaves module
elseif ($object_element=='holiday') {
    $typeOfObject = 'Holiday';
    $tabName = 'CPTitreMenu';
    $tabIcon = 'holiday';
    $objectTabsMethodPrefix = 'holiday';
}
// expense module
elseif ($object_element=='expensereport') {
    $typeOfObject = 'ExpenseReport';
    $tabName = 'ExpenseReport';
    $tabIcon = 'trip';
    $objectTabsMethodPrefix = 'expensereport';
}
// members module
elseif ($object_element=='member') {
    $typeOfObject = 'Adherent';
    $tabName = 'Member';
    $tabIcon = 'user';
    $objectTabsMethodPrefix = 'member';
}
// third party module
elseif ($object_element=='societe') {
    $typeOfObject = 'Societe';
    $tabName = 'ThirdParty';
    $tabIcon = 'company';
    $objectTabsMethodPrefix = 'societe';
}
// contract module
elseif ($object_element=='contrat') {
    $typeOfObject = 'Contrat';
    $tabName = 'Contract';
    $tabIcon = 'contract';
    $objectTabsMethodPrefix = 'contract';
}
// shipment module
elseif ($object_element=='shipping') {
    $typeOfObject = 'Expedition';
    $tabName = 'Shipment';
    $tabIcon = 'sending';
    $objectTabsMethodPrefix = 'shipping';
}
// intervention module
elseif ($object_element=='fichinter') {
    $typeOfObject = 'Fichinter';
    $tabName = 'InterventionCard';
    $tabIcon = 'intervention';
    $objectTabsMethodPrefix = 'fichinter';
}
// supply order module
elseif ($object_element=='order_supplier') {
    $typeOfObject = 'CommandeFournisseur';
    $tabName = 'OrderCard';
    $tabIcon = 'order';
    $objectTabsMethodPrefix = 'ordersupplier';
}
// supplier_proposal module (price request)
elseif ($object_element=='supplier_proposal') {
    $typeOfObject = 'SupplierProposal';
    $tabName = 'CommRequest';
    $tabIcon = 'supplier_proposal';
    $objectTabsMethodPrefix = 'supplier_proposal';
}
// invoice module
elseif ($object_element=='facture') {
    $typeOfObject = 'Facture';
    $tabName = 'InvoiceCustomer';
    $tabIcon = 'bill';
    $objectTabsMethodPrefix = 'facture';
}
// sales tax payment
elseif ($object_element=='tva') {
    $typeOfObject = 'Tva';
    $tabName = 'VATPayment';
    $tabIcon = 'payment';
    $objectTabsMethodPrefix = 'vat';
}


// fetch object
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
$objectTabsMethod = $objectTabsMethodPrefix.'_prepare_head';
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

// @TODO set this part!!!
$modulepart = 'commande';
$permission = $user->rights->commande->creer;
$param = '&id=' . $object->id;

include_once DOL_DOCUMENT_ROOT . '/elbmultiupload/core/tpl/document_actions_post_headers.tpl.php';

llxFooter();
$db->close();