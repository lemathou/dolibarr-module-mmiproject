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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';

dol_include_once('/mmiproject/lib/mmiproject.lib.php');

setlocale(LC_TIME, "fr_FR.utf8");
date_default_timezone_set('Europe/Paris');

// @todo rendre administrable
$soliday = !empty($conf->global->MMIPROJECT_SOLIDAY) ?$conf->global->MMIPROJECT_SOLIDAY :'2022-11-01';
$solidtime = strtotime($soliday);
$solidweeknum = date('W', $solitime);

$time = time();

$date = GETPOST('date');
if (empty($date))
	$date = date('m/Y');
list($month, $year) = explode('/', $date);
$year_month = $year.'-'.$month;

$task_fk_user = GETPOST('task_fk_user');
$task_user = new User($db);
if (!empty($task_fk_user)) {
	$task_user->fetch($task_fk_user);
}

if (! $task_user->id) {
	$task_fk_user = $user->id;
	$task_user = $user;
}

// Options d'affichage
$weeks_aff = GETPOST('weeks_aff');
$month_aff = GETPOST('month_aff');
$more_aff = GETPOST('more_aff');
$cp_aff = GETPOST('cp_aff');
$holidays_aff = GETPOST('holidays_aff');

/*
 * View
 */

//$form = new Form($db);
//$formfile = new FormFile($db);

llxHeader("", $langs->trans("MMIProjectAreaTimeSheet"));
echo '<link rel="stylesheet" href="css/mmiprojects.css" />';

print load_fiche_titre($langs->trans("MMIProjectAreaTimeSheet"), '', 'mmiproject@mmiproject');


//var_dump($user);

if ($user->rights->mmiproject->time->admin) {
	echo '<form method="GET" action="time_monthly.php">';
	$sql = 'SELECT rowid, login, CONCAT(`firstname`, " ", `lastname`) label
		FROM '.MAIN_DB_PREFIX.'user';
	//echo $sql;
	$q = $db->query($sql);
	//var_dump($q); var_dump($db);
	echo 'Collaborateur : <select name="task_fk_user"><option value=""></option>';
	if ($q) {
		while($r=$db->fetch_array($q)) {
			if ($task_fk_user==$r['rowid'])
				$user_name = $r['label'];
			echo '<option value="'.$r['rowid'].'"'.($task_fk_user==$r['rowid'] ?' selected' :'').'>'.$r['label'].'</option>';
		}
	}
	echo '</select>';

	$sql = 'SELECT DISTINCT YEAR(ptt.task_date) `year`, DATE_FORMAT(ptt.task_date, "%m") `month`
		FROM '.MAIN_DB_PREFIX.'projet_task_time ptt';
	//echo $sql;
	$q = $db->query($sql);
	//var_dump($q); var_dump($db);
	echo 'Mois : <select name="date">';
	if ($q) {
		while($r=$db->fetch_array($q)) {
			$r_date = $r['month'].'/'.$r['year'];
			echo '<option value="'.$r_date.'"'.($date==$r_date ?' selected' :'').'>'.$r_date.'</option>';
		}
	}
	echo '</select>';
	echo '<input type="checkbox" name="holidays_aff" value="1"'.(!empty($holidays_aff) ?' checked' :'').' /> Afficher fériés';
	echo '<input type="checkbox" name="cp_aff" value="1"'.(!empty($cp_aff) ?' checked' :'').' /> Afficher CP';
	echo '<input type="checkbox" name="weeks_aff" value="1"'.(!empty($weeks_aff) ?' checked' :'').' /> Afficher cumul semaines';
	echo '<input type="checkbox" name="month_aff" value="1"'.(!empty($month_aff) ?' checked' :'').' /> Afficher cumul mois';
	echo '<input type="checkbox" name="more_aff" value="1"'.(!empty($more_aff) ?' checked' :'').' /> Afficher +';
	echo '<input type="submit" value="Afficher" />';
	echo '<hr />';
	echo '</form>';
}
elseif ($user->rights->mmiproject->time->user) {
	echo '<form method="GET" action="time_monthly.php">';
	echo 'Collaborateur : <input type="hidden" name="task_fk_user" value="'.$user->id.'" />'.$user->firstname.' '.$user->lastname;

	$sql = 'SELECT DISTINCT YEAR(ptt.task_date) `year`, DATE_FORMAT(ptt.task_date, "%m") `month`
		FROM '.MAIN_DB_PREFIX.'projet_task_time ptt
		WHERE ptt.fk_user='.$user->id;
	//echo $sql;
	$q = $db->query($sql);
	//var_dump($q); var_dump($db);
	echo ' Période : <select name="date">';
	if ($q) {
		while($r=$db->fetch_array($q)) {
			$r_date = $r['month'].'/'.$r['year'];
			echo '<option value="'.$r_date.'"'.($date==$r_date ?' selected' :'').'>'.$r_date.'</option>';
		}
	}
	echo '</select>';
	echo '<input type="checkbox" name="month_aff" value="1"'.(!empty($month_aff) ?' checked' :'').' /> Afficher cumul mois';
	echo '<input type="submit" value="Afficher" />';
	echo '<hr />';
	echo '</form>';
}
else {
	echo '<p>Vous n\'avez pas la permission...</p>';
}

//var_dump($date); var_dump($year); var_dump($month);

$cp_mois_debut = 5; // @todo paramètre avril BTP

$periode_year_debut = ($month<$cp_mois_debut) ?$year-1 :$year;
$periode_mois_debut = $cp_mois_debut;
$periode_debut = $periode_year_debut.'-0'.$periode_mois_debut;
$periode_debut_ts = strtotime($periode_debut.'-01');
$periode_debut_weeknum = date('W', $periode_debut_ts);

$periode_year_fin = $periode_year_debut+1;
$periode_mois_fin = $periode_mois_debut-1;
$periode_fin = $periode_year_fin.'-0'.$periode_mois_fin;
$periode_fin_nbdays = cal_days_in_month(CAL_GREGORIAN, $periode_mois_fin, $periode_year_fin);
$periode_fin_date = $periode_fin.'-'.$periode_fin_nbdays;
$periode_fin_ts = strtotime($periode_fin.'-'.$periode_fin_nbdays);
$periode_fin_weeknum = date('W', $periode_fin_ts);

