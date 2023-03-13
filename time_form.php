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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';

dol_include_once('/mmiproject/lib/mmiproject.lib.php');

$addtimespent_multiple_userid = $conf->global->PROJECT_ADDTIMESPENT_MULTIPLE_USERID;
$time_admin = !empty($user->rights->mmiproject->time->admin);

$action = GETPOST('action', 'alpha');

$projects_contacts = [];

// Récup depuis projet/task/time.php
if (GETPOST('_add') && $action == 'addtimespent' && $user->rights->projet->lire) {

    $object = new Task($db);
    $projectstatic = new Project($db);
    $extrafields = new ExtraFields($db);
    $extrafields->fetch_name_optionals_label($projectstatic->table_element);
    $extrafields->fetch_name_optionals_label($object->table_element);

    $id = GETPOST('taskid', 'int');
    $ref = GETPOST('ref', 'alpha');
    if ($id > 0 || $ref) {
        $object->fetch($id, $ref);
    }

	$error = 0;

	$timespent_durationhour = GETPOST('timespent_durationhour', 'int');
	$timespent_durationmin = GETPOST('timespent_durationmin', 'int');
	if (empty($timespent_durationhour) && empty($timespent_durationmin)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error++;
	}
	if ($addtimespent_multiple_userid) {
		if (empty(GETPOST("userid", 'array:int'))) {
			$langs->load("errors");
			setEventMessages($langs->trans('ErrorUserNotAssignedToTask'), null, 'errors');
			$error++;
		}
	}
	else {
		if (!GETPOST("userid", 'int')) {
			$langs->load("errors");
			setEventMessages($langs->trans('ErrorUserNotAssignedToTask'), null, 'errors');
			$error++;
		}
	}

	if (!$error) {
		if ($id || $ref) {
			$object->fetch($id, $ref);
		} else {
			if (!GETPOST('taskid', 'int') || GETPOST('taskid', 'int') < 0) {
				setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Task")), null, 'errors');
				$action = 'createtime';
				$error++;
			} else {
				$object->fetch(GETPOST('taskid', 'int'));
			}
		}

		if (!$error) {
			$object->fetch_projet();
			$project = $object->project;
			if (!isset($projects_contacts[$project->id])) {
				$projects_contacts[$project->id] = [];
				foreach($project->liste_contact(4, 'internal') as $project_contact) {
					$projects_contacts[$project->id][] = $project_contact['id'];
				}
			}

			if (empty($object->project->statut)) {
				setEventMessages($langs->trans("ProjectMustBeValidatedFirst"), null, 'errors');
				$action = 'createtime';
				$error++;
			} else {
				$object->timespent_note = GETPOST("timespent_note", 'alpha');
				if (GETPOST('progress', 'int') > 0) {
					$object->progress = GETPOST('progress', 'int'); // If progress is -1 (not defined), we do not change value
				}
				$object->timespent_duration = GETPOSTINT("timespent_durationhour") * 60 * 60; // We store duration in seconds
				$object->timespent_duration += (GETPOSTINT('timespent_durationmin') ? GETPOSTINT('timespent_durationmin') : 0) * 60; // We store duration in seconds
				if (GETPOST("timehour") != '' && GETPOST("timehour") >= 0) {	// If hour was entered
					$object->timespent_date = dol_mktime(GETPOST("timehour", 'int'), GETPOST("timemin", 'int'), 0, GETPOST("timemonth", 'int'), GETPOST("timeday", 'int'), GETPOST("timeyear", 'int'));
					$object->timespent_withhour = 1;
				} else {
					$object->timespent_date = dol_mktime(12, 0, 0, GETPOST("timemonth", 'int'), GETPOST("timeday", 'int'), GETPOST("timeyear", 'int'));
				}
				if ($addtimespent_multiple_userid) {
					foreach(GETPOST("userid", 'array:int') as $userid) {
						$object->timespent_fk_user = $userid;
						$result = $object->addTimeSpent($user);
						if ($result >= 0) {
							setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
							// Si user pas dans projet on l'ajouter comme contributeur
							if (!in_array($userid, $projects_contacts[$project->id])) {
								$projects_contacts[$project->id][] = $userid;
								$project->add_contact($userid, 'PROJECTCONTRIBUTOR', 'internal');
							}
						} else {
							setEventMessages($langs->trans($object->error), null, 'errors');
							$error++;
						}
					}
				}
				else {
					$object->timespent_fk_user = $userid = GETPOST("userid", 'int');
					$result = $object->addTimeSpent($user);
					if ($result >= 0) {
						setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
						// Si user pas dans projet on l'ajouter comme contributeur
						if (!in_array($userid, $projects_contacts[$project->id])) {
							$projects_contacts[$project->id][] = $userid;
							$project->add_contact($userid, 'PROJECTCONTRIBUTOR', 'internal');
						}
					} else {
						setEventMessages($langs->trans($object->error), null, 'errors');
						$error++;
					}
				}
			}
		}
	} else {
		if (empty($id)) {
			$action = 'createtime';
		} else {
			$action = 'createtime';
		}
	}
}

