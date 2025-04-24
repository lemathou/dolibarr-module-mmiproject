<?php
/* Copyright (C) 2022 Moulin Mathieu <contact@iprospective.fr>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    mmiproject/class/actions_mmiproject.class.php
 * \ingroup mmiproject
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';

dol_include_once('custom/mmicommon/class/mmi_actions.class.php');

/**
 * Class ActionsMMIProject
 */
class ActionsMMIProject extends MMI_Actions_1_0
{
	const MOD_NAME = 'mmiproject';

	public function getNextValue($objsoc, $object)
	{
		global $conf;
		
		$classname = get_class($object);
		if ($classname=='Project') {
			$obj = empty($conf->global->PROJECT_ADDON) ? 'mod_project_simple' : $conf->global->PROJECT_ADDON;
			$classfilename = "core/modules/project/".$obj.'.php';
		}
		elseif ($classname=='Task') {
			$obj = empty($conf->global->TASK_ADDON) ? 'mod_task_simple' : $conf->global->TASK_ADDON;
			$classfilename = "core/modules/project/task/".$obj.'.php';
		}
		
		//Generate next ref
		$defaultref = '';
		// Search template files
		$file = ''; $filefound = 0;
		$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
		foreach ($dirmodels as $reldir) {
			$file = dol_buildpath($reldir.$classfilename, 0);
			if (file_exists($file)) {
				$filefound = 1;
				dol_include_once($reldir.$classfilename);
				$mod = new $obj;
				$defaultref = $mod->getNextValue($objsoc, $object);
				break;
			}
		}
		if (is_numeric($defaultref) && $defaultref <= 0) {
			$defaultref = '';
		}
		
		return $defaultref;
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
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;

		// @todo vérifier contexte
		//$this->resprints = $object->label;
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
		global $conf, $user, $langs, $db;

		$error = 0; // Error counter

		// Propales

		if ($this->in_context($parameters, 'propalcard')) {
			/** @var Propal $object */
			// @todo trigger après passage devis=>commande, récupérer les extrafields
		}

		//var_dump($parameters, $object);
		//var_dump($action);
		/* print_r($parameters); print_r($object); echo "action: " . $action; */

		// Commandes

		elseif ($this->in_context($parameters, 'ordercard')) {
			/** @var Commande $object */
			$product = new Product($db);
			$resource = new Dolresource($db);

			// Créer projet chantier associé
			if ($action=='createproject') {
				if (empty($conf->projet->enabled)) {
					$error++;
					$this->errors[] = "Le module Projet n'est pas activé";
				}
				elseif (!empty($object->fk_project)) {
					$error++;
					$this->errors[] = "Un projet est déjà associé à cette Commande";
				}
				else {
					$project = new Project($db);
					$project->title = 'Chantier '.$object->thirdparty->name.' - '.$object->ref;
					$project->socid = $object->thirdparty->id;
					$project->budget_amount = $object->total_ht;
					$project->usage_task = true;
					$project->array_options['options_fk_commande'] = $object->id;
					$project->ref = $this->getNextValue($object->thirdparty, $project);
					$r = $project->create($user);
					//var_dump($r);
					
					// Assoc à commande
					$object->fk_project = $project->id;
					$object->update($user);

					// Assoc à devis
					$object->fetchObjectLinked();
					//var_dump($object); die();
					if (!empty($object->linkedObjects['propal'])) {
						foreach($object->linkedObjects['propal'] as $propal) {
							$propal->fk_project = $project->id;
							$propal->update($user);
						}
					}
				}

				// Tâches de base
				$sql = 'SELECT label FROM `'.MAIN_DB_PREFIX.'c_order_project_task`
					WHERE active=1
					ORDER BY pos';
				$q = $db->query($sql);
				while(list($label)=$q->fetch_row()) {
					$task = new Task($db);
					$task->fk_project = $project->id;
					$task->label = $label;
					$task->ref = $this->getNextValue($object->thirdparty, $task);
					//var_dump($oline, $task);
					$task->create($user);
				}
			}
			// Mettre à jour projet chantier associé
			if ($action=='updateproject') {
				if (empty($conf->projet->enabled)) {
					$error++;
					$this->errors[] = "Le module Projet n'est pas activé";
				}
				elseif (empty($object->fk_project)) {
					$error++;
					$this->errors[] = "Aucun projet n'est associé à cette Commande";
				}
				else {
					$project = new Project($db);
					$project->fetch($object->fk_project);
					$project->fetch_optionals();
					$project->budget_amount = $object->total_ht;
					$project->usage_task = true;
					$project->update($user);
					$project->getLinesArray(NULL);
					//var_dump($project);
				}
			}
			// Traitement des Lignes/Tâches lorsque projet créé/modifié
			if(!empty($project)) {
				$assoc = [];
				$resources = [];
				$jalons = [];
				$jalon = NULL;
				if (!empty($object->lines)) foreach($object->lines as $oline) {
					// Fin jalon (ss-total)
					if ($oline->product_type==9 && $oline->qty==99) {
						$jalon = NULL;
					}
					// Texte
					elseif ($oline->product_type==9 && $oline->qty==50) {
						$jalons[$oline->id] = $jalon = $oline;
					}
					// Jalon
					elseif ($oline->product_type==9 && $oline->qty==1) {
						$jalons[$oline->id] = $jalon = $oline;
					}
					// Dans Jalon
					elseif($jalon)
						$oline->jalon = $jalon;
				}
				//var_dump($jalons); die();
				if (!empty($object->lines)) foreach($object->lines as $oline) {
					// Uniquement si produit bien paramétré
					if ($oline->product_type!=1 || !$oline->fk_product)
						continue;

					// Infos Product
					$product->fetch($oline->fk_product);
					//var_dump($product->array_options); //return -1;
					$task_resources = [];
					// Récup ressources
					$sql = 'SELECT fk_resource FROM '.MAIN_DB_PREFIX.'product_resource WHERE fk_product='.$product->id;
					$q = $db->query($sql);
					while($r=$q->fetch_array()) {
						if (!in_array($r['fk_resource'], $resources))
							$resources[] = $r['fk_resource'];
						$task_resources[] = $r['fk_resource'];
					}
					if (!$product->array_options['options_task_type'])
						continue;

					// Label
					// Nom spécifique
					//var_dump($product->array_options['options_task_name'], $product->label, $oline->product_label, $oline->desc);
					if (!empty($product->array_options['options_task_name']) && ((!empty($oline->product_label) && $product->label != $oline->product_label) || (!empty($oline->desc) && $product->label != $oline->desc))) {
						$task_label = $product->array_options['options_task_name'];
					}
					elseif ($oline->product_label) {
						$task_label = $oline->product_label;
					}
					else {
						$task_label = $oline->desc;
					}
					$task_label .= ' [depuis Commande]';
					
					//var_dump($oline);
					$oline->fetch_optionals();

					// Vérif déjà associé
					if (!empty($project->lines)) {
						foreach($project->lines as $task) {
							$task->fetch_optionals();
							//var_dump($task);
							if($task->array_options['options_fk_commandedet'] == $oline->id) {
								$assoc[$oline->id] = $task;
								//$task->label = ($oline->libelle ?$oline->libelle :$oline->desc).' [depuis Commande]';
								//$task->update($user);
								break;
							}
						}
					}
					// Création
					if (empty($assoc[$oline->id])) {
						$task = new Task($db);
						$task->fk_project = $project->id;
						$task->label = $task_label;
						$task->array_options['options_fk_commandedet'] = $oline->id;
						$task->ref = $this->getNextValue($object->thirdparty, $task);
						//var_dump($oline, $task);
						$task->create($user);
						$assoc[$oline->id] = $task;
						$task_resources2 = [];
					}
					else {
						$sql = 'SELECT fk_resource FROM '.MAIN_DB_PREFIX.'projet_task_resource WHERE fk_projet_task='.$assoc[$oline->id]->id;
						$q = $db->query($sql);
						while($r=$q->fetch_array()) {
							$task_resources2[] = $r['fk_resource'];
						}
					}
					
					//var_dump($oline); die();
					$task = $assoc[$oline->id];

					// Ajout des nouvelles ressources du produit à la tâche
					foreach ($task_resources as $resource_id) {
						if (!in_array($resource_id, $task_resources2)) {
							$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'projet_task_resource
								(fk_resource, fk_projet_task) VALUES ('.$resource_id.', '.$assoc[$oline->id]->id.')';
							$q = $db->query($sql);
						}
					}

					// Module Jalon
					if ($oline->fk_parent_line) {
						$task->array_options['options_fk_jalon_commandedet'] = $oline->fk_parent_line;
					}
					// Module Sous-Total ATM
					elseif (!empty($oline->jalon)) {
						$task->array_options['options_fk_jalon_commandedet'] = $oline->jalon->id;
					}

					// Infos ligne commande
					$task->array_options['options_qte'] = $oline->qty;
					$task->array_options['options_intervenants'] = $oline->array_options['options_intervenants'];
					$task->planned_workload = round($oline->array_options['options_heure']*3600);

					// Infos Product
					//var_dump($product->id);
					$task->array_options['options_type'] = $product->array_options['options_task_type'];
					$task->array_options['options_fk_product'] = $product->id;
					$task->array_options['options_fk_unit'] = !empty($product->id) ?$product->fk_unit :'';
					$res = $task->update($user);
					//var_dump($task);
					//var_dump($task->id);
					//var_dump($res);
				}

				// Association des ressources utilisées
				foreach($resources as $resource_id) {
					// Vérif déjà associé
					$usetask = false;
					if (!empty($project->lines)) foreach($project->lines as $task) {
						$task->fetch_optionals();
						//var_dump($task);
						if($task->array_options['options_fk_resource'] == $resource_id && empty($task->array_options['options_fk_commandedet'])) {
							$usetask = true;
							//$task->label = ($oline->libelle ?$oline->libelle :$oline->desc).' [depuis Commande]';
							//$task->update($user);
							break;
						}
					}
					if(!empty($usetask))
						continue;
					
					$resource->fetch($resource_id);
					//var_dump($resource); die();
					$task = new Task($db);
					$task->fk_project = $project->id;
					$task->label = 'Utilisation Resource : '.$resource->ref;
					$task->array_options['options_fk_resource'] = $resource->id;
					$task->ref = $this->getNextValue($object->thirdparty, $task);
					//var_dump($oline, $task);
					$task->create($user);
				}
				//var_dump($task);
				//var_dump($project);
				//var_dump($object);
			}
		}

		// Tâches

		elseif ($this->in_context($parameters, 'projecttaskcard')) {
			/** @var Task $object */
			if ($action=='confirm_moveprojecttask') {
				$id = $parameters['id'];
				$fk_projectto = GETPOST('fk_projectto');

				// Look for children
				$children_ids = [];
				$toto_parent_ids = [$id];
				while (!empty($toto_parent_ids)) {
					$sql = "SELECT rowid";
					$sql .= " FROM ".MAIN_DB_PREFIX."projet_task";
					$sql .= " WHERE fk_task_parent IN (".implode(', ', $toto_parent_ids).")";
					$toto_parent_ids = [];
					$result = $this->db->query($sql);
					$children = [];
					if ($result) {
						while ($objp = $this->db->fetch_object($result)) {
							if (!in_array($objp->rowid, $children_ids)) {
								$children_ids[] = $objp->rowid;
								$toto_parent_ids[] = $objp->rowid;
							}
						}
					}
				}
				//var_dump($children_ids); return -1;

				$object->fetch($id);
				$object->fk_project = $fk_projectto;
				$object->fk_task_parent = 0;
				$res = $object->update($user);

				foreach($children_ids as $child_id) {
					$task = new Task($this->db);
					$task->fetch($child_id);
					$task->fk_project = $fk_projectto;
					$res = $task->update($user);
				}

				//var_dump($res, $object->error);

				//var_dump("Déplacer vers : ".$fk_projectto);
				//$this->errors[] = 'Déplacé vers :'.$fk_projectto;
			}
		}

		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			return -1;
		}
	}


	/**
	 * Overloading the formConfirm function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function formConfirm($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;
		//var_dump($parameters, $action);

		$error = 0; // Error counter

		if ($this->in_context($parameters, 'projecttaskcard')) {
			if ($action=='moveprojecttask') {
				$form = $parameters['form'];

				// Choix projets
				$selectFields = " p.rowid, p.ref, p.title";
				$sql = "SELECT ";
				$sql .= $selectFields;
				$sql .= " FROM " . $this->db->prefix() . "projet as p";
				$sql .= ' WHERE p.entity IN (' . getEntity('project') . ')';
				$result = $this->db->query($sql);
				$projectslist = [];
				if ($result) {
					while ($objp = $this->db->fetch_object($result)) {
						$projectslist[$objp->rowid] = $objp->ref.' - '.$objp->title;
					}
				}
				//var_dump();

				$formquestion = [
					['type' => 'select', 'name' => 'fk_projectto', 'label' => $langs->trans('TaskMoveToProject'), 'values' => $projectslist],
				];
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('Move'), $langs->trans('ConfirmMoveProjectTask', $object->ref), 'confirm_moveprojecttask', $formquestion, 0, 1);
				$this->resprints = $formconfirm;
			}
		}

		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			// $this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doPreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doPreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$print = '';

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($this->in_context($parameters, 'projecttasktime')) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$massaction = GETPOST('massaction');
			if ($massaction=='predelete') {
				$print .= dol_get_fiche_head(null, '', '');
				$print .= '<p>Suppression en masse ?</p>';
				$print .= '<input type="hidden" name="massaction" value="delete" />';
				$print .= '<p>Confirmation : <select class="flat width75 marginleftonly marginrightonly" id="confirm" name="confirm"><option value="yes">Oui</option>
<option value="no" selected="">Non</option></select>';
				$print .= '<input class="button valignmiddle confirmvalidatebutton" type="submit" value="Supprimer" onclick="$(\'select[id=massaction]\').remove();" /></p>';
				$print .= dol_get_fiche_end();
			}
		}

		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		} else {
			// $this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
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
		$massaction = $_POST['massaction'];	// do something only for the context 'somecontext1' or 'somecontext2'
		//var_dump($_POST, $parameters, $action, $massaction);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($this->in_context($parameters, 'projecttasktime')) {		// do something only for the context 'somecontext1' or 'somecontext2'
			if ($massaction == 'delete') {
				$objectstatic = new TimeSpent($this->db);
				foreach ($parameters['toselect'] as $objectid) {
					$objectstatic->fetch($objectid);
					$objectstatic->delete($user);
				}
			}
		}

		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			// $this->errors[] = 'Error message';
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
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($this->in_context($parameters, ['projecttasktime'])) {		// do something only for the context 'somecontext1' or 'somecontext2'
			//echo 'COUCOU';
			$this->resprints = '<option value="predelete">'.img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			// $this->errors[] = 'Error message';
			return -1;
		}
	}

	public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;
		
		//var_dump($parameters, $object);
		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($this->in_context($parameters, 'ordercard')) {
			if (!empty($conf->projet->enabled)) {
				if ($object->status > 0 && !$object->fk_project)
					echo '<a id="createproject" class="butAction" href="?id='.$object->id.'&action=createproject">'.$langs->trans("MMIProjectCreate").'</a>';
				elseif ($object->fk_project) {
					echo '<a id="updateproject" class="butAction" href="?id='.$object->id.'&action=updateproject">'.$langs->trans("MMIProjectUpdate").'</a>';
					echo '<script>$(document).ready(function(){ $("a.butActionDelete").click(function(){ alert("'.$langs->trans("MMIProjectOrderLocked").'"); return false; }); });</script>';
				}
			}
		}
		elseif ($this->in_context($parameters, 'projecttaskcard')) {
			echo '<a id="moveprojecttask" class="butAction" href="?id='.$object->id.'&action=moveprojecttask">'.$langs->trans("MMIProjectTaskMove").'</a>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($this->in_context($parameters, ['somecontext1', 'somecontext2'])) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("mmiproject@mmiproject");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'mmiproject') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("MMIProject");
			$this->results['picto'] = 'mmiproject@mmiproject';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->mmiproject->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// utilisé si on veut faire disparaitre des onglets.
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('mmiproject@mmiproject');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/mmiproject/mmiproject_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('MMIProjectTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'mmiprojectemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		}
	}

	// Recherche améliorée dans la liste des tâches et dans la liste des tâches d'un projet

	function printFieldPreListTitle($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['tasklist']))
		{
			$search_no_permanent = GETPOST('search_no_permanent', 'bool');
			$print .= '<div class="divsearchfield">';
			$print .= '<input type="checkbox" id="search_no_permanent" name="search_no_permanent" value="1"'.($search_no_permanent ?' checked="checked"' :'').' /> <label for="search_no_permanent">Sans Permanent</label>';
			$print .= '</div>';
		}
		if ($this->in_context($parameters, ['tasklist', 'projecttaskscard']))
		{
			$form = new Form($this->db);

			$projectstatic = new Project($this->db);
			$search_fk_c_type_contact_project = GETPOST('search_fk_c_type_contact_project', 'int');
			$print .= '<div class="divsearchfield">';
			$print .= '<label for="search_fk_c_type_contact_project">Rôle Projet :</label>';
			$print .= $form->selectarray('search_fk_c_type_contact_project', $projectstatic->liste_type_contact('INTERNAL', 'rowid', 0, 1), $search_fk_c_type_contact_project, 1);
			$print .= '</div>';

			$taskstatic = new Task($this->db);
			$search_fk_c_type_contact_task = GETPOST('search_fk_c_type_contact_task', 'int');
			$print .= '<div class="divsearchfield">';
			$print .= '<label for="search_fk_c_type_contact_task">Rôle tâche :</label>';
			$print .= $form->selectarray('search_fk_c_type_contact_task', $taskstatic->liste_type_contact('INTERNAL', 'rowid', 0, 1), $search_fk_c_type_contact_task, 1);
			$print .= '</div>';

			$search_no_advanced_100 = GETPOST('search_no_advanced_100', 'bool');
			$print .= '<div class="divsearchfield">';
			$print .= '<input type="checkbox" id="search_no_advanced_100" name="search_no_advanced_100" value="1"'.($search_no_advanced_100 ?' checked="checked"' :'').' /> <label for="search_no_advanced_100">Sans finis 100%</for>';
			$print .= '</div>';
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['tasklist', 'projecttaskscard']))
		{
			if (GETPOST('search_no_permanent', 'bool')) {
				$tblalias = $this->in_context($parameters, ['tasklist']) ?'ef' :'efpt';
				$print .= " AND ($tblalias.permanent IS NULL OR $tblalias.permanent = 0)";
			}

			if (GETPOST('search_no_advanced_100', 'bool'))
				$print .= " AND (t.progress IS NULL OR t.progress < 100)";
			
			if (($search_fk_c_type_contact_task = GETPOST('search_fk_c_type_contact_task', 'int'))>0) {
				$search_project_user = GETPOST('search_project_user', getDolGlobalInt('PROJECT_SEARCH_USER_MULTIPLE') ?'array:int' :'int');
				$search_project_user_multiple_and = getDolGlobalInt('PROJECT_SEARCH_USER_MULTIPLE_AND');
				$search_task_user = GETPOST('search_task_user', getDolGlobalInt('PROJECT_SEARCH_USER_MULTIPLE') ?'array:int' :'int');
				$search_task_user_multiple_and = getDolGlobalInt('PROJECT_SEARCH_USER_MULTIPLE_AND');
				if (is_array($search_task_user)) {
					if ($search_task_user_multiple_and) {
						foreach($search_task_user as $i=>$uid) {
							$print .= " AND ect$i.fk_c_type_contact = ".$search_fk_c_type_contact_task." AND ect$i.element_id = t.rowid AND 	ect$i.fk_socpeople = ".((int) $uid);
						}
					}
					else {
						$print .= " AND ect.fk_c_type_contact = ".$search_fk_c_type_contact_task." AND ect.element_id = t.rowid AND ect.fk_socpeople IN (".implode(',', $search_task_user).")";
					}
				}
				elseif ($search_task_user > 0) {
					$print .= " AND ect.fk_c_type_contact = ".$search_fk_c_type_contact_task." AND ect.element_id = t.rowid AND ect.fk_socpeople = ".((int) $search_task_user);
				}
			}
			if (($search_fk_c_type_contact_project = GETPOST('search_fk_c_type_contact_project', 'int'))>0) {
				if (!empty($search_project_user)) {
					if (is_array($search_project_user)) {
						if ($search_project_user_multiple_and) {
							foreach($search_project_user as $i=>$uid) {
								$print .= " AND ecp$i.fk_c_type_contact = ".$search_fk_c_type_contact_project." AND ecp$i.element_id = p.rowid AND 	ecp$i.fk_socpeople = ".((int) $uid);
							}
						}
						else {
							$print .= " AND ecp.fk_c_type_contact = ".$search_fk_c_type_contact_project." AND ecp.element_id = p.rowid AND ecp.fk_socpeople IN (".implode(',', $search_project_user).")";
						}
					}
					elseif ($search_project_user > 0) {
						// TODO Replace this with a EXISTS and remove the link to table + DISTINCT
						$print .= " AND ecp.fk_c_type_contact = ".$search_fk_c_type_contact_project." AND ecp.element_id = p.rowid AND ecp.fk_socpeople = ".((int) $search_project_user);
					}
				}
			}
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListFrom($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['tasklist']))
		{
			//
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function printFieldListSearchParam($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['tasklist', 'projecttaskscard']))
		{
			if (GETPOST('search_no_permanent', 'bool')) {
				$print .= '&search_no_permanent=1';
			}
			if (GETPOST('search_no_advanced_100', 'bool')) {
				$print .= '&search_no_advanced_100=1';
			}
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/* Add here any other hooked methods... */
}

ActionsMMIProject::__init();