//var_dump($task_user);

// Contrats de travail
$sql = 'SELECT e.weeklyhours, e.dateemployment, e.dateemploymentend
	FROM '.MAIN_DB_PREFIX.'user_employment e
	WHERE e.fk_user='.$task_fk_user.'
	ORDER BY e.dateemployment';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
$employs = [];
if ($q) {
	while($r=$db->fetch_array($q)) {
		$employs[$r['dateemployment']] = [
			'begin_date' => $r['dateemployment'],
			'end_date' => $r['dateemploymentend'],
			'weekly' => $r['weeklyhours'],
			'daily' => $r['weeklyhours']/5,
		];
	}
}
//var_dump($employs); die();

// Valeurs par défaut
// Nb par semaine
$weekly = $task_user->weeklyhours;
// Nb h travaillées par jour
$daily = $weekly/5;
// Seuil Heures +25%
$weekly1 = 35;
// Seuil Heures +50%
$weekly2 = 43;
$weekly_max = 48;

// CP théoriques, en fait en nb de jours
// @todo mettre en param, 5 ou 6
$cp_j_ouvr = 6;
$cp_j_total = 5*$cp_j_ouvr; // 5 semaines
$cp_total = $cp_j_total*$daily;

// Nb par mois
$monthly_max = 200;

// Fériés sur la période
$holidays_list = [];
$holidays = [];
$holidays1 = getHolidays2($periode_year_debut);
foreach($holidays1 as $day)
	if (substr($day, 5, 7)>=5) {
		$holidays_list[$day] = ['day' => $day, 'daynumofweek'=>date('w', strtotime($day))];
		$holidays[]  = $day;
	}
$holidays2 = getHolidays2($periode_year_fin);
foreach($holidays2 as $day)
	if (substr($day, 5, 7)<=4) {
		$holidays_list[$day] = ['day' => $day, 'daynumofweek'=>date('w', strtotime($day))];
		$holidays[]  = $day;
	}
//var_dump($holidays);

$model = [
	'duration' => 0,
	'deplacement_duration' => 0,
	'ferie_duration' => 0,
	// dont
	'ferie_trav_duration' => 0,
	'seuil1_duration' => 0,
	'seuil2_duration' => 0,
	// formation
	'arret_formation' => 0,
	// congés
	'arret_cp' => 0,
	'arret_maladie' => 0,
	'arret_rtt' => 0,
	'arret_autre' => 0,
	// Cumul arrets
	'arret_justifie' => 0,
	// CP
	'cp_gagne' => 0,
	'cp_cumul_gagne' => 0,
	'cp_cumul_pris' => 0,
];

$total = $model;


// Cumul Semaine

$cumul_week = [];

for($i=$periode_debut_weeknum;$i<=53;$i++) {
	$dates = getStartAndEndDate($i, $periode_year_debut);
	if ($dates['week_start']>=$periode_year_fin)
		break;
	$cumul_week[$periode_year_debut.'-'.$i] = $model;
	$cumul_week[$periode_year_debut.'-'.$i]['year'] = $periode_year_debut;
	$cumul_week[$periode_year_debut.'-'.$i]['weeknum'] = $i;
	$cumul_week[$periode_year_debut.'-'.$i]['dates'] = $dates;
}
for($i=1;$i<=$periode_fin_weeknum;$i++) {
	$cumul_week[$periode_year_fin.'-'.$i] = $model;
	$cumul_week[$periode_year_fin.'-'.$i]['year'] = $periode_year_fin;
	$cumul_week[$periode_year_fin.'-'.$i]['weeknum'] = $i;
	$cumul_week[$periode_year_fin.'-'.$i]['dates'] = getStartAndEndDate($i, $periode_year_fin);
}

if (!empty($employs)) foreach($employs as $r) {
	$employ = $r;
	break;
}
else {
	$employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
//var_dump($employ); die();
foreach($cumul_week as &$r) {
	$r['nbferies'] = 0;
	$r['nbferiesdim'] = 0;
	$r['nbworkdays'] = 0;
	$r['weekly'] = 0;
	$d = 0;
	for ($i=0;$i<=6;$i++) {
		$ldate = strtotime($r['dates']['week_start'])+$i*86400;
		$daynumofweek = $i;
		$ddate = date('Y-m-d', $ldate);
		$isferie = in_array($ddate, $holidays);
		if ($isferie && $ddate!=$soliday)
			$r['nbferies']++;
		if ($isferie && $daynumofweek==0)
			$r['nbferiesdim']++;
		if (($isferie && $ddate!=$soliday) || in_array($daynumofweek, [0,6]))
			continue;
		$r['nbworkdays']++;
		// Change contract
		//var_dump($employ['end_date'], $ddate, $employ['end_date'] <= $ddate); echo '<br />';
		if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
			$employ_ok = false;
			foreach($employs as $emp) {
				if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
					$employ = $emp;
					$employ_ok = true;
					break;
				}
			}
			if (!$employ_ok)
				$employ = ['weekly'=>$weekly, 'daily'=>$daily];
		}
		$r['weekly'] += $employ['daily'];
	}
	//var_dump($r);
}
unset($r);
//var_dump($cumul_week); die();


// Cumul mois

$cumul_mois = [];

for($i=$periode_mois_debut;$i<=12;$i++) {
	$j = $periode_year_debut.'-'.($i<10 ?'0'.$i :$i);
	$cumul_mois[$j] = $model;
	$cumul_mois[$j]['year'] = $periode_year_debut;
	$cumul_mois[$j]['month'] = $i;
	$cumul_mois[$j]['date'] = $j;
}
for($i=1;$i<=$periode_mois_fin;$i++) {
	$j = $periode_year_fin.'-0'.$i;
	$cumul_mois[$j] = $model;
	$cumul_mois[$j]['year'] = $periode_year_fin;
	$cumul_mois[$j]['month'] = $i;
	$cumul_mois[$j]['date'] = $j;
}
//var_dump($cumul_mois);


