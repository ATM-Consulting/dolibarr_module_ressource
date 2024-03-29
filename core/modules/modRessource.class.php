<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * 	\defgroup   mymodule     Module MyModule
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/mymodule/core/modules directory.
 *  \file       htdocs/mymodule/core/modules/modMyModule.class.php
 *  \ingroup    mymodule
 *  \brief      Description and activation file for module MyModule
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *  Description and activation class for module MyModule
 */
class modRessource extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 7000;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'ressource';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM Consulting - RH";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Gestion des ressources";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.1.5';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='ressource@ressource';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 // Set this to 1 if module has its own trigger directory
		//							'login' => 0,                                    // Set this to 1 if module has its own login method directory
		//							'substitutions' => 0,                            // Set this to 1 if module has its own substitution function file
		//							'menus' => 0,                                    // Set this to 1 if module has its own menus handler directory
		//							'barcode' => 0,                                  // Set this to 1 if module has its own barcode directory
		//							'models' => 0,                                   // Set this to 1 if module has its own models directory
									//'css' => '/ressource/css/boutons.css.php',       // Set this to relative path of css if module has its own css file
		//							'hooks' => array('hookcontext1','hookcontext2')  // Set here all hooks context managed by module
		//							'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array( 'hooks'=>array('ndfpcard') );

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		$this->config_page_url = array("admin_ressource.php@ressource");

		// Dependencies
		$this->depends = array('modRHHierarchie');		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("ressource@ressource");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0)
		// );
		$this->const = array(
			array('RH_DOL_ADMIN_USER','chaine','admin','Ajouté par RH',1)
			,array('RH_USER_MAIL_SENDER','chaine','webmaster@atm-consulting.fr','Ajouté par RH',1)
			,array('RH_USER_MAIL_RECEIVER','chaine','webmaster@atm-consulting.fr','Ajouté par RH',1)
			,array('RH_DAYS_BEFORE_ALERT','chaine','30','Ajouté par RH',1)
			,array('RH_AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE','chaine','0','Ajouté par RH',1)

		);

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:langfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:langfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'order_supplier'   to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'stock'            to add a tab in stock view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// 'user'             to add a tab in user view
		// 'group'            to add a tab in group view
		// 'contact'          to add a tab in contact view
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        $this->tabs = array();/*'thirdparty:+creerTypeRessource:CréerTypeRessource:ressource@ressource:/ressource/index.php',  // To add a new tab identified by code tabname1
                             'thirdparty:+nouvelleRessource:NouvelleRessource:ressource@ressource:/ressource/index.php',
                                      );*/

        // Dictionnaries
        //if (! isset($conf->ressource->enabled)) $conf->ressource->enabled='1';
		$this->dictionnaries=array();
        /* Example:
        if (! isset($conf->mymodule->enabled)) $conf->mymodule->enabled=0;	// This is to avoid warnings
        $this->dictionnaries=array(
            'langs'=>'mymodule@mymodule',
            'tabname'=>array("table1","table2","table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionnary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionnary
        );
        */

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		//$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;
		$this->rights[$r][0] = 7001;
		$this->rights[$r][1] = 'Créer/Modifier un type de ressource';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'typeressource';
		$this->rights[$r][5] = 'createType';
		$r++;
		$this->rights[$r][0] = 7002;
		$this->rights[$r][1] = 'Voir les types de ressource';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'typeressource';
		$this->rights[$r][5] = 'viewType';
		$r++;
		$this->rights[$r][0] = 7003;
		$this->rights[$r][1] = 'Créer/Modifier une ressource';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'createRessource';
		$r++;
		$this->rights[$r][0] = 7004;
		$this->rights[$r][1] = 'Voir les ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'viewRessource';
		$r++;
		$this->rights[$r][0] = 7005;
		$this->rights[$r][1] = 'Créer/Modifier un contrat';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'contrat';
		$this->rights[$r][5] = 'createContract';
		$r++;
		$this->rights[$r][0] = 7006;
		$this->rights[$r][1] = 'Voir les contrats';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'contrat';
		$this->rights[$r][5] = 'viewContract';
		$r++;
		$this->rights[$r][0] = 7007;
		$this->rights[$r][1] = 'Consulter l\'agenda';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'agenda';
		$this->rights[$r][5] = 'viewAgenda';
		$r++;
		$this->rights[$r][0] = 7008;
		$this->rights[$r][1] = 'Gérer l\'attribution des ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'manageAttribution';
		$r++;
		$this->rights[$r][0] = 7009;
		$this->rights[$r][1] = 'Gérer les événements sur les ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'manageEvents';
		$r++;
		$this->rights[$r][0] = 7010;
		$this->rights[$r][1] = 'Importer des fichiers';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'uploadFiles';
		$r++;
		$this->rights[$r][0] = 7011;
		$this->rights[$r][1] = 'Consulter les fichiers';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'download';
		$r++;
		$this->rights[$r][0] = 7012;
		$this->rights[$r][1] = 'Importer des fichiers confidentiels';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'uploadFilesRestricted';
		$r++;
		$this->rights[$r][0] = 7013;
		$this->rights[$r][1] = 'Consulter les fichiers confidentiels';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'viewFilesRestricted';
		$r++;
		$this->rights[$r][0] = 7014;
		$this->rights[$r][1] = 'Importer les fichiers fournisseurs';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'uploadFilesSupplier';
		$r++;
		$this->rights[$r][0] = 7015;
		$this->rights[$r][1] = 'Consulter le calendrier d\'une ressource';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'viewResourceCalendar';
		$r++;
		$this->rights[$r][0] = 7016;
		$this->rights[$r][1] = 'Filtre Recherche dans la liste des ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'searchRessource';
		$r++;
		$this->rights[$r][0] = 7017;
		$this->rights[$r][1] = 'Voir les prix sur les contrats';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'contrat';
		$this->rights[$r][5] = 'viewPrixContrat';
		$r++;
		$this->rights[$r][0] = 7018;
		$this->rights[$r][1] = 'Cacher ses règles téléphoniques';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'hideRegle';
		$r++;
		$this->rights[$r][0] = 7019;
		$this->rights[$r][1] = 'Gérer les règles téléphoniques';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'manageRegle';
		$r++;
		$this->rights[$r][0] = 7020;
		$this->rights[$r][1] = 'Sélectionner les paramètres sur l\'agenda général';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'agenda';
		$this->rights[$r][5] =  'manageAgenda';
		$r++;
		$this->rights[$r][0] = 7021;
		$this->rights[$r][1] = 'Visualiser les fichiers des ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'read';
		$r++;
		$this->rights[$r][0] = 7022;
		$this->rights[$r][1] = 'Accéder aux évènements confidentiels';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'readEvenementConfidentiel';
		$r++;
		$this->rights[$r][0] = 7023;
		$this->rights[$r][1] = 'Accéder au menu de gestion des numéros spéciaux';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'accessSpecialNumbers';
		$r++;
		$this->rights[$r][0] = 7024;
		$this->rights[$r][1] = 'Accéder au menu ressources';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ressource';
		$this->rights[$r][5] = 'accessMenu';

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		//
		// Example to declare a new Top Menu entry and its Left menu entry:
		$this->menu[$r]=array(
						'fk_menu'=>0,			                // Put 0 if this is a top menu
						'type'=>'top',			                // This is a Top menu entry
						'titre'=>$langs->trans('Ressource'),
						'mainmenu'=>'ressource',
						'leftmenu'=>'',
						'url'=>'/ressource/ressource.php?idmenu=100',
						'langs'=>'ressource@ressource',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=>100,
						'enabled'=>'1',							// Define condition to show or hide menu entry. Use '$conf->financement->enabled' if entry must be visible if module is enabled.
						'perms'=>'$user->rights->ressource->ressource->accessMenu',			                // Use 'perms'=>'$user->rights->financement->level1->level2' if you want your menu with a permission rules
						'target'=>'',
						'user'=>0								// 0=Menu for internal users, 1=external users, 2=both
		);

		//RESSOURCES
		$r++;
        $this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=ressource',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Ressource'),
			        	'mainmenu'=> 'ressource',
			        	'leftmenu'=> 'ressources',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/ressource/ressource.php',
						'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 101,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=ressources',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('listeRessource'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/ressource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=ressources',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('listeRessourceLibre'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/ressourceLibre.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 103,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->ressource->viewRessource',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=ressources',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('nouvelleRessource'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/ressource.php?action=new',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 104,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->ressource->createRessource',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=ressources',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('specialNumbers'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/special_numbers.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 104,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->ressource->accessSpecialNumbers',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		//AGENDA
		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('agenda'),
		        	'mainmenu'=> 'ressource',
		        	'leftmenu'=> 'agenda',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/calendrierRessource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 105,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=agenda',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('listeContrat'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/contrat.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 106,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=agenda',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('nouveauContrat'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/contrat.php?action=new',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 107,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->contrat->createContract',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		//TYPES RESSOURCES
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('typeRessource'),
		        	'mainmenu'=> 'ressource',
		        	'leftmenu'=> 'typeressource',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/typeRessource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 108,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->typeressource->viewType',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=typeressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('listeTypeRessource'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'typeressources',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/typeRessource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 109,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->typeressource->viewType',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource,fk_leftmenu=typeressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('nouveauTypeRessource'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'typeressources',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/typeRessource.php?action=new',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 110,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->typeressource->createType',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		//IMPORTS
		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=ressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('ImportFournisseurs'),
		        	'mainmenu'=> 'ressource',
		        	'leftmenu'=> 'import',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/documentSupplier.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 111,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->ressource->ressource->uploadFilesSupplier',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );


		//Entrées dans le menu Rapport
		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Ressources'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> 'rapportressource',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/ressource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 112,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=rapportressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('rapportTotal'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/verificationEssence.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 113,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=rapportressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('rapportTel'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/verificationTelephone.php?action=new',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 114,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=rapportressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('rapportVehicule'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/verificationVehicule.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 115,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=rapportressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('rapportContrat'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/verificationContrat.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 116,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;


		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=rapportressource',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('RessourceExports'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/ressource/exportsRessource.php',
					'langs'=> 'ressource@ressource',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 117,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateRessourceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );

		$r++;





		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $r++;
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function init($options='')
	{
		$sql = array();

		$result=$this->load_tables();

		if(!is_file( dol_buildpath("/ressource/config.php" ))) {
			 $data='<?php require(\'default.config.php\'); /* fichier de conf de base */';

			file_put_contents( dol_buildpath("/ressource/config.php" ) , $data);
		}

		$url =dol_buildpath("/ressource/script/create-maj-base.php",1);
		echo $url;
		file_get_contents($url);


       	$extrafields=new ExtraFields($this->db);
		$res = $extrafields->addExtraField('COMPTE_TIERS', 'CompteTiers', 'varchar', 0, 255, 'user');


		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function remove($options='')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}


	/**
	 *		Create tables, keys and data required by module
	 * 		Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 		and create data commands must be stored in directory /mymodule/sql/
	 *		This function is called by this->init
	 *
	 * 		@return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ressource/sql/');
	}
}

?>