/*
 * View
 */

llxHeader("", $langs->trans("MMIProjectAreaTimeForm"));
echo '<link rel="stylesheet" href="css/mmiprojects.css" />';

print load_fiche_titre($langs->trans("MMIProjectAreaTimeForm"), '', 'mmiproject@mmiproject');

//var_dump($user); die();

$time_now = time();
$date = GETPOST('date');
if (is_numeric(strpos($date, '/'))) {
	$date = explode('/', $date);
	$date = array_reverse($date);
	$date = implode('-', $date);
}
if (empty($date)) {
	$time = $time_now;
	$date = date('Y-m-d', $time);
}
else {
	$time = strtotime($date);
}

$date_before_dmy = date('d/m/Y', $time-86400);
$date_after_dmy = date('d/m/Y', $time+86400);


$year = substr($date, 0, 4);
$month = substr($date, 5, 2);
$day = substr($date, 8, 2);

// Voir le travail de tous les utilisateurs
$users_all = GETPOST('users_all', 'bool');

//var_dump($user);
$userid = GETPOST('userid');
$userids = (empty($userid) ?[$user->id] :(is_numeric($userid) ?[$userid] :$userid));
//var_dump($userids);
$fk_user = $user->id;
$user_name = $user->login;
$sql = 'SELECT u.rowid, u.firstname, u.lastname, CONCAT(u.firstname, " ", u.lastname) AS name
    FROM '.MAIN_DB_PREFIX.'user u';
$q = $db->query($sql);
$users = [];
while($r=$db->fetch_array($q)) {
    //var_dump($r['rowid']);
    $users[$r['rowid']] = $r;
}
//var_dump($users);

// Voir le travail dans tous les projets
$projects_all = GETPOST('projects_all', 'bool');
// Filtrer pour un projet particulier
$fk_project = GETPOST('fk_project', 'int');

// Tâches à proposer
/*
Dont l'utilisateur est au choix :
- présent sur le projet (@todo case à cocher masquer les projets cloturés, coché par défaut)
- présent sur la tache (tâche=>projet donc inutile)
- y a passé du temps (@todo case à cocher c'est pas toujours pertinent, décoché par défaut)
Dont le projet est :
- actif (et non cloturé)
*/
$task_sql_element_on = [];
$tasks = [];
$project_ids = [];
$sql = 'SELECT DISTINCT pt.*

    FROM '.MAIN_DB_PREFIX.'projet_task pt
	
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pt2
        ON pt2.fk_object=pt.rowid
    LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_time ptt
        ON ptt.fk_task=pt.rowid
	INNER JOIN '.MAIN_DB_PREFIX.'projet p
        ON p.rowid=pt.fk_projet
	LEFT JOIN '.MAIN_DB_PREFIX.'element_contact pe
        ON pe.element_id=pt.fk_projet
			AND pe.fk_c_type_contact IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'c_type_contact WHERE element="project")

    WHERE (
		p.fk_statut = 1
		AND (
			pe.fk_socpeople IN ('.implode(', ', $userids).')
			OR ptt.fk_user IN ('.implode(', ', $userids).')
			OR '.($time_admin ?'1' :'0').'
		)
	)';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_array($q)) {
        //var_dump($r['rowid']);
        $tasks[$r['rowid']] = $r;
        if (!in_array($r['fk_projet'], $project_ids))
            $project_ids[] = $r['fk_projet'];
    }
}

