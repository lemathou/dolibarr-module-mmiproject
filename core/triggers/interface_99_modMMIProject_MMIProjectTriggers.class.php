<?php
/* Copyright (C) 2022 SuperAdmin <contact@calyclay.com>
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
 * \file    core/triggers/interface_99_modMMIProject_MMIProjectTriggers.class.php
 * \ingroup mmiproject
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modMMIProject_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';


/**
 *  Class of triggers for MMIProject module
 */
class InterfaceMMIProjectTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "MMIProject triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'mmiproject@mmiproject';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->mmiproject) || empty($conf->mmiproject->enabled)) {
			return 0; // If module is not enabled, we do nothing
		}

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		//var_dump($methodName);
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		};

		// Or you can execute some code here
		switch ($action) {
			case 'TASK_MODIFY':
				break;
			//case 'TASK_DELETE':

			// Task time spent
			case 'TASK_TIMESPENT_CREATE':
			case 'TASK_TIMESPENT_MODIFY':
			case 'TASK_TIMESPENT_DELETE':
			case 'timespent_VALIDATE':
				//var_dump($object);

				//$object->fetch_optionals();
				global $db;
				$t_user = new User($db);
				$t_user->fetch($object->timespent_fk_user);

				$ym = date('Y-m', $object->timespent_date);
				$user_id = $object->timespent_fk_user;

				$sql = 'SELECT rowid, month_hours_sign_date FROM '.MAIN_DB_PREFIX.'user_pay WHERE fk_user='.$user_id.' AND `date`="'.$ym.'-01"';
				$resql = $db->query($sql);
				if ($resql && ($db->num_rows($resql) > 0) && ($obj = $db->fetch_object($resql))) {
					$this->errors[] = 'Impossible de supprimer, modifier ou ajouter du temps sur le mois '.$ym.', il a été validé le '.$obj->month_hours_sign_date;
					return -1;
					break;
				}
				if ($object->planned_workload) {
					$reste = 100 - $object->progress;
					// Evaluation basée sur la quantité produite
					if ($object->array_options['options_qte'] > 0) {
						// Théorique
						$qte = $object->array_options['options_qte'];
						$duration = $object->planned_workload/3600;
						$perf = $qte/$duration;
						// Effectif
						$qte_real = $object->array_options['options_qte_real'];
						$duration_real = $object->duration_effective/3600;
						$perf_real = $qte_real/$duration_real;
						// Reste
						$qte_reste = $qte-$qte_real;
						$object->progress = round($qte_real/$qte*100);
						// Projection selon effectif
						$duration_reste = $perf_real ?round($qte_reste/$perf_real, 2) :'';
						//var_dump($duration_reste);
						$object->array_options['options_temps_restant_prevu_1'] = $duration_reste;
						//var_dump($object->progress);
						// Projection selon théorique
						$duration_reste = $perf_real ?round($qte_reste/$perf, 2) :'';
						$object->array_options['options_temps_restant_prevu_2'] = $duration_reste;
					}
					// Evaluation basée sur la progression
					else {
						if ($reste > 0 && $reste != 100) {
							$duration_real = $object->duration_effective/3600;
							$reste_workload = $duration_real/($reste/100);
							$duration_reste = round($reste_workload, 2);
							$object->array_options['options_temps_restant_prevu_1'] = $duration_reste;
							//var_dump($object->progress);
							// Projection selon théorique
							$reste_workload = $object->planned_workload*$reste/100;
							$duration_reste = round($reste_workload/3600, 2);
							$object->array_options['options_temps_restant_prevu_2'] = $duration_reste;
						}
						elseif($reste == 100) {
							$object->array_options['options_temps_restant_prevu_1'] = round($object->duration_effective/86400, 2);
							$object->array_options['options_temps_restant_prevu_2'] = round($object->duration_effective/86400, 2);
						}
						elseif($reste == 0) {
							$object->array_options['options_temps_restant_prevu_1'] = 0;
							$object->array_options['options_temps_restant_prevu_2'] = 0;
						}
					}
					//var_dump($object);
					// No triggers !!
					$object->update($user, true);
				}
				return 1;
				break;

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}
}
