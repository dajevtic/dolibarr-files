<?php

define("REQUIRE_JQUERY_TIMEPICKER",true);

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

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