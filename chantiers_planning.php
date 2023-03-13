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

dol_include_once('/mmiproject/lib/mmiproject.lib.php');

$time = time();

$date = GETPOST('date');
if (empty($date))
	$date = date('m/Y');
list($month, $year) = explode('/', $date);
$year_month = $year.'-'.$month;

$mois_nbdays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$mois_debut_date = $year_month.'-01';
$mois_debut_ts = strtotime($mois_debut_date);
$mois_debut_weeknum = date('W', $mois_debut_ts);
$mois_debut_dayofweek = date('D', $mois_debut_ts);
$mois_debut_daynumofweek = date('w', $mois_debut_ts);

$mois_fin_date = $year_month.'-'.$mois_nbdays;
$mois_fin_ts = strtotime($mois_fin_date);
$mois_fin_weeknum = date('W', $mois_fin_ts);
$mois_fin_dayofweek = date('D', $mois_fin_ts);
$mois_fin_daynumofweek = date('w', $mois_fin_ts);

$hide = GETPOST('hide');
$chantiers = GETPOST('chantiers');
$hidenodate = GETPOST('hidenodate');

/*
 * View
 */

//$form = new Form($db);
//$formfile = new FormFile($db);

llxHeader("", $langs->trans("MMIProjectAreaChantiers"));
echo '<link rel="stylesheet" href="css/mmiprojects.css">';

print load_fiche_titre($langs->trans("MMIProjectAreaChantiers"), '', 'mmiproject@mmiproject');

print '<form method="POST" id="searchFormList" class="listactionsfilter" action="'.$_SERVER["PHP_SELF"].'">'."\n";
print '<input type="hidden" name="token" value="'.newToken().'" />';

echo '<table border="1">';
echo '<tr>';
echo '<td>';
$sql = 'SELECT DISTINCT YEAR(pt.datee) `year`, DATE_FORMAT(pt.datee, "%m") `month`
    FROM '.MAIN_DB_PREFIX.'projet_task pt
    WHERE pt.datee IS NOT NULL';
//echo $sql;
$sql = '(SELECT DISTINCT YEAR(p.dateo) `year`, DATE_FORMAT(p.dateo, "%m") `month`
    FROM '.MAIN_DB_PREFIX.'projet p
    WHERE p.dateo IS NOT NULL)
	UNION DISTINCT
	(SELECT DISTINCT YEAR(p.datee) `year`, DATE_FORMAT(p.datee, "%m") `month`
    FROM '.MAIN_DB_PREFIX.'projet p
    WHERE p.datee IS NOT NULL)
	UNION DISTINCT
	(SELECT DISTINCT YEAR(pt.dateo) `year`, DATE_FORMAT(pt.dateo, "%m") `month`
    FROM '.MAIN_DB_PREFIX.'projet_task pt
    WHERE pt.dateo IS NOT NULL)
	UNION DISTINCT
	(SELECT DISTINCT YEAR(pt.datee) `year`, DATE_FORMAT(pt.datee, "%m") `month`
    FROM '.MAIN_DB_PREFIX.'projet_task pt
    WHERE pt.datee IS NOT NULL)
	ORDER BY year, month';
//echo $sql;
$q = $db->query($sql);
//var_dump($q); var_dump($db);
echo '<p>Mois : <select name="date">';
if ($q) {
    while($r=$db->fetch_array($q)) {
        $r_date = $r['month'].'/'.$r['year'];
        echo '<option value="'.$r_date.'"'.($date==$r_date ?' selected' :'').'>'.$r_date.'</option>';
    }
}
echo '</select></p>';
echo '</td>';
echo '<td>';
//echo '<p><select name="mode">';
$modes = [''];
echo '</select></p>';
echo '</td>';
echo '<td>';
echo '<p><input type="checkbox" name="chantiers"'.($chantiers ?' checked' :'').' /> uniquement les projets de chantier</p>';
echo '<p><input type="checkbox" name="hide"'.($hide ?' checked' :'').' /> Cacher les projets permanent</p>';
echo '<p><input type="checkbox" name="hidenodate"'.($hidenodate ?' checked' :'').' /> Cacher les projets sans dates début ni fin</p>';
echo '</td>';
echo '<td>';
echo '<input type="submit" value="Afficher" />';
echo '</td>';
echo '</tr>';
echo '</table>';
print '</form>';

// @todo : par défaut 6 mois glissant, possibilité modifier

