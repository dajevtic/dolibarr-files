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

/**
 * \file    htdocs/elbmultiupload/class/actions_elbmultiupload.class.php
 * \ingroup elbmultiupload
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsElbmultiupload
 */
class ActionsElbmultiupload
{
    /**
     * @var DoliDB Database handler.
     */
    public $db;

    /**
     * @var string Error code (or message)
     */
    public $error = '';

    /**
     * @var array Errors
     */
    public $errors = array();


    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = array();

    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public $resprints;


    /**
     * Constructor
     *
     *  @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * Execute action
     *
     * @param	array			$parameters		Array of parameters
     * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param	string			$action      	'add', 'update', 'view'
     * @return	int         					<0 if KO,
     *                           				=0 if OK but we want to process standard actions too,
     *                            				>0 if OK and we want to replace standard actions.
     */
    function getNomUrl($parameters,&$object,&$action)
    {
        global $db,$langs,$conf,$user;
        $this->resprints = '';
        return 0;
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))	    // do something only for the context 'somecontext1' or 'somecontext2'
        {
            // Do what you want here...
            // You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
        }

        if (! $error) {
            $this->results = array('myreturn' => 999);
            $this->resprints = 'A text to show';
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }


    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function doMassActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
        {
            foreach($parameters['toselect'] as $objectid)
            {
                // Do action on each object id
            }
        }

        if (! $error) {
            $this->results = array('myreturn' => 999);
            $this->resprints = 'A text to show';
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }


    /**
     * Overloading the addMoreMassActions function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
        {
            $this->resprints = '<option value="0"'.($disabled?' disabled="disabled"':'').'>'.$langs->trans("MyModuleMassAction").'</option>';
        }

        if (! $error) {
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }

    /**
     * Execute action
     *
     * @param	array	$parameters		Array of parameters
     * @param   Object	$object		   	Object output on PDF
     * @param   string	$action     	'add', 'update', 'view'
     * @return  int 		        	<0 if KO,
     *                          		=0 if OK but we want to process standard actions too,
     *  	                            >0 if OK and we want to replace standard actions.
     */
    function beforePDFCreation($parameters, &$object, &$action)
    {
        global $conf, $user, $langs;
        global $hookmanager;

        $outputlangs=$langs;

        $ret=0; $deltemp=array();
        dol_syslog(get_class($this).'::executeHooks action='.$action);

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
        {

        }

        return $ret;
    }

    /**
     * Execute action
     *
     * @param	array	$parameters		Array of parameters
     * @param   Object	$pdfhandler   	PDF builder handler
     * @param   string	$action     	'add', 'update', 'view'
     * @return  int 		        	<0 if KO,
     *                          		=0 if OK but we want to process standard actions too,
     *  	                            >0 if OK and we want to replace standard actions.
     */
    function afterPDFCreation($parameters, &$pdfhandler, &$action)
    {
        global $conf, $user, $langs;
        global $hookmanager;

        $outputlangs=$langs;

        $ret=0; $deltemp=array();
        dol_syslog(get_class($this).'::executeHooks action='.$action);

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
        {

        }

        return $ret;
    }

    /**
     * Method for ajax actions
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @throws Exception
     */
    function doAjaxActions($parameters, &$object, &$action, $hookmanager)
    {
        global $user;

        dol_syslog("CALLING AJAX ELBMULTIUPLOAD ACTION user=" . $user->id . " object=" . (property_exists($object, 'element') ? $object->element : "unknown") . " action=$action params=" . print_r($parameters, true));

        $context = $parameters['currentcontext'];
        parse_str($parameters['formData'], $formData);

        // elbmultiupload action controller
        $action_controller_class_name = "Elbmultiupload" . ucfirst($context) . "Action";
        $action_controller = DOL_DOCUMENT_ROOT . "/elbmultiupload/class/action/" . $action_controller_class_name . ".php";
        if (file_exists($action_controller)) {
            require_once $action_controller;
            $actionControler = new $action_controller_class_name;
            if (method_exists($actionControler, $action)) {
                define("DO_AJAX_ACTION",true);
                $this->results = $action_controller_class_name::$action($object, $parameters);
                return;
            }
        }
    }

    /**
     * Get link to the object by module name and file's relative path
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     * @throws Exception
     */
	function getObjectLink($parameters, &$object, &$action, $hookmanager)
	{
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file_mapping.class.php';

		global $conf, $db;
		$modulepart = $parameters['modulepart'];
		$relativefile = $parameters['relativefile'];
		if ($modulepart == 'elbmultiupload' && !empty($relativefile)) {

			$elbfilemap = new ELbFileMapping($db);
			$res = $elbfilemap->fetchFromEcmfilesPath($relativefile);

			if ($res > 0) {
				$object_id = $elbfilemap->object_id;
				$modulepart = $elbfilemap->object_type;

				if ($modulepart == "produit") $modulepart = "product";
				if ($modulepart == "propale") $modulepart = "propal";

				if ($modulepart == 'company') {
					include_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
					$object_instance = new Societe($db);
				} else if ($modulepart == 'invoice') {
					include_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
					$object_instance = new Facture($db);
				} else if ($modulepart == 'invoice_supplier') {
					include_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
					$object_instance = new FactureFournisseur($db);
				} else if ($modulepart == 'propal') {
					include_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
					$object_instance = new Propal($db);
				} else if ($modulepart == 'supplier_proposal') {
					include_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';
					$object_instance = new SupplierProposal($db);
				} else if ($modulepart == 'commande') {
					include_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
					$object_instance = new Commande($db);
				} else if ($modulepart == 'order_supplier') {
					include_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';
					$object_instance = new CommandeFournisseur($db);
				} else if ($modulepart == 'contract') {
					include_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';
					$object_instance = new Contrat($db);
				} else if ($modulepart == 'product') {
					include_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
					$object_instance = new Product($db);
				} else if ($modulepart == 'tax') {
					include_once DOL_DOCUMENT_ROOT . '/compta/sociales/class/chargesociales.class.php';
					$object_instance = new ChargeSociales($db);
				} else if ($modulepart == 'project') {
					include_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
					$object_instance = new Project($db);
				} else if ($modulepart == 'fichinter') {
					include_once DOL_DOCUMENT_ROOT . '/fichinter/class/fichinter.class.php';
					$object_instance = new Fichinter($db);
				} else if ($modulepart == 'user') {
					include_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
					$object_instance = new User($db);
				} else if ($modulepart == 'expensereport') {
					include_once DOL_DOCUMENT_ROOT . '/expensereport/class/expensereport.class.php';
					$object_instance = new ExpenseReport($db);
				} else if ($modulepart == 'holiday') {
					include_once DOL_DOCUMENT_ROOT . '/holiday/class/holiday.class.php';
					$object_instance = new Holiday($db);
				} else if ($modulepart == 'loan') {
                    require_once DOL_DOCUMENT_ROOT . '/loan/class/loan.class.php';
                    $object_instance = new Loan($db);
                } else if ($modulepart == 'don') {
                    require_once DOL_DOCUMENT_ROOT . '/don/class/don.class.php';
                    $object_instance = new Don($db);
                } else if ($modulepart == 'facture') {
                    require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
                    $object_instance = new Facture($db);
                } else if ($modulepart == 'project') {
                    require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
                    $object_instance = new Project($db);
                }

				$hookmanager->object_instance = $object_instance;
				$hookmanager->object_id = $object_id;
				$hookmanager->object_ref = null;

			}
			return 1;
		}
		return 0;
	}

    /**
     * Method adds additonal paramaters for solr indexing
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     * @throws Exception
     */
	function getSolrIndexingAdditionalParams($parameters, &$object, &$action, $hookmanager)
	{

		if(!class_exists('ELbFileMapping')) {
			return 0;
		}
		$ecmFiles = $parameters['ecmFiles'];
		$elbfilemap = new ELbFileMapping($this->db);
		$res = $elbfilemap->fetchFromEcmfilesPath($ecmFiles->filename);
		$additionalParams = array();
		if ($res > 0) {
			$tags = json_decode($elbfilemap->tags);
			if (is_array($tags) && count($tags) > 0) {
				$additionalParams["literal.elb_tag"] = $tags;
			}
		}
		if (strlen($elbfilemap->description) > 0) {
			$additionalParams["literal.elb_file_desc"] = $elbfilemap->description;
		}
		if (strlen($elbfilemap->revision) > 0) {
			$additionalParams["literal.elb_revision"] = $elbfilemap->revision;
		}
		$additionalParams["literal.elb_file_active"] = $elbfilemap->active;
		if (is_array($additionalParams) && count($additionalParams) > 0) {
			$hookmanager->resArray['additionalParams'] = $additionalParams;
			return 1;
		}

		return 0;
	}

    /**
     * Solr search by additional parameters
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrSearchAdditionalSearch($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf;
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.category.class.php';
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			$s = '<div class="divsearchfield">';
			$s .= '<table cellpadding="0" cellspacing="0">';
			$s .= '<tr>';
			$s .= '<td>' . $langs->trans('Tags') . ': </td>';
			$form = new Form($this->db);
			$all_tags = ElbFileCategory::getFileTags();
			$search_tags = $_REQUEST['search_tags'];
			$s .= '<td>';
			$s .= $form->multiselectarray('search_tags', $all_tags, $search_tags, '', 0, '', 0, '300px', '', '', true);
			$s .= '</td>';
			$s .= '</tr>';
			$s .= '</table>';
			$s .= '</div>';
			$hookmanager->resArray['solrSearchAdditionalSearch'] = $s;
			return 1;
		}
		return 0;
	}

    /**
     * Method adds additional column to the solr search table
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrSearchAdditionalColumnHeader($parameters, &$object, &$action, $hookmanager)
    {
		global $langs, $conf;
		$headers = array();
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			$s = '<td>';
			$s .= $langs->trans('Tags');
			$s .= '</td>';
		}
		$headers[] = $s;
		$s = '<td>';
		$s.= $langs->trans('Description');
		$s.= '</td>';
		$headers[] = $s;
		$s = '<td>';
		$s.= $langs->trans('Revision');
		$s.= '</td>';
		$headers[] = $s;
		$hookmanager->resArray['headers'] = $headers;
		return 1;
	}

    /**
     * Method adds search inputs for additional columns to the solr search table
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrSearchAdditionalColumnSearch($parameters, &$object, &$action, $hookmanager)
    {
    	global $conf;
		$columns = array();
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			//Tags
			$s = '<th>';
			$s .= '</th>';
			$columns[] = $s;
		}
		//Description
		$search_desc = GETPOST('search_desc');
		$s= '<th align="left">';
		$s.= '<input class="flat" size="10" name="search_desc" value="'.$search_desc.'"/>';
		$s.= '</th>';
		$columns[] = $s;
		//Revision
		$search_rev = GETPOST('search_rev');
		$s= '<th align="left">';
		$s.= '<input class="flat" size="5" name="search_rev" value="'.$search_rev.'"/>';
		$s.= '</th>';
		$columns[] = $s;
		$hookmanager->resArray['columns'] = $columns;
		return 1;
	}

    /**
     * Method adds search by data from additional search parameters
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrSearchAdditionalColumnData($parameters, &$object, &$action, $hookmanager)
    {
		global $langs, $conf;
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.category.class.php';
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';
		require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.html.form.class.php';
		$data = array();
		$file = $parameters['file'];
		//Tags
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			static $all_tags;
			if(empty($all_tags)) {
				$all_tags = ElbFileCategory::getFileTags();
			}
			$tags = $file['index_data']['elb_tag'];
			$s = '<td>';
			if (is_array($tags) && count($tags)) {
				$s .= "[" . implode(", ", $tags) . "]";
			}
			$s .= '</td>';
			$data[] = $s;
		}
		//Description
		$s= '<td>';
		$s.= $file['index_data']['elb_file_desc'];
		$s.= '</td>';
		$data[] = $s;
		//Revision
		$s= '<td>';
		$s.= $file['index_data']['elb_revision'];
		$s.= '</td>';
		$data[] = $s;
		$hookmanager->resArray['data'] = $data;
		return 1;
	}

    /**
     * Method executes solr search by additional params
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrExecuteAdditionalSearch($parameters, &$object, &$action, $hookmanager)
    {
    	global $conf;
		$query_parts = array();
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			$search_tags = $_REQUEST['search_tags'];
			if (count($search_tags) > 0) {
				$tags_array = array();
				foreach ($search_tags as $tag) {
					$tags_array[] = '"' . $tag . '"';
				}
				$tag_query = "(" . implode(" OR ", $tags_array) . ")";
				$query_parts[] = "elb_tag:" . $tag_query;
			}
		}
		$search_desc = GETPOST('search_desc');
		if(strlen($search_desc)>0) {
			$query_parts[] = "elb_file_desc:".$search_desc;
		}
		$search_rev = GETPOST('search_rev');
		if(strlen($search_rev)>0) {
			$query_parts[] = "elb_revision:".$search_rev;
		}

		if(count($query_parts)>0) {
			$hookmanager->resArray['query_parts'] = $query_parts;
			return 1;
		}

		return 0;
	}

    /**
     * Method adds additional search parameters to the solr
     *
     * @param $parameters
     * @param $object
     * @param $action
     * @param $hookmanager
     * @return int
     */
	function solrSearchUrlParams($parameters, &$object, &$action, $hookmanager)
    {
    	global $conf;
    	$param = $parameters['param'];
		$search_desc = GETPOST('search_desc');
		$search_rev = GETPOST('search_rev');
		if ($conf->global->ELB_ALLOW_CATEGORIES_FOR_FILES) {
			$search_tags = $_REQUEST['search_tags'];
			if (count($search_tags) > 0) {
				foreach ($search_tags as $tag) {
					$param .= "&search_tags[]=" . $tag;
				}
			}
		}
		$param.="&search_desc=".$search_desc;
		$param.="&search_rev=".$search_rev;
		$hookmanager->resArray['param'] = $param;
		return 1;
	}

	/**
	 * Hook method that returns download link for other modules
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 * @return int
	 */
	function getObjectDownloadLink($parameters, &$object, &$action, $hookmanager) {
		$modulepart = $parameters['modulepart'];
		$forcedownload = $parameters['forcedownload'];
		$filepath = $parameters['filepath'];
		$entity = $parameters['entity'];
		if($modulepart == 'elbmultiupload') {
			$link = DOL_URL_ROOT . '/elbmultiupload/document.php?modulepart=' . $modulepart;
			if ($forcedownload) {
				$link .= '&attachment=1';
			}
			if (!empty($entity)) {
				$link .= '&entity=' . $entity;
			}
			$link .= '&file=' . urlencode($filepath);
			$pathinfo = pathinfo($filepath);
			$link .= '&fmapid=' . $pathinfo['filename'];
			$hookmanager->resArray['link'] = $link;
			return 1;
		}
		return 0;
	}

}
