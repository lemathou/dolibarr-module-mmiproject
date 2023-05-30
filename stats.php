<?php
/* Copyright (C) 2022 Moulin Mathieu <contact@iprospective.fr>
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
dol_include_once('/mmicommon/lib/mmi.lib.php');

// Access control
if (!$user->admin) {
	accessforbidden();
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';

$page_title = "MMIProjectAreaStats";

/*
 * View
 */

$form = new Form($db);
//$formfile = new FormFile($db);

llxHeader("", $langs->trans($page_title));
echo '<link rel="stylesheet" href="css/mmiprojects.css" />';

print load_fiche_titre($langs->trans($page_title), '', $modulecontext);

$l = [];

// Temps : 
$sql = 'SELECT cd.fk_product, a.label,
	SUM(ptt.task_duration)/3600 AS duration,
	COUNT(DISTINCT p.rowid) AS projet_nb, COUNT(DISTINCT pt.rowid) AS task_nb, COUNT(DISTINCT ptt.rowid) AS time_nb, COUNT(DISTINCT ptt.fk_user) AS user_nb, COUNT(DISTINCT cd.rowid) commandedet_nb1
	FROM '.MAIN_DB_PREFIX.'projet_task_time AS ptt
	INNER JOIN '.MAIN_DB_PREFIX.'projet_task AS pt
		ON pt.rowid=ptt.fk_task
	INNER JOIN '.MAIN_DB_PREFIX.'projet AS p
		ON p.rowid=pt.fk_projet
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_extrafields AS p2
		ON p2.fk_object=p.rowid
	INNER JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields AS pt2
		ON pt2.fk_object=pt.rowid

	INNER JOIN '.MAIN_DB_PREFIX.'commandedet AS cd
		ON cd.rowid=pt2.fk_commandedet
	
	INNER JOIN '.MAIN_DB_PREFIX.'product AS a
		ON a.rowid=cd.fk_product

	WHERE 1
	
	GROUP BY cd.fk_product';
	//INNER JOIN '.MAIN_DB_PREFIX.'product AS a ON (a.rowid=cd.fk_product OR a.rowid=pt2.fk_product)
//echo $sql;
$q = $db->query($sql);
//var_dump($q);
while($r=$q->fetch_assoc()) {
	//var_dump($r);
	$l[$r['fk_product']] = $r;
}

// Commandes : quantité commandée, etc.
$sql = 'SELECT cd.fk_product, a.label,
	COUNT(DISTINCT cd.fk_commande) commande_nb, 
	COUNT(DISTINCT cd.rowid) commandedet_nb, SUM(cd.qty) AS commandedet_qte,
	(SELECT SUM(_cd.total_ht) FROM '.MAIN_DB_PREFIX.'commandedet _cd WHERE _cd.rowid=cd.rowid) AS commandedet_amount
	FROM '.MAIN_DB_PREFIX.'commandedet cd
	
	INNER JOIN '.MAIN_DB_PREFIX.'product AS a
		ON a.rowid=cd.fk_product

	WHERE cd.rowid IN (
		SELECT pt2.fk_commandedet
		FROM '.MAIN_DB_PREFIX.'projet_task_time AS ptt
		INNER JOIN '.MAIN_DB_PREFIX.'projet_task AS pt
			ON pt.rowid=ptt.fk_task
		INNER JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields AS pt2
			ON pt2.fk_object=pt.rowid
		WHERE 1
	)
	GROUP BY cd.fk_product
';
//echo $sql;
$q = $db->query($sql);
//var_dump($q);
while($r=$q->fetch_assoc()) {
	//var_dump($r);
	$l[$r['fk_product']] = array_merge($l[$r['fk_product']], $r);
}

$l[5] = ['kk'=>f, 'efefe'=>9];

$h = false;
echo '<table border="1" cellpadding="4" class="table">';
foreach($l as $r) {
	if (!$h) {
		echo '<thead><tr>';
		foreach(array_keys($r) as $f)
			echo '<th>'.$f.'<th>';
		echo '</tr></thead>';
		echo '<tbody>';
		$h = true;
	}
	echo '<tr>';
	foreach($r as $v)
		echo '<td align="right">'.$v.'<td>';
	echo '<td align="right">'.($r['commandedet_amount']/$r['duration']).'<td>';
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';

// End of page
llxFooter();
$db->close();