// Projets associés
$projects = [];
$sql = 'SELECT DISTINCT p.*
    FROM '.MAIN_DB_PREFIX.'projet p
    WHERE p.rowid IN ('.implode(',', $project_ids).')';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_array($q)) {
        //var_dump($r['rowid']);
        $projects[$r['rowid']] = $r;
    }
}

// Temps passé
$time_day = [];
$time_day2 = [];
$sql = 'SELECT ptt.*
    FROM '.MAIN_DB_PREFIX.'projet_task_time ptt
    INNER JOIN '.MAIN_DB_PREFIX.'projet_task pt ON pt.rowid=ptt.fk_task
	LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pt2
        ON pt2.fk_object=pt.rowid
	INNER JOIN '.MAIN_DB_PREFIX.'projet p
        ON p.rowid=pt.fk_projet
    WHERE ptt.task_date="'.$db->escape($date).'"
    '.(!$projects_all && $fk_project>0 ?' AND p.rowid='.$fk_project :'').'
    '.(!$users_all ?' AND ptt.fk_user IN ('.implode(', ', $userids).')' :'').'
    ORDER BY ptt.task_datehour, ptt.task_duration, ptt.fk_task';
//echo '<p>'.$sql.'</p>';
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
    while($r=$db->fetch_array($q)) {
        //var_dump($r);
        $time_day[$r['rowid']] = $r;
        if (empty($time_day2[$r['task_datehour'].'-'.$r['task_duration'].'-'.$r['fk_task']]))
            $time_day2[$r['task_datehour'].'-'.$r['task_duration'].'-'.$r['fk_task']] = ['rows'=>[], 'userids'=>[]];
        $time_day2[$r['task_datehour'].'-'.$r['task_duration'].'-'.$r['fk_task']]['rows'][$r['rowid']] = $r;
        $time_day2[$r['task_datehour'].'-'.$r['task_duration'].'-'.$r['fk_task']]['userids'][] = $r['fk_user'];
    }
}

$form = new Form($db);
//$formfile = new FormFile($db);

//var_dump($time_day);

?>