// Jours par mois

if (!empty($employs)) foreach($employs as $r) {
	$employ = $r;
	break;
}
else {
	$employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
foreach($cumul_mois as &$r) {
	$r['nbdays'] = cal_days_in_month(CAL_GREGORIAN, $r['month'], $r['year']);
	$r['nbferies'] = 0;
	$r['nbferiesdim'] = 0;
	$d = 0;
	for ($i=1;$i<=$r['nbdays'];$i++) {
		$ldate = mktime(0, 0, 0, $r['month'], $i, $r['year']);
		$daynumofweek = date('w', $ldate);
		$ddate = date('Y-m-d', $ldate);
		$isferie = in_array($ddate, $holidays);
		if ($isferie && $ddate!=$soliday)
			$r['nbferies']++;
		if ($isferie && $daynumofweek==0)
			$r['nbferiesdim']++;
		// on ne comptabilise pas
		if (($isferie && $ddate!=$soliday) || in_array($daynumofweek, [0,6]))
			continue;
		$d++;
		// Change contract
		if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
			$employ_ok = false;
			foreach($employs as $emp) {
				if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
					$employ = $emp;
					$employ_ok = true;
					$r['employ'] = $employ;
					break;
				}
			}
			if (!$employ_ok) {
				$employ = ['weekly'=>$weekly, 'daily'=>$daily];
				$r['employ'] = $employ;
			}	
		}
		$r['monthly'] += $employ['daily'];

		// CP pris/heures à faire en fct du contrat...
		// Donc compter par jour !!
	}
	// Dernier contrat valide ce mois
	$r['employ'] = $employ;
	$r['daily'] = $r['employ']['daily'];
	// Workday / Workhours
	$r['nbworkdays'] = $d;
	// CP
	$r['cp_j_gagne'] = 2.5;
	$r['cp_gagne'] = 2.5*$employ['daily'];
	//var_dump($r['date'], $year_month);
	if ($r['date'] <= $year_month) {
		$total['cp_j_gagne'] += $r['cp_j_gagne'];
		$total['cp_gagne'] += $r['cp_j_gagne']*$employ['daily'];
	}
	$r['cp_j_cumul_gagne'] = $total['cp_j_gagne'];
	$r['cp_cumul_gagne'] = $total['cp_j_gagne']*$employ['daily'];
}
//var_dump($total);
unset($r);
//var_dump($cumul_mois); die();


// Jours du mois affiché

$month_number = cal_days_in_month(CAL_GREGORIAN, $month, $year); // 31
$firstday = $year_month .'-01';
$lastday = $year_month .'-'.$month_number;
$month_workdays = 0;

// Jours de Congés & co du mois
if (!empty($employs)) foreach($employs as $r) {
	$employ = $r;
	break;
}
else {
	$employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
$l = [];
for ($i=1;$i<=$month_number;$i++) {
	$ldate = mktime(0, 0, 0, $month, $i, $year);
	$ddate = date('Y-m-d', $ldate);
	$daynumofweek = date('w', $ldate);
	$isferie = in_array($ddate, $holidays) || $daynumofweek==0;
	if (!($isferie && $ddate!=$soliday) && !in_array($daynumofweek, [0,6]))
		$month_workdays++;
	// Change contractvim .
	if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
		$employ_ok = false;
		foreach($employs as $emp) {
			if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
				$employ = $emp;
				$employ_ok = true;
				break;
			}
		}
		if (!$employ_ok)
			$employ = ['weekly'=>$weekly, 'daily'=>$daily];
	}
	$l[$ddate] = array_merge([
		'ldate' => $ldate,
		'dayofweek' => strftime('%A', $ldate),
		'daynumofweek' => $daynumofweek,
		'date' => date('d/m/Y', $ldate),
		'weeknum' => date('W', $ldate),
		'isferie' => $isferie,
		//'year' => date('Y', $ldate),
		'timespent' => [], // Optionnal detailled list
		'daily' => $employ['daily'],
		],
		$model);
}

// Assignation fériés ouvrés

if (!empty($employs)) foreach($employs as $r) {
        $employ = $r;
        break;
}
else {
        $employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
foreach($holidays as $ddate) {
	$ldate = strtotime($ddate);
	$weeknum = date('W', $ldate);
	$daynumofweek = date('w', $ldate);
	$lyearmonth = substr($ddate, 0, 7);
	$lyear = substr($ddate, 0, 4);
	$lmonth = substr($ddate, 5, 2);
	// Samedi/Dimanche ou journée de solidarité => pas férié payé
	if (in_array($daynumofweek, [0, 6]) || $ddate==$soliday)
		continue;
        // Change contract
        if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
                $employ_ok = false;
                foreach($employs as $emp) {
                        if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
                                $employ = $emp;
                                $employ_ok = true;
                                break;
                        }
                }
                if (!$employ_ok)
                        $employ = ['weekly'=>$weekly, 'daily'=>$daily];
        }
	if ($lmonth==$month)
		$l[$ddate]['ferie_duration'] = $employ['daily'];
	$cumul_week[$lyear.'-'.$weeknum]['ferie_duration'] += $employ['daily'];
	$cumul_mois[$lyearmonth]['ferie_duration'] += $employ['daily'];
}
//die();

$task_type = [
	1 => 'Déplacement',
];

// Jours Travaillés du mois

$sql = 'SELECT ptt.task_date, SUM(ptt.task_duration)/3600 duration, p2.task_type 
	FROM '.MAIN_DB_PREFIX.'projet_task_time ptt
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task pt ON pt.rowid=ptt.fk_task
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pt2 ON pt2.fk_object=pt.rowid
	LEFT JOIN '.MAIN_DB_PREFIX.'commandedet cd ON cd.rowid=pt2.fk_commandedet
	LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields p2 ON p2.fk_object=cd.fk_product
	WHERE ptt.fk_user='.$task_fk_user.'
		AND (YEAR(ptt.task_date)=\''.$db->escape($year).'\' AND MONTH(ptt.task_date)=\''.$db->escape($month).'\')
	GROUP BY ptt.task_date, p2.task_type';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		//var_dump($r);
		if ($r['task_type']==1) {
			$l[$r['task_date']]['deplacement_duration'] += $r['duration'];
			if ($l[$r['task_date']]['isferie'])
				$l[$r['task_date']]['ferie_trav_duration'] = +$r['duration'];
		}
		else {
			$l[$r['task_date']]['duration'] += $r['duration'];
			if ($l[$r['task_date']]['isferie'])
				$l[$r['task_date']]['ferie_trav_duration'] = +$r['duration'];
		}
	}
}
//var_dump($l);


