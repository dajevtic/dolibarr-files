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
 * 	\defgroup   elbmultiupload     Module Elbmultiupload
 *  \brief      Module adds upload additional files tab/functionality for each object in the system
 *
 *  \file       htdocs/elbmultiupload/core/modules/modElbmultiupload.class.php
 *  \ingroup    elbmultiupload
 *  \brief      Description and activation file for module Elbmultiupload
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module MyModule
 */
class modElbmultiupload extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs,$conf;

        $this->db = $db;

        // Id for module (must be unique).
        // Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 156000;		// TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'elbmultiupload';

        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
        // It is used to group modules by family in module setup page
        $this->family = "other";
        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '01';
        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

        // Module label (no space allowed), used if translation string 'ModuleMyModuleName' not found (MyModule is name of module).
        $this->name = preg_replace('/^mod/i','',get_class($this));
        // Module description, used if translation string 'ModuleMyModuleDesc' not found (MyModule is name of module).
        $this->description = "Module adds upload additional files tab/functionality for each object in the system";
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "Module adds upload additional files tab/functionality for each object in the system";

        $this->editor_name = 'ELB Solutions';
        $this->editor_url = 'https://www.elb-solutions.com';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.0';

        //Url to the file with your last numberversion of this module
        //$this->url_last_version = 'http://www.example.com/versionmodule.txt';
        // Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        $this->picto='elbmultiupload@elbmultiupload';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array(
            'triggers' => 1,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
            'login' => 0,                                    	// Set this to 1 if module has its own login method file (core/login)
            'substitutions' => 1,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
            'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
            'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
            'tpl' => 1,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
            'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
            'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
            'css' => array('/elbmultiupload/css/elbmultiupload.css.php'),	// Set this to relative path of css file if module has its own css file
            'js' => array('/elbmultiupload/js/elbmultiupload.js'),          // Set this to relative path of js file if module must load a js on all pages
            'hooks' => array('globalcard','formfile'), 	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
            'moduleforexternal' => 0							// Set this to 1 if feature of module are opened to external users
        );

        //objecttype:+tabname1:Title1:langfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__

        // Data directories to create when module is enabled.
        // Example: this->dirs = array("/mymodule/temp","/mymodule/subdir");
        $this->dirs = array("");

        // Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
        $this->config_page_url = array("setup.php@elbmultiupload");

        // Dependencies
        $this->hidden = false;			// A condition to hide module
        $this->depends = array();		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        $this->requiredby = array();	// List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = array();	// List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
        $this->langfiles = array("elbmultiupload@elbmultiupload");
        //$this->phpmin = array(5,4);					// Minimum version of PHP required by module
        $this->need_dolibarr_version = array(4,0);		// Minimum version of Dolibarr required by module
        $this->warnings_activation = array();			// Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        $this->warnings_activation_ext = array();		// Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        //$this->automatic_activation = array('FR'=>'MyModuleWasAutomaticallyActivatedBecauseOfYourCountryChoice');
        //$this->always_enabled = true;								// If true, can't be disabled

        // Constants
        // List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        // Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
        //                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
        // );
        $this->const = array(
            2=>array('ELB_UPLOAD_FILES_BUFFER','chaine','uploadbuffer','Buffer for uploaded files. Files will be removed to the ELB_UPLOAD_FILES_DIRECTORY if everything is OK',1,'current',0),
            3=>array('ELB_UPLOAD_FILES_DIRECTORY','chaine','uploadedfiles','Subdirectory in DOL_DATA_ROOT where we store uploaded files',1,'current',0)
        );

        // Some keys to add into the overwriting translation tables
        /*$this->overwrite_translation = array(
            'en_US:ParentCompany'=>'Parent company or reseller',
            'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
        )*/

        if (! isset($conf->elbmultiupload) || ! isset($conf->elbmultiupload->enabled))
        {
            $conf->elbmultiupload=new stdClass();
            $conf->elbmultiupload->enabled=0;
        }


        // Array to add new pages in new tabs
        $this->tabs = array();
        // Example:
        // $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
        //
        // Where objecttype can be
        // 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        // 'contact'          to add a tab in contact view
        // 'contract'         to add a tab in contract view
        // 'group'            to add a tab in group view
        // 'intervention'     to add a tab in intervention view
        // 'invoice'          to add a tab in customer invoice view
        // 'invoice_supplier' to add a tab in supplier invoice view
        // 'member'           to add a tab in fundation member view
        // 'opensurveypoll'	  to add a tab in opensurvey poll view
        // 'order'            to add a tab in customer order view
        // 'order_supplier'   to add a tab in supplier order view
        // 'payment'		  to add a tab in payment view
        // 'payment_supplier' to add a tab in supplier payment view
        // 'product'          to add a tab in product view
        // 'propal'           to add a tab in propal view
        // 'project'          to add a tab in project view
        // 'stock'            to add a tab in stock view
        // 'thirdparty'       to add a tab in third party view
        // 'user'             to add a tab in user view

        // user
        $this->tabs[] = array('data' => 'user:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=user');
        // user group
        $this->tabs[] = array('data' => 'group:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=usergroup');
        // hr - leave
        $this->tabs[] = array('data' => 'holiday:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=holiday');
        // expense
        $this->tabs[] = array('data' => 'expensereport:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=expensereport');
        // member
        $this->tabs[] = array('data' => 'member:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=member');
        // third party
        $this->tabs[] = array('data' => 'thirdparty:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=societe');
        // proposal
        $this->tabs[] = array('data' => 'propal:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=propal');
        // customer order
        $this->tabs[] = array('data' => 'order:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=commande');
        // contract
        $this->tabs[] = array('data' => 'contract:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=contrat');
        // shipment
        $this->tabs[] = array('data' => 'delivery:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=shipping');
        // intervention
        $this->tabs[] = array('data' => 'intervention:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=fichinter');
        // supplier order
        $this->tabs[] = array('data' => 'supplier_order:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=order_supplier');
        // supplier price request
        $this->tabs[] = array('data' => 'supplier_proposal:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=supplier_proposal');
        // invoice
        $this->tabs[] = array('data' => 'invoice:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=facture');
        // invoice payments
        $this->tabs[] = array('data' => 'payment:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=payment');
        // sales tax payment
        $this->tabs[] = array('data' => 'vat:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=tva');
        // social or fiscal tax
        $this->tabs[] = array('data' => 'tax:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=chargesociales');
        // supplier invoice
        $this->tabs[] = array('data' => 'supplier_invoice:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=invoice_supplier');
        // supplier invoice payments
        $this->tabs[] = array('data' => 'payment_supplier:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=payment_supplier');
        // salary payments
        $this->tabs[] = array('data' => 'salaries:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=payment_salary');
        // loan
        $this->tabs[] = array('data' => 'loan:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=loan');
        // donation
        $this->tabs[] = array('data' => 'donation:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=don');
        // bank account
        $this->tabs[] = array('data' => 'bank:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=bank_account');
        // product
        $this->tabs[] = array('data' => 'product:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=product');
        // warehouse
        $this->tabs[] = array('data' => 'stock:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=stock');
        // project
        $this->tabs[] = array('data' => 'project:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=project');
        // agenda (calendar)
        // NOT available because calendar events are missing ids in lists
        //$this->tabs[] = array('data' => 'agenda:+additionalfiles:AdditionalFiles:elbmultiupload@elbmultiupload:1:/elbmultiupload/card.php?id=__ID__&object_element=action');


        // Dictionaries
        $this->dictionaries=array();
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@mymodule',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionary
        );
        */


        // Boxes/Widgets
        // Add here list of php file(s) stored in mymodule/core/boxes that contains class to show a widget.
        $this->boxes = array(
            //0=>array('file'=>'mymodulewidget1.php@elbmultiupload','note'=>'Widget provided by MyModule','enabledbydefaulton'=>'Home'),
            //1=>array('file'=>'mymodulewidget2.php@mymodule','note'=>'Widget provided by MyModule'),
            //2=>array('file'=>'mymodulewidget3.php@mymodule','note'=>'Widget provided by MyModule')
        );


        // Cronjobs (List of cron jobs entries to add when module is enabled)
        // unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
        $this->cronjobs = array(
            0=>array('label'=>'MyJob label', 'jobtype'=>'method', 'class'=>'/mymodule/class/myobject.class.php', 'objectname'=>'MyObject', 'method'=>'doScheduledJob', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->mymodule->enabled', 'priority'=>50)
        );
        // Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->mymodule->enabled', 'priority'=>50),
        //                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->mymodule->enabled', 'priority'=>50)
        // );


        // Permissions
        $this->rights = array();		// Permission array used by this module

//        $r=0;
//        $this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
//        $this->rights[$r][1] = 'Read myobject of MyModule';	// Permission label
//        $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
//        $this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)
//        $this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)
//
//        $r++;
//        $this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
//        $this->rights[$r][1] = 'Create/Update myobject of MyModule';	// Permission label
//        $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
//        $this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)
//        $this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)
//
//        $r++;
//        $this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
//        $this->rights[$r][1] = 'Delete myobject of MyModule';	// Permission label
//        $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
//        $this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)
//        $this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->mymodule->level1->level2)


        // Main menu entries
        $this->menu = array();			// List of menus to add
        $r=0;

        // Add here entries to declare new menus

        /* BEGIN MODULEBUILDER TOPMENU */
//        $this->menu[$r++]=array('fk_menu'=>'',			                // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
//            'type'=>'top',			                // This is a Top menu entry
//            'titre'=>'MyModule',
//            'mainmenu'=>'mymodule',
//            'leftmenu'=>'',
//            'url'=>'/elbmultiupload/index.php',
//            'langs'=>'elbmultiupload@elbmultiupload',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
//            'position'=>1000+$r,
//            'enabled'=>'$conf->elbmultiupload->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
//            'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
//            'target'=>'',
//            'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both

        /* END MODULEBUILDER TOPMENU */

        /* BEGIN MODULEBUILDER LEFTMENU MYOBJECT
        $this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=mymodule',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
                                'type'=>'left',			                // This is a Left menu entry
                                'titre'=>'List MyObject',
                                'mainmenu'=>'mymodule',
                                'leftmenu'=>'mymodule_myobject_list',
                                'url'=>'/mymodule/myobject_list.php',
                                'langs'=>'mymodule@mymodule',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
                                'position'=>1000+$r,
                                'enabled'=>'$conf->mymodule->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
                                'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
                                'target'=>'',
                                'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
        $this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=mymodule,fk_leftmenu=mymodule',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
                                'type'=>'left',			                // This is a Left menu entry
                                'titre'=>'New MyObject',
                                'mainmenu'=>'mymodule',
                                'leftmenu'=>'mymodule_myobject_new',
                                'url'=>'/mymodule/myobject_page.php?action=create',
                                'langs'=>'mymodule@mymodule',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
                                'position'=>1000+$r,
                                'enabled'=>'$conf->mymodule->enabled',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
                                'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
                                'target'=>'',
                                'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
        END MODULEBUILDER LEFTMENU MYOBJECT */

        // Machines - Left menu - first level
        $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=home',
            'type'=>'left',
            'titre'=>'CategoriesForFiles',
            'mainmenu'=>'home',
            'leftmenu'=>'home',
            'url'=>'/elbmultiupload/categories/index.php',
            'langs'=>'elbmultiupload@elbmultiupload',
            'position'=>10,
            'enabled'=>'$conf->elbmultiupload->enabled',
            'perms'=>1,
            'target'=>'',
            'user'=>2,
            'entity'=>0);


        // Exports
        $r=1;

        /* BEGIN MODULEBUILDER EXPORT MYOBJECT */
        /*
        $langs->load("mymodule@mymodule");
        $this->export_code[$r]=$this->rights_class.'_'.$r;
        $this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        $this->export_icon[$r]='myobject@mymodule';
        $keyforclass = 'MyObject'; $keyforclassfile='/mymobule/class/myobject.class.php'; $keyforelement='myobject';
        include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
        $keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject';
        include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
        //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
        $this->export_sql_start[$r]='SELECT DISTINCT ';
        $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
        $this->export_sql_end[$r] .=' WHERE 1 = 1';
        $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
        $r++; */
        /* END MODULEBUILDER EXPORT MYOBJECT */
    }

    /**
     *	Function called when module is enabled.
     *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *	It also creates data directories
     *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
     *	@return     int             	1 if OK, 0 if KO
     */
    public function init($options='')
    {
        $result=$this->_load_tables('/elbmultiupload/sql/');
        if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')

        // Create extrafields
        include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        $extrafields = new ExtraFields($this->db);

        //$result1=$extrafields->addExtraField('myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'mymodule@mymodule', '$conf->mymodule->enabled');
        //$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'mymodule@mymodule', '$conf->mymodule->enabled');
        //$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'mymodule@mymodule', '$conf->mymodule->enabled');
        //$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'mymodule@mymodule', '$conf->mymodule->enabled');
        //$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'mymodule@mymodule', '$conf->mymodule->enabled');

        $sql = array();

        return $this->_init($sql, $options);
    }

    /**
     *	Function called when module is disabled.
     *	Remove from database constants, boxes and permissions from Dolibarr database.
     *	Data directories are not deleted
     *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
     *	@return     int             	1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}