echo '<p>Du '.$mois_debut_dayofweek.' '.$mois_debut_date.' au '.$mois_fin_dayofweek.' '.$mois_fin_date.'</p>';
echo '<p><a href="">&lt;&lt;</a> Semaines '.$mois_debut_weeknum.' à '.$mois_fin_weeknum.'</p>';


// Semaines
$weeks = [];
$dates = [];
$dto = new DateTime();
$dto->setISODate($year, $mois_debut_weeknum);
$periode_debut_date = $dto->format('Y-m-d');
$periode_fin_date = null;
for($w=(int)$mois_debut_weeknum; $w<=$mois_fin_weeknum; $w++) {
    $weeks[$w] = [];
    //var_dump($w);
	$dto->setISODate($year, $w);
    for($i=1; $i<=6; $i++) {
        $d = $dto->format('Y-m-d');
        //var_dump($d);

        $dates[$d] = [
			'date' => $dto->format('d/m/Y'),
			'projects' => [],
			'tasks' => [],
		];
        $weeks[$w][$d] = &$dates[$d];

        if ($i<6) {
            $dto->modify('+1 days');
        }
        else {
            if ($w==$mois_fin_weeknum) {
                $periode_fin_date = $d;
            }
            break;
        }
    }
}

$fk_project_contacts = [];
$sql = 'SELECT rowid
    FROM '.MAIN_DB_PREFIX.'c_type_contact
    WHERE element="project"'; 
//echo $sql;
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_object($q)) {
        //var_dump($r);
        $fk_project_contacts[] = $r->rowid;
    }
}

$fk_project_tasks_contacts = [];
$sql = 'SELECT rowid
    FROM '.MAIN_DB_PREFIX.'c_type_contact
    WHERE element="project_task"'; 
//echo $sql;
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_object($q)) {
        //var_dump($r);
        $fk_project_tasks_contacts[] = $r->rowid;
    }
}


// Tâches dans ces dates + id projet
$sql = 'SELECT DISTINCT pt.rowid, pt.dateo, pt.datee, pt.fk_project, GROUP_CONCAT(c.fk_socpeople SEPARATOR ",") socpeople
    FROM '.MAIN_DB_PREFIX.'projet_task pt
	LEFT JOIN '.MAIN_DB_PREFIX.'projet p
		ON p.rowid=pt.fk_projet
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_extrafields p2
		ON p2.fk_object=p.rowid
    LEFT JOIN '.MAIN_DB_PREFIX.'element_contact c
        ON c.element_id=pt.rowid AND c.fk_c_type_contact IN ('.implode(',', $fk_project_contacts).')
    WHERE 1
		'.($chantiers ?'AND (p2.fk_commande IS NOT NULL)' :'').'
		'.($hide ?'AND (p2.permanent IS NULL OR p2.permanent=0)' :'').'
		'.($hidenodate ?'AND (pt.dateo IS NOT NULL AND pt.datee IS NOT NULL)' :'').'
        AND (
			(
				(pt.dateo IS NOT NULL AND pt.dateo <= "'.$periode_fin_date.'")
				AND
				(
					(pt.datee IS NULL AND "'.$periode_debut_date.'" <= pt.dateo)
					OR   
					(pt.datee IS NOT NULL AND "'.$periode_debut_date.'" <= pt.datee)
				)
			)
			OR
			(
				(pt.datee IS NOT NULL AND "'.$periode_debut_date.'" <= pt.datee)
				AND
				(
					(pt.dateo IS NULL AND pt.datee <= "'.$periode_fin_date.'")
					OR   
					(pt.dateo IS NOT NULL AND pt.dateo <= "'.$periode_fin_date.'")
				)
			)
		)
    GROUP BY pt.rowid';
//echo '<pre>'.$sql.'</pre>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_object($q)) {
        //var_dump($r);
        $a = ($r->dateo < $periode_debut_date) ?$periode_debut_date :$r->dateo;
        $b = ($r->datee) ?($r->datee > $periode_fin_date ?$periode_fin_date :$r->datee) :$r->dateo;
        //var_dump($a, $b);
        $dto = Datetime::createFromFormat('Y-m-d H:i:s', $a);
        $d = $dto->format('Y-m-d');
        $dates[$d]['projects'][] = $r;
        while($d < $b) {
            $dto->modify('+1 days');
            $d = $dto->format('Y-m-d');
            $dates[$d]['tasks'][] = $r;
        }
    }
}

