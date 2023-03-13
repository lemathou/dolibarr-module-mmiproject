<?php

// Tâches
$reso = [];
$sql = 'SELECT r.*
	FROM `'.MAIN_DB_PREFIX.'resource` AS r';
$q = $db->query($sql);
while($r=$q->fetch_assoc())
	$reso[$r['rowid']] = $r;
//var_dump($reso);

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

// Ressources liées
$pr = [];
$sql = 'SELECT pr.*, pt.rowid AS fk_task, pt.label AS task_name
	FROM `'.MAIN_DB_PREFIX.'projet_task_resource` AS pr
	INNER JOIN `'.MAIN_DB_PREFIX.'projet_task` AS pt
		ON pt.rowid = pr.fk_projet_task
	WHERE pt.`fk_projet`='.$object->id;
//echo $sql;
$q = $db->query($sql);
//var_dump($q);
while($r=$q->fetch_assoc())
	$pr[$r['rowid']] = $r;
//var_dump($q);

$cssclass = "titlefield";
echo '<h3>Liste des ressources liées</h3>';
echo '<p>Les ressources ci-dessous sont liées aux tâches du projet.</p>';
echo '<p>Vous pouvez en ajouter/supprimer à partir des tâches.</p>';
include DOL_DOCUMENT_ROOT.'/custom/mmiproject/tpl/resources_list.tpl.php';

print '</div>';