// Jours de Congés & co du mois
if (!empty($employs)) foreach($employs as $r) {
	$employ = $r;
	break;
}
else {
	$employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
$sql = 'SELECT h.date_debut, h.date_fin, h.fk_type
FROM '.MAIN_DB_PREFIX.'holiday h
WHERE h.fk_user='.$task_fk_user.' AND h.statut = '.Holiday::STATUS_APPROVED.'
	AND (
		("'.$firstday.'" <= h.date_debut AND h.date_debut <= "'.$lastday.'" )
		OR ("'.$firstday.'" <= h.date_fin AND h.date_fin <= "'.$lastday.'" )
	)';
//echo '<pre>'.$sql.'</pre>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		//var_dump($r);
		// maladie
		if ($r['fk_type']==1)
			$type = 'maladie';
		// Autre
		if ($r['fk_type']==2)
			$type = 'autre';
		// rtt
		if ($r['fk_type']==4)
			$type = 'rtt';
		// payé
		if ($r['fk_type']==5)
			$type = 'cp';
		// formation
		if ($r['fk_type']==6)
			$type = 'formation';
		// Maternité/Paternité
		if ($r['code']=='LEAVE_MATER')
			$type = 'mater';
		
		$first = (substr($r['date_debut'], 0, 7)==$year_month) ?(int)substr($r['date_debut'], -2, 2) :1;
		$last = (substr($r['date_fin'], 0, 7)==$year_month) ?(int)substr($r['date_fin'], -2, 2) :(int)$month_number;

		// Ajout pour chaque jour comptabilisé (ni férié, samedi, dimanche)
		for ($i=$first; $i<=$last; $i++) {
			$ddate = $year_month.'-'.($i<10 ?'0'.$i :$i);
			$ldate = strtotime($ddate);
			$daynumofweek = date('w', $ldate);
			// Change contract
			if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
				$employ_ok = false;
				foreach($employs as $emp) {
					if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
						$employ = $emp;
						$employ_ok = true;
						break;
					}
				}
				if (!$employ_ok)
					$employ = ['weekly'=>$weekly, 'daily'=>$daily];
			}
			// Jour férié, samedi, dimanche => pas comptabilisé
			if (in_array($ddate, $holidays) || in_array($daynumofweek, [0, 6]))
				continue;
			$l[$ddate]['arret_'.$type] = $employ['daily'];
		}
	}
}


// Cumuls travaillé par mois et semaines

$sql = 'SELECT SUBSTRING(ptt.task_date, 1, 7) `date`, SUM(ptt.task_duration)/3600 duration, p2.task_type 
	FROM '.MAIN_DB_PREFIX.'projet_task_time ptt
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task pt ON pt.rowid=ptt.fk_task
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pt2 ON pt2.fk_object=pt.rowid
	LEFT JOIN '.MAIN_DB_PREFIX.'commandedet cd ON cd.rowid=pt2.fk_commandedet
	LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields p2 ON p2.fk_object=cd.fk_product
	WHERE ptt.fk_user='.$task_fk_user.'
		AND (\''.$db->escape($periode_debut).'-00\' <= ptt.task_date AND ptt.task_date <= \''.$db->escape($periode_fin_date).'\')
	GROUP BY SUBSTRING(ptt.task_date, 1, 7), p2.task_type';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		//var_dump($r);
		if ($r['task_type']==1)
			$cumul_mois[$r['date']]['deplacement_duration'] += $r['duration'];
		else
			$cumul_mois[$r['date']]['duration'] += $r['duration'];
	}
}

$sql = 'SELECT YEAR(ptt.task_date) `year`, WEEK(ptt.task_date, 1) `date`, SUM(ptt.task_duration)/3600 duration, p2.task_type 
	FROM '.MAIN_DB_PREFIX.'projet_task_time ptt
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task pt ON pt.rowid=ptt.fk_task
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pt2 ON pt2.fk_object=pt.rowid
	LEFT JOIN '.MAIN_DB_PREFIX.'commandedet cd ON cd.rowid=pt2.fk_commandedet
	LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields p2 ON p2.fk_object=cd.fk_product
	WHERE ptt.fk_user='.$task_fk_user.'
		AND (\''.$db->escape($periode_debut).'-00\' <= ptt.task_date AND ptt.task_date <= \''.$db->escape($periode_fin_date).'\')
	GROUP BY WEEK(ptt.task_date, 1), p2.task_type';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		//var_dump($r);
		if ($r['task_type']==1)
			$cumul_week[$r['year'].'-'.$r['date']]['deplacement_duration'] += $r['duration'];
		else
			$cumul_week[$r['year'].'-'.$r['date']]['duration'] += $r['duration'];
	}
}

// Cumul congés par mois et semaines

if (!empty($employs)) foreach($employs as $r) {
	$employ = $r;
	break;
}
else {
	$employ = ['weekly'=>$weekly, 'daily'=>$daily];
}
$sql = 'SELECT h.date_debut, h.date_fin, h.fk_type, ht.code
FROM '.MAIN_DB_PREFIX.'holiday h
INNER JOIN '.MAIN_DB_PREFIX.'c_holiday_types ht ON ht.rowid=h.fk_type
WHERE h.fk_user='.$task_fk_user.' AND h.statut = '.Holiday::STATUS_APPROVED.'
	AND (
		("'.$periode_debut.'-00" <= h.date_debut AND h.date_debut <= "'.$periode_fin.'-00" )
		OR ("'.$periode_debut.'-00" <= h.date_fin AND h.date_fin <= "'.$periode_fin.'-00" )
	)';