<script type="text/javascript">
$(document).ready(function(){
    $('#form_add').each(function(){
        var changed_last;
        $('#begin_hour, #end_hour, #duration', this).change(function(){
            var changed = $(this).attr('id');
            //alert(changed);

            var time_regex = /^([0-9]+)(:|h)?([0-9]*)$/g;

            var begin_hour = $('#form_add #begin_hour').val();
            begin_hour = begin_hour.replace('h', ':');
            var begin_hour_ok = begin_hour.match(time_regex);
            var begin_hour2 = parseTime(begin_hour);
            $('#form_add #begin_hour').css('backgroundColor', (begin_hour_ok ? 'transparent' : 'red'));
            var end_hour = $('#form_add #end_hour').val();
            var end_hour_ok = end_hour.match(time_regex);
            end_hour = end_hour.replace('h', ':');
            var end_hour2 = parseTime(end_hour);
            $('#form_add #end_hour').css('backgroundColor', (end_hour_ok ? 'transparent' : 'red'));
            var duration = $('#form_add #duration').val();
            var duration_ok = duration.match(time_regex);
            duration = duration.replace('h', ':');
            var duration2 = parseTime(duration);
            $('#form_add #duration').css('backgroundColor', (duration_ok ? 'transparent' : 'red'));

            var regex_ok = begin_hour_ok && end_hour_ok && duration_ok;

            if (regex_ok) {
                //alert(begin_hour);

                if (changed=='begin_hour' || changed=='duration') {
                    end_hour2 = [begin_hour2[0] + duration2[0], begin_hour2[1] + duration2[1]];
                    //alert(end_hour2);
                }
                else if(changed=='end_hour') {
                    var bh = begin_hour2[0];
                    var eh = end_hour2[0];
                    var bm = begin_hour2[1];
                    var em = end_hour2[1];
                    if (em<bm) {
                        var h = eh-bh-1;
                        var m = 60-bm+em;
                    }
                    else {
                        var h = eh-bh;
                        var m = em-bm;
                    }
                    duration2 = [h, m];
                    //alert(end_hour2);
                }

                begin_hour = parseTime2(begin_hour2);
                end_hour = parseTime2(end_hour2);
                duration = parseTime2(duration2);

                $('#form_add #begin_hour').val(begin_hour);
                $('#form_add #end_hour').val(end_hour);
                $('#form_add #duration').val(duration);

                $('#form_add input[name=timehour]').val(begin_hour2[0]);
                $('#form_add input[name=timemin]').val(begin_hour2[1]);
                $('#form_add input[name=timespent_durationhour]').val(duration2[0]);
                $('#form_add input[name=timespent_durationmin]').val(duration2[1]);
            }

            changed_last = changed;
        });
        $('#begin_hour, #end_hour, #duration').focus(function() {
            $(this).select();
        });
    });
});

function parseTime(t)
{
    var t2 = t.split(':');
    var h = parseFloat(t2[0]);
    t2[0] = parseInt(t2[0]);
    t2[1] = (t2[1]!=undefined ?parseInt(t2[1]) :0);
    if (h!=t2[0]) {
        t2[1] += parseInt(60*(h-t2[0]));
    }
    return t2;
}

function parseTime2(t)
{
    while (t[1]>=60) {
        t[0] += 1;
        t[1] -= 60;
    }

    var h = t[0];
    if (h==0)
        h = '00';
    else if (h<10)
        h = '0'+h;
    
    var m = t[1];
    if (m==0)
        m = '00';
    else if (m<10)
        m = '0'+m;
    
    var t2 = [h, m];
    return t2.join(':');
}
</script>

<!-- /projet/task/time.php -->
<form id="form_add" action="" method="POST">
<input name="action" type="hidden" value="addtimespent" />

<input name="timeyear" type="hidden" value="<?php echo $year; ?>" />
<input name="timemonth" type="hidden" value="<?php echo $month; ?>" />
<input name="timeday" type="hidden" value="<?php echo $day; ?>" />
<input name="timehour" type="hidden" value="" />
<input name="timemin" type="hidden" value="" />
<input name="timespent_durationhour" type="hidden" value="" />
<input name="timespent_durationmin" type="hidden" value="" />
<!-- <input name="taskid" type="hidden" value="" /> -->

<table border="1">
<tr>
	<th>Date</th>
	<th style="min-width: 250px;">Utilisateurs</th>
	<th style="min-width: 250px;">Projet</th>
	<th></th>
