<?php

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
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'product_resource
		(`fk_product`, `usage`, `fk_resource`)
		VALUES
		('.$id.', "'.$usage.'", '.$fk_resource.')';
	//, `fk_c_type_resource`
	$db->query($sql);
}

if ($action == 'resource_edit') {
	$sql = 'UPDATE '.MAIN_DB_PREFIX.'product_resource
		SET `usage`="'.$usage.'", `fk_resource`='.$fk_resource.'
		WHERE rowid='.$link_id.'';
		//, `fk_c_type_resource`='.$fk_c_type_resource.'
	$db->query($sql);
}

if (!empty($del = GETPOST('delete', 'int'))) {
	$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'product_resource
		WHERE rowid='.$del.'';
	$db->query($sql);
}

$edit = GETPOST('edit', 'int');
if (!empty($edit)) {
	$sql = 'SELECT pr.*
	FROM `'.MAIN_DB_PREFIX.'product_resource` AS pr
		WHERE pr.`rowid`='.$edit;
	//echo $sql;
	$q = $db->query($sql);
	$link = $q->fetch_assoc();
}
//var_dump($link);

// Ressources liées
$pr = [];
$sql = 'SELECT pr.*
	FROM `'.MAIN_DB_PREFIX.'product_resource` AS pr
	WHERE pr.`fk_product`='.$object->id.'
	ORDER BY pr.`pos`';
//echo $sql;
$q = $db->query($sql);
while($r=$q->fetch_assoc())
	$pr[$r['rowid']] = $r;
//var_dump($q);