//echo '<pre>'.$sql.'</pre>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		//var_dump($r);
		// maladie
		if ($r['code']=='LEAVE_SICK')
			$type = 'maladie';
		// Autre
		if ($r['code']=='LEAVE_OTHER')
			$type = 'autre';
		// rtt
		if ($r['code']=='LEAVE_RTT_FR')
			$type = 'rtt';
		// payé
		if ($r['code']=='LEAVE_PAID_FR')
			$type = 'cp';
		// formation
		if ($r['code']=='LEAVE_LEARN')
			$type = 'formation';
		// Maternité/Paternité
		if ($r['code']=='LEAVE_MATER')
			$type = 'mater';
		
		$y1 = substr($r['date_debut'], 0, 4);
		$m1 = (int)substr($r['date_debut'], 5, 2);
		$d1 = (int)substr($r['date_debut'], 8, 2);
		if ($y1==$periode_year_debut && $m1<5) {
			$r['date_debut'] = $periode_year_debut.'-05-01';
			$m1 = 5;
			$d1 = 1;
		}
		
		$y2 = substr($r['date_fin'], 0, 4);
		$m2 = (int)substr($r['date_fin'], 5, 2);
		$d2 = (int)substr($r['date_fin'], 8, 2);
		if ($y2==$periode_year_fin && $m2>4) {
			$r['date_fin'] = $periode_year_fin.'-04-30';
			$m2 = 4;
			$d2 = 30;
		}

		$days = [];
		for($y=$y1;$y<=$y2;$y++) {
			if ($y==$y1) {
				for ($m=(int)$m1; $m<=($y2!=$y1 ?'12' :$m2); $m++) {
					$dtot = cal_days_in_month(CAL_GREGORIAN, $m, $y); // 31
					for ($d=($m==$m1 ?$d1 :1); $d<=($y1==$y2 && $m==$m2 ?$d2 :$dtot); $d++) {
						$day = $y.'-'.($m<10 ?'0'.$m :$m).'-'.($d<10 ?'0'.$d :$d);
						$days[] = $day;
						//var_dump($day);
					}
				}
			}
			else {
				for ($m=1; $m<=$m2; $m++) {
					$dtot = cal_days_in_month(CAL_GREGORIAN, $m, $y); // 31
					for ($d=1; $d<=($m==$m2 ?$d2 :$dtot); $d++) {
						$day = $y.'-'.($m<10 ?'0'.$m :$m).'-'.($d<10 ?'0'.$d :$d);
						$days[] = $day;
						//var_dump($day);
					}
				}
			}
		}

		foreach($days as $day) {
			$ldate = strtotime($day);
			$daynumofweek = date('w', $ldate);
			$weeknum = date('W', $ldate);
			$ddate = $day;
			// Change contract
			if (!empty($employ['end_date']) && $employ['end_date'] <= $ddate) {
				$employ_ok = false;
				foreach($employs as $emp) {
					if (empty($emp['end_date']) || $ddate < $emp['end_date']) {
						$employ = $emp;
						$employ_ok = true;
						break;
					}
				}
				if (!$employ_ok)
					$employ = ['weekly'=>$weekly, 'daily'=>$daily];
			}
			// Samedi, Dimanche, Férie => on compte pas
			if (in_array($day, $holidays) || in_array($daynumofweek, [0, 6]))
				continue;
			$cumul_mois[substr($day, 0, 7)]['arret_'.$type] += $employ['daily'];
			$cumul_mois[substr($day, 0, 7)]['arret_'.$type.'_j'] += 1;
			$cumul_week[substr($day, 0, 5).$weeknum]['arret_'.$type] += $employ['daily'];
			$cumul_week[substr($day, 0, 5).$weeknum]['arret_'.$type.'_j'] += 1;
		}
	}
}


// Calculs cumulés Hebdo

$cdelta = 0;
$ctheo = 0;
$c = 0;
foreach($cumul_week as &$r) {
	$r['effectif'] = $r['duration'] + $r['deplacement_duration'] + $r['arret_formation'];
	$r['comptabilise'] = $r['effectif'] + $r['arret_cp'] + $r['arret_maladie'] + $r['arret_autre'];
	$r['delta'] = $r['comptabilise'] - $r['weekly'];
	$r['paye'] = $r['effectif'] + $r['ferie_duration'] - ($solidweeknum==$r['weeknum'] ?$l[$soliday]['daily'] :0);
	
	$c += $r['comptabilise'];
	$ctheo += $r['weekly'];
	$cdelta += $r['delta'];
	$r['cumul'] = $c;
	$r['cumul_theo'] = $ctheo;
	$r['cumul_delta'] = $cdelta;
}
unset($r);
//var_dump($cumul_week);


// Calculs cumulés Mois

$cdelta = 0;
$ctheo = 0;
$c = 0;
foreach($cumul_mois as &$r) {
	$r['effectif'] = $r['duration'] + $r['deplacement_duration'] + $r['arret_formation'];
	$r['comptabilise'] = $r['effectif'] + $r['arret_cp'] + $r['arret_maladie'] + $r['arret_autre'];
	$r['delta'] = $r['comptabilise'] - $r['monthly'];
	$r['paye'] = $r['effectif'] + $r['ferie_duration'] - (substr($soliday, 0, 7)==$r['date'] ?$l[$soliday]['daily'] :0);

	$c += $r['comptabilise'];
	$ctheo += $r['monthly'];
	$cdelta += $r['delta'];
	$r['cumul'] = $c;
	$r['cumul_theo'] = $ctheo;
	$r['cumul_delta'] = $cdelta;
}
unset($r);
//var_dump($cumul_mois);

$monthcur = $cumul_mois[$year_month];
//var_dump($monthcur);
$monthhours = $monthcur['monthly'];
$monthly = $daily*$month_workdays; // @todo recalculer bien !!

// Affichage