</tr>
<tr>
	<td>
		<p><?php echo $form->selectDate($date, 'date', '', '', '', '', 1, 1); //$date; ?></p>
		<p style="text-align: center;"><a href="javascript:;" onclick="$('#date').val('<?php echo $date_before_dmy; ?>').change();$('#refresh').click();">&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="$('#date').val('<?php echo $date_after_dmy; ?>').change();$('#refresh').click();">&gt;</a></p>
	</td>
	<td><?php
    if ($time_admin && $addtimespent_multiple_userid)
        print $form->select_dolusers((GETPOST('userid', 'array:int') ? GETPOST('userid', 'array:int') : [$user->id]), 'userid', 0, '', 0, '', [], 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToProject"), '', 0, 0, 1);
    else
        print '<p><input type="hidden" name="userid'.($addtimespent_multiple_userid ?'[]' :'').'" value="'.$user->id.'" /> '.$user->lastname.' '.$user->firstname.'</p>';
	?>
	<p><input type="checkbox" name="users_all"<?php if ($users_all) echo ' checked'; ?> /> Voir le temps passé de tous les utilisateurs<br />(sur les projets auquels j'ai accès)</p></td>
    <td>
    <p><?php
        $projects_form = [];
        //var_dump($projects);
        foreach($projects as $project)
            $projects_form[$project['rowid']] = ['label'=>'['.$project['ref'].'] - '.$project['title']];
        echo $form->selectArray('fk_project', $projects_form, $fk_project, 'Choisir un projet pour filtrer les tâches');
    ?></p>
	<p><input type="checkbox" name="projects_all"<?php if ($projects_all) echo ' checked'; ?> /> Voir le temps passé sur tous les projets<br />(auquels j'ai accès)</p></td>
	<td><input id="refresh" type="submit" name="_refresh" value="Refresh" /></td>
</tr>
</table>

<br />

<table border="1">
<thead>
    <tr>
        <th>Heure<br />début</th>
        <th>Heure<br />fin</th>
        <th>Durée</th>
        <td>Utilisateur</td>
        <th>Tâche</th>
        <th>Projet</th>
        <th>Info.</th>
        <?php
        foreach($users as $row) {
            // @todo On verra plus tard
            if (in_array($row['rowid'], $userids)) {
                //echo '<th>'.$row['firstname'].' '.$row['lastname'].'</th>';
            }
        }
        ?>
    </tr>
</thead>
<tbody>
<?php
$duree_tot = 0;
?>
<?php foreach($time_day as $row) {
    $task = $tasks[$row['fk_task']];
    $project = $projects[$task['fk_projet']];
    //var_dump($task);
    //var_dump($row);
    $datenew = (empty($rowold) || ($row['task_datehour'] != $rowold['task_datehour']) || ($row['task_duration'] != $rowold['task_duration']) || ($row['fk_task'] != $rowold['fk_task']));
    $rowold = $row;
    $duree_tot += $row['task_duration'];
    $duree_h = floor($row['task_duration']/3600);
    $duree_m = floor($row['task_duration']/60) - $duree_h*60;
    $duree = ($duree_h>=10 ?$duree_h :'0'.$duree_h).':'.($duree_m>=10 ?$duree_m :'0'.$duree_m);
    //var_dump($userids, $time_day2[$row['task_datehour'].'-'.$row['task_duration'].'-'.$row['fk_task']]['userids']);  echo '<br />';
    $time_useradd = empty(array_intersect($userids, $time_day2[$row['task_datehour'].'-'.$row['task_duration'].'-'.$row['fk_task']]['userids']));
    ?>
    <tr>
        <td class="begin_hour" align="right"><?php if ($datenew) echo substr($row['task_datehour'], 11, 5); ?></td>
        <td class="end_hour" align="right"><?php if ($datenew) echo $datefin=date('H:i', strtotime($row['task_datehour'])+$row['task_duration']); ?></td>
        <td class="duration" align="right"><?php if ($datenew) echo $duree; ?></td>
        <td><?php echo $users[$row['fk_user']]['name']; ?></td>
        <td data-fk-task="<?php echo $task['rowid']; ?>"><?php echo '<a href="/projet/tasks/time.php?id='.$task['rowid'].'">'.$task['label'].'</a>'; ?></td>
        <td><?php echo '<a href="/projet/tasks/time.php?withproject=1&projectid=7?id='.$project['rowid'].'">'.$project['title'].'</a>'; ?></td>
        <td><?php if (!empty($row['note'])) echo '<span style="cursor: help;" title="'.$row['note'].'">...</span>'; ?></td>
        <td>
            <?php if ($row['fk_user']==$user->id || $time_admin) { ?>
            <a class="reposition editfielda" target="_blank" href="/projet/tasks/time.php?id=<?php echo $row['fk_task']; ?>&amp;action=editline&amp;lineid=<?php echo $row['rowid']; ?>&contextpage=timespentlist"><span class="fas fa-pencil-alt" style=" color: #444;" title="Modifier"></span></a>
            <a class="reposition paddingleft" target="_blank" href="/projet/tasks/time.php?id=<?php echo $row['fk_task']; ?>&amp;action=deleteline&amp;lineid=<?php echo $row['rowid']; ?>&contextpage=timespentlist&amp;token=<?php echo $token; ?>"><span class="fas fa-trash pictodelete paddingleft" style="" title="Supprimer"></span></a>
            <?php }
            if ($datenew && $time_useradd) { ?>
            <input class="duplicate" type="button" value="Dupliquer" />
            <?php } ?>
        </td>
    </tr>
<?php } ?>
<?php if(!empty($duree_tot)) {
            $duree_h = floor($duree_tot/3600);
            $duree_m = floor($duree_tot/60) - $duree_h*60;
            $duree = ($duree_h>=10 ?$duree_h :'0'.$duree_h).':'.($duree_m>=10 ?$duree_m :'0'.$duree_m);
?>
    <tr>
        <th colspan="2">Journée complète</th>
        <td class="duration" align="right"><?php echo $duree; ?></td>
    </tr>
    <tr>
    </tr>
<?php } ?>
    <tr>
        <td><input id="begin_hour" name="begin_hour" type="text" size="5" value="<?php echo isset($datefin) ?$datefin :'00:00'; ?>" style="text-align: right; border: 0;padding: 0;" /></td>
        <td><input id="end_hour" name="end_hour" type="text" size="5" value="00:00" style="text-align: right; border: 0;padding: 0;" /></td>
        <td><input id="duration" name="duration" type="text" size="5" value="00:00" style="text-align: right; border: 0;padding: 0;" /></td>
        <td>Utilisateurs listés en en-tête</td>
        <td colspan="2"><?php
        
        $tasks_form = [];
        //var_dump($projects);
        foreach($tasks as $task) {
            if ($fk_project>0) {
                if ($fk_project==$task['fk_projet'])
                    $tasks_form[$task['rowid']] = ['label'=>'['.$task['ref'].'] - '.$task['label']];
            }
            else {
                $project = $projects[$task['fk_projet']];
                $tasks_form[$task['rowid']] = ['label'=>'['.$task['ref'].'] - '.$project['title'].' =&gt; '.$task['label']];
            }
        }
        echo $form->selectArray('taskid', $tasks_form, '', 'Choisir une tâche'); //fk_task
        ?></td>
    </tr>
    <tr>
        <td colspan="4">Commentaire (optionnel) :</td>
        <td colspan="2"><textarea name="timespent_note" style="width: 100%;"></textarea></td>
        <td style="border:0;"></td>
        <td style="border:0;"><input name="_add" type="submit" value="Ajouter" /></td>
    </tr>
</tbody>
</table>
</form>

<script type="text/javascript">
function duplicate()
{
	alert('Fonctionnalité à terminer');
	return;
    $('#begin_hour').val($('.begin_hour', this.parentNode.parentNode).html()).change();
    //$('#end_hour').val($('.end_hour', this.parentNode.parentNode).html()).change();
    $('#duration').val($('.duration', this.parentNode.parentNode).html()).change();
    $('#fk_task').val($('[data-fk-task]', this.parentNode.parentNode).attr('data-fk-task'));
    $('#form_add input[name=_add]').click();

}
$(document).ready(function(){
    $('input.duplicate').click(duplicate);
});
</script>

<?php

// End of page
llxFooter();
$db->close();
