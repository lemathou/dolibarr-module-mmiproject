<?php
/* Copyright (C) 2024 Moulin Mathieu iProspective <contact@iprospective.fr>
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

// Load Dolibarr environment
require_once 'env.inc.php';
require_once 'main_load.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';

dol_include_once('/mmiproject/lib/mmiproject.lib.php');

setlocale(LC_TIME, "fr_FR.utf8");
date_default_timezone_set('Europe/Paris');

$right_contract_all = $user->rights->mmiproject->contract->all;

$action = GETPOST('action');

// Mise à jour des heures sup
if ($action=='hsup') {
	if (! $right_contract_all) {
		die(json_encode(['r'=>false, 'error'=>"Unauthorized"]));
	}
	$hsup = GETPOST('hsup');
	if (!is_numeric($hsup))
		$hsup = NULL;
	$user_id = GETPOST('user_id');
	if (!is_numeric($user_id))
		die(json_encode(['r'=>false, 'error'=>'Invalid user']));
	$month = GETPOST('month');
	if (!$month)
		die(json_encode(['r'=>false, 'error'=>'Invalid month']));
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'user_pay WHERE fk_user='.$user_id.' AND `date`="'.$month.'-01"';
	$resql = $db->query($sql);
	if ($resql && ($db->num_rows($resql) > 0) && (list($rowid)=$resql->fetch_row())) {
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'user_pay SET paid_hrsup='.(is_numeric($hsup) ?$hsup :'NULL').' WHERE rowid='.$rowid;
		$db->query($sql);
	}
	else {
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'user_pay (`fk_user`, `date`, `paid_hrsup`) VALUES('.$user_id.', "'.$month.'-01", '.(is_numeric($hsup) ?$hsup :'NULL').')';
		$db->query($sql);
	}
	die(json_encode(['r'=>true, 'message'=>$sql]));
}

// Mise à jour des heures sup décalées
if ($action=='decal_hsup_conge') {
	if (! $right_contract_all) {
		die(json_encode(['r'=>false, 'error'=>"Unauthorized"]));
	}
	$hsup = GETPOST('hsup');
	if (!is_numeric($hsup))
		$hsup = NULL;
	$user_id = GETPOST('user_id');
	if (!is_numeric($user_id))
		die(json_encode(['r'=>false, 'error'=>'Invalid user']));
	$month = GETPOST('month');
	if (!$month)
		die(json_encode(['r'=>false, 'error'=>'Invalid month']));
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'user_pay WHERE fk_user='.$user_id.' AND `date`="'.$month.'-01"';
	$resql = $db->query($sql);
	if ($resql && ($db->num_rows($resql) > 0) && (list($rowid)=$resql->fetch_row())) {
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'user_pay SET decal_hsup_conge='.(is_numeric($hsup) ?$hsup :'NULL').' WHERE rowid='.$rowid;
		$db->query($sql);
	}
	else {
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'user_pay (`fk_user`, `date`, `decal_hsup_conge`) VALUES('.$user_id.', "'.$month.'-01", '.(is_numeric($hsup) ?$hsup :'NULL').')';
		$db->query($sql);
	}
	die(json_encode(['r'=>true, 'message'=>$sql]));
}