echo '<p id="user_name">'.$user_name.'</p>';
echo '<p>Période : '.date_reverse($periode_debut).' à '.date_reverse($periode_fin).' / Mois de '.strftime("%B", strtotime($month.'-01')).' ('.date_reverse($year_month).') : '.$month_number.' jours dans le mois, '.$month_workdays.' travaillables</p>';
echo '<p>'.$monthcur['daily'].'h/j sur contrat en vigueur, '.$monthcur['monthly'].'h à travailler dans le mois</p>';

echo '<div class="div-table-responsive-no-min"><table class="month">';
echo '<caption>Travail du mois</caption>';
echo '<thead>';
	echo '<tr class="liste_titre">';
	echo '<th>Jour</th>';
	echo '<th>Date</th>';
	$colspantot = $colspan + 3;
	echo '<th width="60">H. trav.</th>';
	echo '<th width="60">H. dépl.</th>';
	echo '<th width="60">H. dépl. hors trav.</th>';
	echo '<th width="60">H. payé férié</th>';
	echo '<th width="60">H. trav. dim./férie</th>';
	echo '<th width="60">H. >'.$weekly1.'h</th>';
	echo '<th width="60">H. >'.$weekly2.'h</th>';
	echo '<th width="60">H. CP</th>';
	echo '<th width="60">Abs. justif.</th>';
	echo '<th width="60">Forma.</th>';
	//echo '<th width="60">Partiel</th>';
	echo '</tr>';
echo '</thead>';
echo '<tbody>';

$week = NULL;
$weeknum = 0;
$week_duration = 0;
$date_before = '';
foreach($l as $ddate=>$row) {
	$ldate = $row['ldate'];
	if (empty($week) || (!empty($ldate_before) && $ldate_before != $ldate && $row['daynumofweek']==1)) {
		$week_dates = getStartAndEndDate($row['weeknum'], $year); // @todo déjà fait, récupérer l'info
		echo '<tr> <td class="separator" colspan="12"></td></tr>';
		echo '<tr> <td>Semaine&nbsp;'.$row['weeknum'].'</th> <th colspan="11">'.date_reverse($week_dates['week_start']).' au '.date_reverse($week_dates['week_end']).'</th></tr>';
		$week = $model;
	}
	$row['arret_justifie'] = $row['arret_autre']+$row['arret_maladie']+$row['arret_rtt'];

	// Cumul semaine
	foreach(array_keys($model) as $key) if (!in_array($key, ['seuil1_duration', 'seuil2_duration']))
		$week[$key] += $row[$key];

	echo '<tr class="'.($row['isferie'] ?'holyday' :'').'">';
	echo '<td>'.$row['dayofweek'].'</td>';
	echo '<td>'.$row['date'].'</td>';
	echo '<td>'.duration_aff($row['duration']).'</td>';
	echo '<td>'.duration_aff($row['deplacement_duration']).'</td>';
	echo '<td></td>';
	echo '<td>'.duration_aff($row['ferie_duration']).'</td>';
	echo '<td>'.duration_aff($row['ferie_trav_duration']).'</td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td>'.($row['arret_cp'] ?$row['arret_cp'] :'').'</td>';
	echo '<td>'.($row['arret_justifie'] ?$row['arret_justifie'] :'').'</td>';
	echo '<td>'.($row['arret_formation'] ?$row['arret_formation'] :'').'</td>';
	echo '</tr>';

	// Récap semaine
	if ($row['daynumofweek']==0 || $lastday==$ddate) {
		$week['seuil2_duration'] = max(0, $week['duration']+$week['deplacement_duration']-$weekly2);
		$week['seuil1_duration'] = max(0, $week['duration']+$week['deplacement_duration']-$weekly1-$week['seuil2_duration']);

		echo '<tr>';
		echo '<td><b>SSTOTAL</b></td>';
		echo '<td colspan="'.$colspan.'"></td>';
		echo '<td>'.duration_aff($week['duration']).'</td>';
		echo '<td>'.duration_aff($week['deplacement_duration']).'</td>';
		echo '<td></td>';
		echo '<td>'.duration_aff($week['ferie_duration']).'</td>';
		echo '<td>'.duration_aff($week['ferie_trav_duration']).'</td>';
		echo '<td>'.duration_aff($week['seuil1_duration']).'</td>';
		echo '<td>'.duration_aff($week['seuil2_duration']).'</td>';
		echo '<td>'.duration_aff($week['arret_cp']).'</td>';
		echo '<td>'.duration_aff($week['arret_justifie']).'</td>';
		echo '<td>'.duration_aff($week['arret_formation']).'</td>';
		echo '</tr>';
		// Cumul mois
		foreach(array_keys($model) as $key)
			$total[$key] += $week[$key];
	}

	$ldate_before = $ldate;
}
	echo '<tr> <td class="separator" colspan="12"></td></tr>';
	echo '<tr>';
	echo '<th>TOTAL</th>';
	echo '<th></th>';
	echo '<td>'.duration_aff($total['duration']).'</td>';
	echo '<td>'.duration_aff($total['deplacement_duration']).'</td>';
	echo '<td></td>';
	echo '<td>'.duration_aff($total['ferie_duration']).'</td>';
	echo '<td>'.duration_aff($total['ferie_trav_duration']).'</td>';
	echo '<td>'.duration_aff($total['seuil1_duration']).'</td>';
	echo '<td>'.duration_aff($total['seuil2_duration']).'</td>';
	echo '<td>'.duration_aff($total['arret_cp']).'</td>';
	echo '<td>'.duration_aff($total['arret_justifie']).'</td>';
	echo '<td>'.duration_aff($total['arret_formation']).'</td>';
	echo '</tr>';
echo '</tbody>';
echo '</table></div>';

$total['effectif'] = $total['duration'] + $total['deplacement_duration'] + $total['arret_formation'];
$total['comptabilise'] = $total['effectif'] + $total['arret_justifie'];
$total['diff'] = $total['comptabilise'] - $cumul_mois[$year_month]['monthly'];
$total['paye'] = $total['effectif'] + $total['ferie_duration'] - (substr($soliday, 0, 7)==$year_month ?$l[$soliday]['daily'] :0);

