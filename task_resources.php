<?php
/* Copyright (C) 2005		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2006-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2010-2012	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
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
 *	\file       htdocs/projet/tasks/task.php
 *	\ingroup    project
 *	\brief      Page of a project task
 */

// Load Dolibarr environment
require_once 'env.inc.php';
require_once 'main_load.inc.php';
 
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/task/modules_task.php';

// Load translation files required by the page
$langs->loadlangs(array('projects', 'companies'));

$_GET['withproject'] = 1;

$id = GETPOST('id', 'int');
$ref = GETPOST("ref", 'alpha', 1); // task ref
$taskref = GETPOST("taskref", 'alpha'); // task ref
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$withproject = GETPOST('withproject', 'int');
$project_ref = GETPOST('project_ref', 'alpha');
$planned_workload = ((GETPOST('planned_workloadhour', 'int') != '' || GETPOST('planned_workloadmin', 'int') != '') ? (GETPOST('planned_workloadhour', 'int') > 0 ?GETPOST('planned_workloadhour', 'int') * 3600 : 0) + (GETPOST('planned_workloadmin', 'int') > 0 ?GETPOST('planned_workloadmin', 'int') * 60 : 0) : '');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('projecttaskcard', 'globalcard'));

$object = new Task($db);
$extrafields = new ExtraFields($db);
$projectstatic = new Project($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$parameters = array('id'=>$id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if ($id > 0 || $ref) {
	$object->fetch($id, $ref);
}

// Security check
$socid = 0;

restrictedArea($user, 'projet', $object->fk_project, 'projet&project');



/*
 * Actions
 */

if ($action == 'update' && !GETPOST("cancel") && $user->rights->projet->creer) {
	$error = 0;

	if (empty($taskref)) {
		$error++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
	}
	if (!GETPOST("label")) {
		$error++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
	}
	if (!$error) {
		$object->oldcopy = clone $object;

		$tmparray = explode('_', $_POST['task_parent']);
		$task_parent = $tmparray[1];
		if (empty($task_parent)) {
			$task_parent = 0; // If task_parent is ''
		}

		$object->ref = $taskref ? $taskref : GETPOST("ref", 'alpha', 2);
		$object->label = GETPOST("label", "alphanohtml");
		$object->description = GETPOST('description', "alphanohtml");
		$object->fk_task_parent = $task_parent;
		$object->planned_workload = $planned_workload;
		$object->date_start = dol_mktime(GETPOST('dateohour', 'int'), GETPOST('dateomin', 'int'), 0, GETPOST('dateomonth', 'int'), GETPOST('dateoday', 'int'), GETPOST('dateoyear', 'int'));
		$object->date_end = dol_mktime(GETPOST('dateehour', 'int'), GETPOST('dateemin', 'int'), 0, GETPOST('dateemonth', 'int'), GETPOST('dateeday', 'int'), GETPOST('dateeyear', 'int'));
		$object->progress = price2num(GETPOST('progress', 'alphanohtml'));

		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost(null, $object, '@GETPOSTISSET');
		if ($ret < 0) {
			$error++;
		}

		if (!$error) {
			$result = $object->update($user);
			if ($result < 0) {
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	} else {
		$action = 'edit';
	}
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->supprimer) {
	$result = $projectstatic->fetch($object->fk_project);
	$projectstatic->fetch_thirdparty();

	if ($object->delete($user) > 0) {
		header('Location: '.DOL_URL_ROOT.'/projet/tasks.php?restore_lastsearch_values=1&id='.$projectstatic->id.($withproject ? '&withproject=1' : ''));
		exit;
	} else {
		setEventMessages($object->error, $object->errors, 'errors');
		$action = '';
	}
}

// Retrieve First Task ID of Project if withprojet is on to allow project prev next to work
if (!empty($project_ref) && !empty($withproject)) {
	if ($projectstatic->fetch('', $project_ref) > 0) {
		$tasksarray = $object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($tasksarray) > 0) {
			$id = $tasksarray[0]->id;
		} else {
			header("Location: ".DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.(empty($mode) ? '' : '&mode='.$mode));
		}
	}
}

// Build doc
if ($action == 'builddoc' && $user->rights->projet->creer) {
	// Save last template used to generate document
	if (GETPOST('model')) {
		$object->setDocModel($user, GETPOST('model', 'alpha'));
	}

	$outputlangs = $langs;
	if (GETPOST('lang_id', 'aZ09')) {
		$outputlangs = new Translate("", $conf);
		$outputlangs->setDefaultLang(GETPOST('lang_id', 'aZ09'));
	}
	$result = $object->generateDocument($object->model_pdf, $outputlangs);
	if ($result <= 0) {
		setEventMessages($object->error, $object->errors, 'errors');
		$action = '';
	}
}

// Delete file in doc form
if ($action == 'remove_file' && $user->rights->projet->creer) {
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	$langs->load("other");
	$upload_dir = $conf->projet->dir_output;
	$file = $upload_dir.'/'.dol_sanitizeFileName(GETPOST('file'));

	$ret = dol_delete_file($file);
	if ($ret) {
		setEventMessages($langs->trans("FileWasRemoved", GETPOST('urlfile')), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('urlfile')), null, 'errors');
	}
}


/*
 * View
 */

llxHeader('', $langs->trans("Task"));

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);

if ($id > 0 || !empty($ref)) {
	$res = $object->fetch_optionals();
	if (!empty($conf->global->PROJECT_ALLOW_COMMENT_ON_TASK) && method_exists($object, 'fetchComments') && empty($object->comments)) {
		$object->fetchComments();
	}

	$result = $projectstatic->fetch($object->fk_project);
	if (!empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($projectstatic, 'fetchComments') && empty($projectstatic->comments)) {
		$projectstatic->fetchComments();
	}
	if (!empty($projectstatic->socid)) {
		$projectstatic->fetch_thirdparty();
	}

	$object->project = clone $projectstatic;

	//$userWrite = $projectstatic->restrictedProjectArea($user, 'write');

	if (!empty($withproject)) {
		// Tabs for project
		$tab = 'tasks';
		$head = project_prepare_head($projectstatic);
		print dol_get_fiche_head($head, $tab, $langs->trans("Project"), -1, ($projectstatic->public ? 'projectpub' : 'project'), 0, '', '');

		$param = ($mode == 'mine' ? '&mode=mine' : '');

		// Project card

		$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

		$morehtmlref = '<div class="refidno">';
		// Title
		$morehtmlref .= $projectstatic->title;
		// Thirdparty
		if ($projectstatic->thirdparty->id > 0) {
			$morehtmlref .= '<br>'.$langs->trans('ThirdParty').' : '.$projectstatic->thirdparty->getNomUrl(1, 'project');
		}
		$morehtmlref .= '</div>';

		// Define a complementary filter for search of next/prev ref.
		if (!$user->rights->projet->all->lire) {
			$objectsListId = $projectstatic->getProjectsAuthorizedForUser($user, 0, 0);
			$projectstatic->next_prev_filter = " rowid IN (".$db->sanitize(count($objectsListId) ?join(',', array_keys($objectsListId)) : '0').")";
		}

		dol_banner_tab($projectstatic, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border tableforfield centpercent">';

		// Usage
		if (!empty($conf->global->PROJECT_USE_OPPORTUNITIES) || empty($conf->global->PROJECT_HIDE_TASKS) || !empty($conf->eventorganization->enabled)) {
			print '<tr><td class="tdtop">';
			print $langs->trans("Usage");
			print '</td>';
			print '<td>';
			if (!empty($conf->global->PROJECT_USE_OPPORTUNITIES)) {
				print '<input type="checkbox" disabled name="usage_opportunity"'.(GETPOSTISSET('usage_opportunity') ? (GETPOST('usage_opportunity', 'alpha') != '' ? ' checked="checked"' : '') : ($projectstatic->usage_opportunity ? ' checked="checked"' : '')).'"> ';
				$htmltext = $langs->trans("ProjectFollowOpportunity");
				print $form->textwithpicto($langs->trans("ProjectFollowOpportunity"), $htmltext);
				print '<br>';
			}
			if (empty($conf->global->PROJECT_HIDE_TASKS)) {
				print '<input type="checkbox" disabled name="usage_task"'.(GETPOSTISSET('usage_task') ? (GETPOST('usage_task', 'alpha') != '' ? ' checked="checked"' : '') : ($projectstatic->usage_task ? ' checked="checked"' : '')).'"> ';
				$htmltext = $langs->trans("ProjectFollowTasks");
				print $form->textwithpicto($langs->trans("ProjectFollowTasks"), $htmltext);
				print '<br>';
			}
			if (empty($conf->global->PROJECT_HIDE_TASKS) && !empty($conf->global->PROJECT_BILL_TIME_SPENT)) {
				print '<input type="checkbox" disabled name="usage_bill_time"'.(GETPOSTISSET('usage_bill_time') ? (GETPOST('usage_bill_time', 'alpha') != '' ? ' checked="checked"' : '') : ($projectstatic->usage_bill_time ? ' checked="checked"' : '')).'"> ';
				$htmltext = $langs->trans("ProjectBillTimeDescription");
				print $form->textwithpicto($langs->trans("BillTime"), $htmltext);
				print '<br>';
			}
			if (!empty($conf->eventorganization->enabled)) {
				print '<input type="checkbox" disabled name="usage_organize_event"'.(GETPOSTISSET('usage_organize_event') ? (GETPOST('usage_organize_event', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_organize_event ? ' checked="checked"' : '')).'"> ';
				$htmltext = $langs->trans("EventOrganizationDescriptionLong");
				print $form->textwithpicto($langs->trans("ManageOrganizeEvent"), $htmltext);
			}
			print '</td></tr>';
		}

		// Visibility
		print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
		if ($projectstatic->public) {
			print $langs->trans('SharedProject');
		} else {
			print $langs->trans('PrivateProject');
		}
		print '</td></tr>';

		// Date start - end
		print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
		$start = dol_print_date($projectstatic->date_start, 'day');
		print ($start ? $start : '?');
		$end = dol_print_date($projectstatic->date_end, 'day');
		print ' - ';
		print ($end ? $end : '?');
		if ($projectstatic->hasDelay()) {
			print img_warning("Late");
		}
		print '</td></tr>';

		// Budget
		print '<tr><td>'.$langs->trans("Budget").'</td><td>';
		if (strcmp($projectstatic->budget_amount, '')) {
			print price($projectstatic->budget_amount, '', $langs, 1, 0, 0, $conf->currency);
		}
		print '</td></tr>';

		// Other attributes
		$cols = 2;
		//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

		print '</table>';

		print '</div>';

		print '<div class="fichehalfright">';
		print '<div class="ficheaddleft">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border centpercent">';

		// Description
		print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
		print nl2br($projectstatic->description);
		print '</td></tr>';

		// Categories
		if ($conf->categorie->enabled) {
			print '<tr><td class="valignmiddle">'.$langs->trans("Categories").'</td><td>';
			print $form->showCategories($projectstatic->id, 'project', 1);
			print "</td></tr>";
		}

		print '</table>';

		print '</div>';
		print '</div>';
		print '</div>';

		print '<div class="clearboth"></div>';

		print dol_get_fiche_end();

		print '<br>';
	}

	/*
	 * Actions
	*/
	/*print '<div class="tabsAction">';

	if ($user->rights->projet->all->creer || $user->rights->projet->creer)
	{
	if ($projectstatic->public || $userWrite > 0)
	{
	print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=create'.$param.'">'.$langs->trans('AddTask').'</a>';
	}
	else
	{
	print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("NotOwnerOfProject").'">'.$langs->trans('AddTask').'</a>';
	}
	}
	else
	{
	print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('AddTask').'</a>';
	}

	print '</div>';
	*/

	// To verify role of users
	//$userAccess = $projectstatic->restrictedProjectArea($user); // We allow task affected to user even if a not allowed project
	//$arrayofuseridoftask=$object->getListContactId('internal');

	$head = task_prepare_head($object);
	{
		/*
		 * Fiche tache en mode visu
		 */
		$param = ($withproject ? '&withproject=1' : '');
		$linkback = $withproject ? '<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'&restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>' : '';

		print dol_get_fiche_head($head, 'task_resources', $langs->trans("Task"), -1, 'projecttask', 0, '', 'reposition');

		if ($action == 'delete') {
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".GETPOST("id", 'int').'&withproject='.$withproject, $langs->trans("DeleteATask"), $langs->trans("ConfirmDeleteATask"), "confirm_delete");
		}

		if (!GETPOST('withproject') || empty($projectstatic->id)) {
			$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user, 0, 1);
			$object->next_prev_filter = " fk_projet IN (".$db->sanitize($projectsListId).")";
		} else {
			$object->next_prev_filter = " fk_projet = ".((int) $projectstatic->id);
		}

		$morehtmlref = '';

		// Project
		if (empty($withproject)) {
			$morehtmlref .= '<div class="refidno">';
			$morehtmlref .= $langs->trans("Project").': ';
			$morehtmlref .= $projectstatic->getNomUrl(1);
			$morehtmlref .= '<br>';

			// Third party
			$morehtmlref .= $langs->trans("ThirdParty").': ';
			if (!empty($projectstatic->thirdparty)) {
				$morehtmlref .= $projectstatic->thirdparty->getNomUrl(1);
			}
			$morehtmlref .= '</div>';
		}

		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';

		print '<div class="underbanner clearboth"></div>';

		// Ressources
		$reso = [];
		$sql = 'SELECT r.*
			FROM `'.MAIN_DB_PREFIX.'resource` AS r';
		$q = $db->query($sql);
		while($r=$q->fetch_assoc())
			$reso[$r['rowid']] = $r;
		//var_dump($reso);

		// Types de ressources
		$rt = [];
		$sql = 'SELECT rt.*
			FROM `'.MAIN_DB_PREFIX.'c_type_resource` AS rt';
		$q = $db->query($sql);
		while($r=$q->fetch_assoc())
			$rt[$r['rowid']] = $r;
		//var_dump($rt);

		$link_id = GETPOST('link_id', 'int');
		$fk_c_type_resource = GETPOST('fk_c_type_resource', 'int');
		$fk_resource = GETPOST('fk_resource', 'int');
		$usage = GETPOST('usage', 'alpha');

		if ($action == 'resource_add') {
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'projet_task_resource
				(`fk_projet_task`, `fk_resource`)
				VALUES
				('.$id.', '.$fk_resource.')';
			//, `fk_c_type_resource`
			//echo $sql;
			$db->query($sql);
		}

		if ($action == 'resource_edit') {
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'projet_task_resource
				SET `usage`="'.$usage.'", `fk_resource`='.$fk_resource.'
				WHERE rowid='.$link_id.'';
				//, `fk_c_type_resource`='.$fk_c_type_resource.'
			$db->query($sql);
		}

		if (!empty($del = GETPOST('delete', 'int'))) {
			$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'projet_task_resource
				WHERE rowid='.$del.'';
			//echo $sql;
			$db->query($sql);
		}

		$edit = GETPOST('edit', 'int');
		if (!empty($edit)) {
			$sql = 'SELECT pr.*
			FROM `'.MAIN_DB_PREFIX.'projet_task_resource` AS pr
				WHERE pr.`rowid`='.$edit;
			//echo $sql;
			$q = $db->query($sql);
			$link = $q->fetch_assoc();
		}
		//var_dump($link);

		// Ressources liées
		$pr = [];
		$sql = 'SELECT pr.*
			FROM `'.MAIN_DB_PREFIX.'projet_task_resource` AS pr
			WHERE pr.`fk_projet_task`='.$object->id;
		//echo $sql;
		$q = $db->query($sql);
		while($r=$q->fetch_assoc())
			$pr[$r['rowid']] = $r;
		//var_dump($q);

		//include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';
		$resources_crud_links = true;
		include DOL_DOCUMENT_ROOT.'/custom/mmiproject/tpl/resources.tpl.php';

		print '<table class="border centpercent tableforfield">';

		print '</table>';
		print '</div>';

		print '<div class="fichehalfright"><div class="ficheaddleft">';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border centpercent tableforfield">';

		print '</table>';

		print '</div>';
		print '</div>';

		print '</div>';
		print '<div class="clearboth"></div>';

		print dol_get_fiche_end();
	}

}

// End of page
llxFooter();
$db->close();
