<?php

define("REQUIRE_JQUERY_TIMEPICKER",true);

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.ajax.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.common.manager.class.php';
require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/lib/elbmultiupload.lib.php';

date_default_timezone_set($conf->global->MAIN_SERVER_TZ);

$hook=GETPOST('hook');
$action=GETPOST('action');
$params=GETPOST('params');
$formData=GETPOST('formData');

$hookmanager->initHooks(array($hook));
$parameters=array('params'=>$params, 'formData'=>$formData);
$object='';
$reshook = $hookmanager->executeHooks('doAjaxActions', $parameters, $object, $action);

print json_encode($hookmanager->resArray);