$cumul = [
	'total_theorique' => $cumul_mois[$periode_fin]['cumul_theo'],
	'theorique' => $cumul_mois[$year_month]['cumul_theo'],
	'comptabilise' => $cumul_mois[$year_month]['cumul'],
	'delta' => $cumul_mois[$year_month]['cumul_delta'],
];

$cp = [
	'total_theorique' => $cp_total,
	'gagnes' => $total['cp_gagne'],
	'pris' => $total['arret_cp'],
];

echo '<div class="total"><table border="1">';
echo '<caption>Cumul / Synthèse</caption>';
echo '<thead>';
echo '<tr>';
echo '<th>Total "effectif":<br /><i>travail+déplacement+formation</i></th>';
echo '<td>'.duration_aff($monthcur['effectif']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Heures ferié payé<br />(hors journée solidarité)</th>';
echo '<td>'.duration_aff($monthcur['ferie_duration']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Total absences justifiées à déduire<br /><i>RTT+maladie+CP+autre</i></th>';
echo '<td>'.duration_aff($monthcur['arret_justifie']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Total "comptabilisé"<br /><i>Effectif+Absences</i></th>';
echo '<td>'.duration_aff($monthcur['comptabilise']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Théorique:<br /><i>Heures à faire dans le mois</i></th>';
echo '<td>'.duration_aff($monthcur['monthly']).'</td>';
echo '</tr>';
echo '<tr style="color: '.($monthcur['delta']>=0 ?'green' :'red').';">';
echo '<th>Delta<br /><i>Différence Théorique-Comptabilisé</i></th>';
echo '<td>'.($monthcur['delta']>0 ?'+' :'').duration_aff($monthcur['delta']).'</td>';
echo '</tr>';

if (substr($soliday, 0, 7)==$year_month || isset($_GET['more_aff'])) {
echo '<tr>';
echo '<td colspan="2"><hr /></td>';
echo '</tr>';
}
if (substr($soliday, 0, 7)==$year_month) {
echo '<tr>';
echo '<th>Journée de Solidarité:<br /><i>'.$soliday.'</i></th>';
echo '<td>'.duration_aff(-$l[$soliday]['daily']).'</td>';
echo '</tr>';
}
if (isset($_GET['more_aff'])) {
echo '<tr>';
echo '<th>Congés payés:</th>';
echo '<td>'.duration_aff($monthcur['arret_cp']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Congés maladie (payés ?):</i></th>';
echo '<td></td>';
echo '</tr>';
echo '<tr>';
echo '<th>Heures payées hors CP:<br /><i>effectif+férié-solidarité</i></th>';
echo '<td>'.duration_aff($monthcur['paye']).'</td>';
echo '</tr>';
}

echo '<tr>';
echo '<td colspan="2"><hr /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>dont Heures >'.$weekly1.'h</th>';
echo '<td>'.duration_aff($monthcur['seuil1_duration']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>dont Heures >'.$weekly2.'h</th>';
echo '<td>'.duration_aff($monthcur['seuil2_duration']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>dont Heures dimanche/feriés</th>';
echo '<td>'.duration_aff($monthcur['ferie_trav_duration']).'</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>Cumul comptabilisé</th>';
echo '<td>'.duration_aff($monthcur['cumul']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Cumul théorique</th>';
echo '<td>'.duration_aff($monthcur['cumul_theo']).'</td>';
echo '</tr>';
echo '<tr style="color: '.($monthcur['cumul_delta']>=0 ?'green' :'red').';">';
echo '<th>Cumul Delta</th>';
echo '<td>'.duration_aff($monthcur['cumul_delta']).'</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr /></td>';
echo '</tr>';

if (isset($_GET['more_aff'])) {
echo '<tr>';
echo '<th>Annuel théorique</th>';
echo '<td>'.duration_aff($cumul['total_theorique']).'</td>';
echo '</tr>';
}
echo '<tr>';
echo '<th>Reste période</th>';
echo '<td>'.duration_aff($cumul['total_theorique']-$cumul['comptabilise']).'</td>';
echo '</tr>';

if (isset($_GET['cp_aff'])) {
echo '<tr>';
echo '<th>CP gagnés année précédente</th>';
echo '<td></td>';
echo '</tr>';
echo '<tr>';
echo '<th>CP Pris gagnés année précédente</th>';
echo '<td>'.duration_aff($cp['pris']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>CP Restant année précédente</th>';
echo '<td>'.duration_aff($cp['gagnes']-$cp['pris']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>CP Annuel théorique année en cours</th>';
echo '<td>'.duration_aff($cp['total_theorique']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>CP Gagnés année en cours pour année suivante</th>';
echo '<td>'.duration_aff($cp['gagnes']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<th>CP Pris par anticipation gagnés année en cours</th>';
echo '<td>'.duration_aff($cp['pris']).'</td>';
echo '</tr>';
}
echo '</table></div>';


// Feriés

if (!empty($holidays_aff)) {
	echo '<table border="1">';
	echo '<caption>Jours fériés</caption>';
	foreach($holidays as $holiday) {
		echo '<tr><td>'.$holiday.'</td></tr>';
	}
	echo '</table>';
}


// Affichage semaines

if (!empty($weeks_aff)) {
	echo '<p>Attention, les RTT ne sont volontairement pas ajoutées au delta, en effet, l\'idée est justement de les utiliser pour abaisser le delta !</p>';
	echo '<table border="1" cellpadding="2" id="cumul_week">';
	echo '<caption>Récap semaines</caption>';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Année</th>';
	echo '<th>Numéro</th>';
	echo '<th>Date début</th>';
	echo '<th>Date fin</th>';

	echo '<th>Jours<br />Travaillables</th>';
	echo '<th>Heures<br />Travaillables</th>';

	echo '<th>Travail</th>';
	echo '<th>Dépl.</th>';
	echo '<th>Form.</th>';
	echo '<th>Férié</th>';
	echo '<th>CP</th>';
	echo '<th>RTT</th>';
	echo '<th>Maladie</th>';
	echo '<th>Autre</th>';

	echo '<th>Effectif<br />(Trav.Dépl.Form.)</th>';
	echo '<th>Comptabilisé<br />(Effectuif+Abs.)</th>';
	echo '<th>Théorique</th>';
	echo '<th>Delta</th>';

	if (isset($_GET['more_aff'])) {
	echo '<th>Payé</th>';
	}

	echo '<th width="50">Cumul Comptabilisé</th>';
	echo '<th width="50">Cumul théorique</th>';
	echo '<th width="50">Cumul Delta</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($cumul_week as &$r) {
		echo '<tr>';
		echo '<td>'.$r['year'].'</td>';
		echo '<td>'.$r['weeknum'].'</td>';
		echo '<td>'.date_reverse($r['dates']['week_start']).'</td>';
		echo '<td>'.date_reverse($r['dates']['week_end']).'</td>';

		echo '<td>'.$r['nbworkdays'].'</td>';
		echo '<td>'.$r['weekly'].'</td>';

		echo '<td>'.duration_aff($r['duration']).'</p>';
		echo '<td>'.duration_aff($r['deplacement_duration']).'</p>';
		echo '<td>'.duration_aff($r['arret_formation']).'</p>';
		echo '<td>'.duration_aff($r['ferie_duration']).'</p>';
		echo '<td>'.duration_aff($r['arret_cp']).'</p>';
		echo '<td>'.duration_aff($r['arret_rtt']).'</p>';
		echo '<td>'.duration_aff($r['arret_maladie']).'</p>';
		echo '<td>'.duration_aff($r['arret_autre']).'</p>';

		echo '<td'.($r['effectif']>$weekly_max ?' class="alert"' :'').'>'.duration_aff($r['effectif']).'</p>';
		echo '<td>'.duration_aff($r['comptabilise']).'</p>';
		echo '<td>'.duration_aff($r['weekly']).'</p>';
		echo '<td style="color: '.($r['delta']>=0 ?'green' :'red').';">'.duration_aff($r['delta']).'</p>';

		if (isset($_GET['more_aff'])) {
		echo '<td>'.duration_aff($r['paye']).'</p>';
		}

		echo '<td>'.duration_aff($r['cumul']).'</p>';
		echo '<td>'.duration_aff($r['cumul_theo']).'</p>';
		echo '<td style="color: '.($r['cumul_delta']>=0 ?'green' :'red').';">'.duration_aff($r['cumul_delta']).'</p>';
		echo '</tr>';
	}
	unset($r);
	echo '</tbody>';
	echo '</table>';
}


// Affichage mois

if (!empty($month_aff)) {
	echo '<p>Attention, les RTT ne sont volontairement pas ajoutées au delta, en effet, l\'idée est justement de les utiliser pour abaisser le delta !</p>';
	echo '<table border="1" cellpadding="2" id="cumul_mois">';
	echo '<caption>Récap mois</caption>';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Mois</th>';
	echo '<th>Jours</th>';
	echo '<th>Jours<br />Travaillables</th>';
	echo '<th>Heures<br />Travaillables</th>';

	echo '<th>Travail</th>';
	echo '<th>Dépl.</th>';
	echo '<th>Form.</th>';
	echo '<th>Férié</th>';
	echo '<th>CP</th>';
	echo '<th>RTT</th>';
	echo '<th>Maladie</th>';
	echo '<th>Autre</th>';

	echo '<td></td>';
	echo '<th>Effectif<br />(Trav+Dépl+Form)</th>';
	echo '<th>Comptabilisé<br />(Effectif+Abs.)</th>';
	echo '<th>Théorique</th>';
	echo '<th>Delta</th>';

	if (isset($_GET['more_aff'])) {
	echo '<td>&nbsp;</td>';
	echo '<th>Payé</th>';
	echo '<th>Payé CP</th>';
	echo '<th>Payé Maladie</th>';
	}

	echo '<td></td>';
	echo '<th>Cumul comptabilisé</th>';
	echo '<th>Cumul théorique</th>';
	echo '<th>Cumul Delta</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($cumul_mois as &$r) {
		echo '<tr>';
		echo '<td>'.$r['date'].'</td>';

		echo '<td>'.$r['nbdays'].'</td>';
		echo '<td>'.$r['nbworkdays'].'</td>';
		echo '<td>'.$r['monthly'].'</td>';

		echo '<td>'.duration_aff($r['duration']).'</p>';
		echo '<td>'.duration_aff($r['deplacement_duration']).'</p>';
		echo '<td>'.duration_aff($r['arret_formation']).'</p>';
		echo '<td>'.duration_aff($r['ferie_duration']).'</p>';
		echo '<td>'.duration_aff($r['arret_cp']).'</p>';
		echo '<td>'.duration_aff($r['arret_rtt']).'</p>';
		echo '<td>'.duration_aff($r['arret_maladie']).'</p>';
		echo '<td>'.duration_aff($r['arret_autre']).'</p>';

		echo '<td></td>';
		echo '<td'.($r['effectif']>$monthly_max ?' class="alert"' :'').'>'.duration_aff($r['effectif']).'</p>';
		echo '<td>'.duration_aff($r['comptabilise']).'</p>';
		echo '<td>'.duration_aff($r['monthly']).'</p>';
		echo '<td style="color: '.($r['delta']>=0 ?'green' :'red').';">'.duration_aff($r['delta']).'</p>';

		if (isset($_GET['more_aff'])) {
		echo '<td></td>';
		echo '<td>'.duration_aff($r['paye']).'</p>';
		echo '<td>'.($r['arret_cp_j']>0 ?duration_aff($r['arret_cp_j']).'j' :'').'</p>';
		echo '<td>'.duration_aff($r['arret_maladie']).'</p>';
		}
		
		echo '<td></td>';
		echo '<td>'.duration_aff($r['cumul']).'</p>';
		echo '<td>'.duration_aff($r['cumul_theo']).'</p>';
		echo '<td style="color: '.($r['cumul_delta']>=0 ?'green' :'red').';">'.duration_aff($r['cumul_delta']).'</p>';
		echo '</tr>';
	}
	unset($r);
	echo '</tbody>';
	echo '</table>';
}

//var_dump($l);

// End of page
llxFooter();
$db->close();
