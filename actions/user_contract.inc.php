<?php

$link_id = GETPOST('link_id', 'int');
$status = GETPOST('status', 'int');
$ref = GETPOST('ref', 'alpha');
$job = GETPOST('job', 'alpha');
$salary = GETPOST('salary', 'double');
$weeklyhours = GETPOST('weeklyhours', 'double');
$dailyhours = GETPOST('dailyhours', 'double');
$workdaysnb = GETPOST('workdaysnb', 'int');
$workdays = GETPOST('workdays', 'array:int');
$workdays2 = GETPOST('workdays2', 'array:int');
$dateemployment = GETPOST('dateemployment', 'date');
$dateemploymentend = GETPOST('dateemploymentend', 'date');

if ($action == 'contract_add') {
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'user_employment
		(`fk_user`, `status`, `ref`, `job`, `salary`, `weeklyhours`, `dailyhours`, `workdaysnb`, `workdays`, `workdays2`, `dateemployment`, `dateemploymentend`)
		VALUES
		('.$id.', "'.$status.'", "'.$ref.'", "'.$job.'", '.(is_numeric($salary) ?'"'.$salary.'"' :'NULL').', '.(is_numeric($weeklyhours) ?'"'.$weeklyhours.'"' :'NULL').', '.(is_numeric($dailyhours) ?'"'.$dailyhours.'"' :'NULL').', '.(is_numeric($workdaysnb) ?'"'.$workdaysnb.'"' :'NULL').', '.(!empty($workdays) ?'"'.implode(',', $workdays).'"' :'NULL').', '.(!empty($workdays2) ?'"'.implode(',', $workdays2).'"' :'NULL').', '.(!empty($dateemployment) ?'"'.$dateemployment.'"' :'NULL').', '.(!empty($dateemploymentend) ?'"'.$dateemploymentend.'"' :'NULL').')';
	//, `fk_c_type_resource`
	$db->query($sql);
}

if ($action == 'contract_edit') {
	$sql = 'UPDATE '.MAIN_DB_PREFIX.'user_employment
		SET `status`="'.$status.'", `ref`="'.$ref.'", `job`="'.$job.'", `salary`='.(is_numeric($salary) ?'"'.$salary.'"' :'NULL').', `weeklyhours`='.(is_numeric($weeklyhours) ?'"'.$weeklyhours.'"' :'NULL').', `dailyhours`='.(is_numeric($dailyhours) ?'"'.$dailyhours.'"' :'NULL').', `workdaysnb`='.(is_numeric($workdaysnb) ?'"'.$workdaysnb.'"' :'NULL').', `workdays`='.(!empty($workdays) ?'"'.implode(',', $workdays).'"' :'NULL').', `workdays2`='.(!empty($workdays2) ?'"'.implode(',', $workdays2).'"' :'NULL').', `dateemployment`='.(!empty($dateemployment) ?'"'.$dateemployment.'"' :'NULL').', `dateemploymentend`='.(!empty($dateemploymentend) ?'"'.$dateemploymentend.'"' :'NULL').'
		WHERE rowid='.$link_id.'';
		//, `fk_c_type_resource`='.$fk_c_type_resource.'
	$db->query($sql);
}

if (!empty($del = GETPOST('contract_delete', 'int'))) {
	$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'user_employment
		WHERE rowid='.$del.' AND fk_user='.$id;
	$db->query($sql);
}

$edit = GETPOST('edit', 'int');
if (!empty($edit)) {
	$sql = 'SELECT pr.*
	FROM `'.MAIN_DB_PREFIX.'user_employment` AS pr
		WHERE pr.`rowid`='.$edit.' AND pr.fk_user='.$id;
	//echo $sql;
	$q = $db->query($sql);
	$link = $q->fetch_assoc();
	if (!empty($link['workdays']))
		$link['workdays'] = explode(',', $link['workdays']);
	if (!empty($link['workdays2']))
		$link['workdays2'] = explode(',', $link['workdays2']);
}

$contracts = [];
$sql = 'SELECT r.*
	FROM `'.MAIN_DB_PREFIX.'user_employment` AS r
	WHERE r.fk_user='.$id;
$q = $db->query($sql);
while($r=$q->fetch_assoc())
	$contracts[$r['rowid']] = $r;