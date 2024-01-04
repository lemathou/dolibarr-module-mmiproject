<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2022 Moulin Mathieu <contact@iprospective.fr>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   mmiproject     Module MMIProject
 *  \brief      MMIProject module descriptor.
 *
 *  \file       htdocs/mmiproject/core/modules/modMMIProject.class.php
 *  \ingroup    mmiproject
 *  \brief      Description and activation file for module MMIProject
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module MMIProject
 */
class modMMIProject extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 437813; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'mmiproject';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "projects";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleMMIProjectName' not found (MMIProject is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleMMIProjectDesc' not found (MMIProject is name of module).
		$this->description = "Gestion de projets avancée";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Création auto et gestion de projets de chantiers avec suivi des heures";

		// Author
		$this->editor_name = 'Mathieu Moulin iProspective MMI';
		$this->editor_url = 'https://iprospective.fr';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where MMIPROJECT is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'mmilogo@mmiproject';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 1,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/mmiproject/css/mmiproject.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/mmiproject/js/mmiproject.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				'ordercard',
				'tasklist',
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mmiproject/temp","/mmiproject/subdir");
		$this->dirs = array("/mmiproject/temp");

		// Config pages. Put here list of php page, stored into mmiproject/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@mmiproject");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array('modMMICommon', 'modProjet');
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = array("mmiproject@mmiproject");

		// Prerequisites
		$this->phpmin = array(7, 2); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'MMIProjectWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('MMIPROJECT_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('MMIPROJECT_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->mmiproject) || !isset($conf->mmiproject->enabled)) {
			$conf->mmiproject = new stdClass();
			$conf->mmiproject->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		$this->tabs[] = array('data'=>'product:+resources:Resources:mmiproject@mmiproject:$user->rights->mmiproject->resources->product:custom/mmiproject/product_resources.php?id=__ID__');
		$this->tabs[] = array('data'=>'project:+resources:Resources:mmiproject@mmiproject:$user->rights->mmiproject->resources->product:custom/mmiproject/projet_resources.php?id=__ID__');
		$this->tabs[] = array('data'=>'task:+task_resources:Resources:mmiproject@mmiproject:$user->rights->mmiproject->resources->product:custom/mmiproject/task_resources.php?id=__ID__');
		$this->tabs[] = array('data'=>'user:+contracts:Contrats:mmiproject@mmiproject:$user->rights->mmiproject->contract->all:custom/mmiproject/user_contract.php?id=__ID__');
		
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@mmiproject:$user->rights->mmiproject->read:/mmiproject/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mmiproject:$user->rights->othermodule->read:/mmiproject/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
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

		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'mmiproject@mmiproject',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."c_propal_lostreason", MAIN_DB_PREFIX."c_propal_type", MAIN_DB_PREFIX."c_propal_technique", MAIN_DB_PREFIX."c_order_project_task"),
			// Label of tables
			'tablib'=>array("Raison Perdu Propal", 'Type de Propal', 'Technique Propal', 'Tâches de chantiers depuis commande'),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active, f.pos FROM '.MAIN_DB_PREFIX.'c_propal_lostreason as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active, f.pos FROM '.MAIN_DB_PREFIX.'c_propal_type as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active, f.pos FROM '.MAIN_DB_PREFIX.'c_propal_technique as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active, f.pos FROM '.MAIN_DB_PREFIX.'c_order_project_task as f'),
			// Sort order
			'tabsqlsort'=>array("pos ASC", "pos ASC", "pos ASC", "pos ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label,pos", "code,label,pos", "code,label,pos", "code,label,pos"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label,pos", "code,label,pos", "code,label,pos", "code,label,pos"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label,pos", "code,label,pos", "code,label,pos", "code,label,pos"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->mmiproject->enabled, $conf->mmiproject->enabled, $conf->mmiproject->enabled, $conf->mmiproject->enabled)
		);

		// Boxes/Widgets
		// Add here list of php file(s) stored in mmiproject/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'mmiprojectwidget1.php@mmiproject',
			//      'note' => 'Widget provided by MMIProject',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/mmiproject/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->mmiproject->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->mmiproject->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->mmiproject->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Use personnal TimeSheet'; // Permission label
		$this->rights[$r][4] = 'time';
		$this->rights[$r][5] = 'user'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Show everyones TimeSheet'; // Permission label
		$this->rights[$r][4] = 'time';
		$this->rights[$r][5] = 'admin'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Show chantiers planning'; // Permission label
		$this->rights[$r][4] = 'time';
		$this->rights[$r][5] = 'chantiers'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Show stats'; // Permission label
		$this->rights[$r][4] = 'time';
		$this->rights[$r][5] = 'stats'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Link Resources with Products'; // Permission label
		$this->rights[$r][4] = 'resources';
		$this->rights[$r][5] = 'product'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Edit Resources cat'; // Permission label
		$this->rights[$r][4] = 'resources';
		$this->rights[$r][5] = 'cat'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Update user contracts'; // Permission label
		$this->rights[$r][4] = 'contract';
		$this->rights[$r][5] = 'all'; // In php code, permission will be checked by test if ($user->rights->mmiproject->myobject->write)
		$r++;
		/* BEGIN MODULEBUILDER PERMISSIONS */
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectArea',
			//'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects',
			'url'=>'/custom/mmiproject/index.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->user',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=mmiprojects',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaTimeSheet',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects_time_monthly',
			'url'=>'/custom/mmiproject/time_monthly.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->user',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=mmiprojects',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaTimeForm',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects_time_form',
			'url'=>'/custom/mmiproject/time_form.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->user',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=mmiprojects',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaChantiers',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects_chantiers_planning',
			'url'=>'/custom/mmiproject/chantiers_planning.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->chantiers',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=mmiprojects',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaContrat',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects_contrat',
			'url'=>'/custom/mmiproject/contrat.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->chantiers',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=mmiprojects',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaStats',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'project',
			'leftmenu'=>'mmiprojects_stats',
			'url'=>'/custom/mmiproject/stats.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->time->stats',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=tools,fk_leftmenu=resource',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'MMIProjectAreaResources',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'tools',
			'leftmenu'=>'mmiprojects_resources',
			'url'=>'/custom/mmiproject/type_resources.php',
			'langs'=>'mmiproject@mmiproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->mmiproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->mmiproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->mmiproject->resources->cat',			                // Use 'perms'=>'$user->rights->mmiproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("mmiproject@mmiproject");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='myobject@mmiproject';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyObject'; $keyforclassfile='/mmiproject/class/myobject.class.php'; $keyforelement='myobject@mmiproject';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/mmiproject/class/myobject.class.php'; $keyforelement='myobjectline@mmiproject'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@mmiproject';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@mmiproject';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'myobject_line as tl ON tl.fk_myobject = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/*
		 $langs->load("mmiproject@mmiproject");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='myobject@mmiproject';
		 $keyforclass = 'MyObject'; $keyforclassfile='/mmiproject/class/myobject.class.php'; $keyforelement='myobject@mmiproject';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@mmiproject';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/mmiproject/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		
		// Project
		$extrafields->addExtraField('fk_commande', $langs->trans('Extrafield_fk_commande'), 'link', 100, '', 'projet', 0, 0, '', array('options'=>array('Commande:commande/class/commande.class.php'=>null)), 1, '', 5, $langs->trans('ExtrafieldToolTip_fk_commande'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('permanent', $langs->trans('Extrafield_project_permanent'), 'boolean',  100,  "", 'projet',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_project_permanent'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		
		// Project Tasks
		$extrafields->addExtraField('fk_commandedet', $langs->trans('Extrafield_fk_commandedet'), 'int', 100, 10, 'projet_task', 0, 0, '', "", 1, '', -5, $langs->trans('ExtrafieldToolTip_fk_commandedet'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('permanent', $langs->trans('Extrafield_task_permanent'), 'boolean',  100,  "", 'projet_task',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_task_permanent'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('fk_jalon_commandedet', $langs->trans('Extrafield_fk_jalon_commandedet'), 'sellist', 100, '', 'projet_task', 0, 0, '', "a:1:{s:7:\"options\";a:1:{s:44:\"commandedet:label:rowid::(label IS NOT NULL)\";N;}}", 1, '', -5, $langs->trans('ExtrafieldToolTip_fk_jalon_commandedet'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('fk_product', $langs->trans('Extrafield_fk_product'), 'link', 100, '', 'projet_task',  0, 0, '', array('options'=>array('Product:product/class/product.class.php'=>null)), 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_product'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('type', $langs->trans('Extrafield_task_type'), 'select',  100,  3, 'projet_task',  0, 0, '', array('options'=>array('1'=>'Déplacement de personne','2'=>'Réalisation/Pose','3'=>'Installation/Protection/Manutention','4'=>'Autre prestation de service à compter','5'=>'Location avec opérateur','6'=>'Organisation')), 1,'', -1, $langs->trans('ExtrafieldToolTip_task_type'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('fk_resource', $langs->trans('Extrafield_fk_resource'), 'link',  100,  '', 'projet_task',  0, 0, '', array('options'=>array('Dolresource:resource/class/dolresource.class.php'=>null)), 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_resource'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('intervenants', $langs->trans('Extrafield_intervenants'), 'int', 100, 2, 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_intervenants'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('qte', $langs->trans('Extrafield_qte'), 'double', 100, '10,2', 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_qte'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('qte_real', $langs->trans('Extrafield_qte_real'), 'double', 100, '10,2', 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_qte_real'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('fk_unit', $langs->trans('Extrafield_fk_unit'), 'sellist', 100, '10,2', 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_fk_unit'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('temps_restant_prevu_1', $langs->trans('Extrafield_temps_restant_prevu_1'), 'double', 100, '6,2', 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_temps_restant_prevu_1'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 1);
		$extrafields->addExtraField('temps_restant_prevu_2', $langs->trans('Extrafield_temps_restant_prevu_2'), 'double', 100, '6,2', 'projet_task', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_temps_restant_prevu_2'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 1);
		
		// Commande
		$extrafields->addExtraField('fk_propal_type', $langs->trans('Extrafield_fk_propal_type'), 'sellist',  100,  "", 'commande',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:27:\"c_propal_type:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_type'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('fk_propal_technique', $langs->trans('Extrafield_fk_propal_technique'), 'sellist',  100,  "", 'commande',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:32:\"c_propal_technique:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_technique'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('chantier_type', $langs->trans('Extrafield_propal_chantier_type'), 'select',  100,  3, 'commande',  0, 0, '', array('options'=>array('1'=>'Neuf','2'=>'Rénovation')), 1,'', -1, '', '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre', $langs->trans('Extrafield_appeloffre'), 'select',  100,  '', 'commande',  0, 0, '', array('options'=>array('1'=>'Appel d\'offre public','2'=>'Appel d\'offre privé','3'=>'Devis direct')), 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre_soustraitant', $langs->trans('Extrafield_appeloffre_soustraitant'), 'boolean',  100,  '', 'commande',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre_soustraitant'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('propal_liv_date_maxi', $langs->trans('Extrafield_propal_liv_date_maxi'), 'date',  100,  "", 'commande',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_liv_date_maxi'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('propal_decennale', $langs->trans('Extrafield_propal_decennale'), 'boolean',  100,  "", 'commande',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_decennale'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled & $conf->global->MMIPROJECT_DECENNALE_FIELD', 0);
		
		// Command line
		$extrafields->addExtraField('intervenants', $langs->trans('Extrafield_intervenants'), 'int', 100, 2, 'commandedet', 0, 0, '', "", 1, '', 1, $langs->trans('ExtrafieldToolTip_intervenants'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('heure', $langs->trans('Extrafield_heure'), 'double', 100, '10,2', 'commandedet', 0, 0, '', "", 1, '', 1, $langs->trans('ExtrafieldToolTip_heure'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 1);
		
		// Propal
		$extrafields->addExtraField('fk_propal_type', $langs->trans('Extrafield_fk_propal_type'), 'sellist',  100,  "", 'propal',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:27:\"c_propal_type:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_type'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('fk_propal_technique', $langs->trans('Extrafield_fk_propal_technique'), 'sellist',  100,  "", 'propal',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:32:\"c_propal_technique:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_technique'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('fk_propal_lostreason', $langs->trans('Extrafield_fk_propal_lostreason'), 'sellist',  100,  "", 'propal',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:33:\"c_propal_lostreason:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_lostreason'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('chantier_type', $langs->trans('Extrafield_propal_chantier_type'), 'select',  100,  3, 'propal',  0, 0, '', array('options'=>array('1'=>'Neuf','2'=>'Rénovation')), 1,'', -1, '', '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre', $langs->trans('Extrafield_appeloffre'), 'select',  100,  '', 'propal',  0, 0, '', array('options'=>array('1'=>'Appel d\'offre public','2'=>'Appel d\'offre privé','3'=>'Devis direct')), 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre_soustraitant', $langs->trans('Extrafield_appeloffre_soustraitant'), 'boolean',  100,  '', 'propal',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre_soustraitant'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		//$extrafields->addExtraField('appeloffre_maitrise', $langs->trans('Extrafield_appeloffre_maitrise'), 'link',  100,  '', 'propal',  0, 0, '', array('options'=>array('Fournisseur:fourn/class/fournisseur.class.php'=>null)), 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre_maitrise'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0); // mis dans un type de contact
		$extrafields->addExtraField('propal_client_wait', $langs->trans('Extrafield_propal_client_wait'), 'boolean',  100,  "", 'propal',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_client_wait'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('propal_liv_date_mini', $langs->trans('Extrafield_propal_liv_date_mini'), 'date',  100,  "", 'propal',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_liv_date_mini'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('propal_remise_date_maxi', $langs->trans('Extrafield_propal_remise_date_maxi'), 'date',  100,  "", 'propal',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_remise_date_maxi'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('propal_decennale', $langs->trans('Extrafield_propal_decennale'), 'boolean',  100,  "", 'propal',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_decennale'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled & $conf->global->MMIPROJECT_DECENNALE_FIELD', 0);
		
		// Propal line
		$extrafields->addExtraField('intervenants', $langs->trans('Extrafield_intervenants'), 'int', 100, 2, 'propaldet', 0, 0, '', "", 1, '', 1, $langs->trans('ExtrafieldToolTip_intervenants'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled');
		$extrafields->addExtraField('heure', $langs->trans('Extrafield_heure'), 'double', 100, '10,2', 'propaldet', 0, 0, '', "", 1, '', 1, $langs->trans('ExtrafieldToolTip_heure'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 1);
		
		// Product
		$extrafields->addExtraField('task_type', $langs->trans('Extrafield_task_type'), 'select',  100,  3, 'product',  0, 0, '', array('options'=>array('1'=>'Déplacement de personne','2'=>'Réalisation/Pose','3'=>'Installation/Protection/Manutention','4'=>'Autre prestation de service à compter','5'=>'Location avec opérateur','6'=>'Organisation')), 1,'', -1, $langs->trans('ExtrafieldToolTip_task_type'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);

		// Facture
		$extrafields->addExtraField('propal_decennale', $langs->trans('Extrafield_propal_decennale'), 'boolean',  100,  "", 'facture',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_propal_decennale'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled & $conf->global->MMIPROJECT_DECENNALE_FIELD', 0);
		$extrafields->addExtraField('fk_propal_type', $langs->trans('Extrafield_fk_propal_type'), 'sellist',  100,  "", 'facture',  0, 0, '', "a:1:{s:7:\"options\";a:1:{s:27:\"c_propal_type:label:rowid::\";N;}}", 1,'', -1, $langs->trans('ExtrafieldToolTip_fk_propal_type'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre', $langs->trans('Extrafield_appeloffre'), 'select',  100,  '', 'facture',  0, 0, '', array('options'=>array('1'=>'Appel d\'offre public','2'=>'Appel d\'offre privé','3'=>'Devis direct')), 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('appeloffre_soustraitant', $langs->trans('Extrafield_appeloffre_soustraitant'), 'boolean',  100,  '', 'facture',  0, 0, '', "", 1,'', -1, $langs->trans('ExtrafieldToolTip_appeloffre_soustraitant'), '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('date_livraison', $langs->trans('DeliveryDate'), 'date',  100,  "", 'facture',  0, 0, '', "", 1,'', -1, '', '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);
		$extrafields->addExtraField('date_livraison_aff', $langs->trans('Extrafield_date_livraison_aff'), 'select',  100,  "", 'facture',  0, 0, '', array('options'=>array('1'=>'Prévue','2'=>'Livré')), 1,'', -1, '', '', $conf->entity, 'mmiproject@mmiproject', '$conf->mmiproject->enabled', 0);

		// @todo conf->mmiproject->enabled à vérifier !!

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = 'mmiproject';
		$myTmpObjects = array();
		$myTmpObjects['MyObject'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'MyObject') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/mmiproject/template_myobjects.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/mmiproject';
				$dest = $dirodt.'/template_myobjects.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