// Projets dont tâches dans ces dates + projets dans ces dates
$sql = 'SELECT DISTINCT p.rowid, p.dateo, p.datee, p.fk_soc, p.ref, p.title, p.description, GROUP_CONCAT(DISTINCT c.fk_socpeople SEPARATOR ",") socpeople
    FROM '.MAIN_DB_PREFIX.'projet p
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_extrafields p2
		ON p2.fk_object=p.rowid
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task pt
		ON pt.fk_projet=p.rowid
    LEFT JOIN '.MAIN_DB_PREFIX.'element_contact c
		ON c.element_id=p.rowid AND c.fk_c_type_contact IN ('.implode(',', $fk_project_contacts).')
    WHERE 1
		'.($chantiers ?'AND (p2.fk_commande IS NOT NULL)' :'').'
		'.($hide ?'AND (p2.permanent IS NULL OR p2.permanent=0)' :'').'
		'.($hidenodate ?'AND (p.dateo IS NOT NULL AND p.datee IS NOT NULL)' :'').'
		AND (
			(
				(p.dateo IS NOT NULL AND p.dateo <= "'.$periode_fin_date.'")
				AND
				(
					(p.datee IS NULL AND "'.$periode_debut_date.'" <= p.dateo)
					OR   
					(p.datee IS NOT NULL AND "'.$periode_debut_date.'" <= p.datee)
				)
			)
			OR
			(
				(p.datee IS NOT NULL AND "'.$periode_debut_date.'" <= p.datee)
				AND
				(
					(p.dateo IS NULL AND p.datee <= "'.$periode_fin_date.'")
					OR   
					(p.dateo IS NOT NULL AND p.dateo <= "'.$periode_fin_date.'")
				)
			)
			OR
			(
				(pt.dateo IS NOT NULL AND pt.dateo <= "'.$periode_fin_date.'")
				AND
				(
					(pt.datee IS NULL AND "'.$periode_debut_date.'" <= pt.dateo)
					OR   
					(pt.datee IS NOT NULL AND "'.$periode_debut_date.'" <= pt.datee)
				)
			)
			OR
			(
				(pt.datee IS NOT NULL AND "'.$periode_debut_date.'" <= pt.datee)
				AND
				(
					(pt.dateo IS NULL AND pt.datee <= "'.$periode_fin_date.'")
					OR   
					(pt.dateo IS NOT NULL AND pt.dateo <= "'.$periode_fin_date.'")
				)
			)
		)
    GROUP BY p.rowid';
//echo '<pre>'.$sql.'</pre>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_object($q)) {
		//var_dump($r);
		$a = (!$r->dateo || $r->dateo < $periode_debut_date) ?$periode_debut_date :$r->dateo;
		$b = (!$r->datee || $r->datee > $periode_fin_date) ?$periode_fin_date :$r->datee;
		if (strlen($a)==10)
			$a .= ' 00:00:00';
		if (strlen($b)==10)
			$b .= ' 00:00:00';
		//var_dump($a, $b);

		// On commence à $a
		$dto = Datetime::createFromFormat('Y-m-d H:i:s', $a);
		//var_dump($dto);
		$d = $dto->format('Y-m-d');
		//var_dump($d);
		$dates[$d]['projects'][] = $r;
		// Jusqu'à $b
		while($d < $b) {
			$dto->modify('+1 days');
			$d = $dto->format('Y-m-d');
			$dates[$d]['projects'][$r->rowid] = $r;
		}
	}
}

// AFFICHAGE

echo '<table border="1">';
foreach($weeks as $w=>$week) {
	echo '<tr>';
	//var_dump($week);
	foreach($week as $i=>$j) {
		echo '<td>';
		//echo '<p>'.$i.'</p>';
		echo '<p>'.$j['date'].'</p>';
		if(!empty($j['projects'])) {
			echo '<div>';
			echo '<p>Projets :</p>';
			foreach($j['projects'] as $proj) {
				echo '<p><a href="/projet/card.php?id='.$proj->rowid.'">'.$proj->title.'</a></p>';
			}
			echo '</div>';
		}
		if(!empty($j['tasks'])) {
			echo '<div>';
			echo '<p>Tâches :</p>';
			foreach($j['tasks'] as $task) {
				echo '<p><a href="/projet/task/card.php?id='.$task->rowid.'&withproject=1">'.$task->title.'</a></p>';
			}
			echo '</div>';
		}
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>';
