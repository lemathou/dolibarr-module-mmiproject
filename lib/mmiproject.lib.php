<?php
/* Copyright (C) 2022 Moulin Mathieu <contact@iprospective.fr>
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
 * \file    mmiproject/lib/mmiproject.lib.php
 * \ingroup mmiproject
 * \brief   Library files with common functions for MMIProject
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function mmiprojectAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("mmiproject@mmiproject");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/mmiproject/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/mmiproject/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/mmiproject/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@mmiproject:/mmiproject/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@mmiproject:/mmiproject/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'mmiproject@mmiproject');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'mmiproject@mmiproject', 'remove');

	return $head;
}

function date_reverse($date)
{
	$e = explode('-', $date);
	return implode('/', array_reverse($e));
}

function duration_aff($duration)
{
	return $duration != 0
		?number_format(round($duration, 2), 2, '.', '')
		:'';
}

function getStartAndEndDate($week, $year)
{
	$dto = new DateTime();
	$dto->setISODate($year, $week);
	$ret['week_start'] = $dto->format('Y-m-d');
	$dto->modify('+6 days');
	$ret['week_end'] = $dto->format('Y-m-d');
	return $ret;
}

function getHolidays($year = null)
{
	if ($year === null)
	{
		$year = intval(strftime('%Y'));
	}

	$easterDate = easter_date($year);
	$easterDay = date('j', $easterDate);
	$easterMonth = date('n', $easterDate);
	$easterYear = date('Y', $easterDate);

	$holidays = array(
		// Jours feries fixes
		mktime(0, 0, 0, 1, 1, $year),// 1er janvier
		mktime(0, 0, 0, 5, 1, $year),// Fete du travail
		mktime(0, 0, 0, 5, 8, $year),// Victoire des allies
		mktime(0, 0, 0, 7, 14, $year),// Fete nationale
		mktime(0, 0, 0, 8, 15, $year),// Assomption
		mktime(0, 0, 0, 11, 1, $year),// Toussaint
		mktime(0, 0, 0, 11, 11, $year),// Armistice
		mktime(0, 0, 0, 12, 25, $year),// Noel

		// Jour feries qui dependent de paques
		mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear),// Lundi de paques
		mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),// Ascension
		mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear), // Pentecote
	);

	sort($holidays);

	return $holidays;
}

function getHolidays2($year = null)
{
	if ($year === null)
	{
		$year = intval(strftime('%Y'));
	}

	$easterDate = easter_date($year);
	$easterDay = date('j', $easterDate);
	$easterMonth = date('n', $easterDate);
	$easterYear = date('Y', $easterDate);

	$holidays = array(
		// Jours feries fixes
		$year.'-01-01',// 1er janvier
		$year.'-05-01',// Fete du travail
		$year.'-05-08',// Victoire des allies
		$year.'-07-14',// Fete nationale
		$year.'-08-15',// Assomption
		$year.'-11-01',// Toussaint
		$year.'-11-11',// Armistice
		$year.'-12-25',// Noel

		// Jour feries qui dependent de paques
		date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear)),// Lundi de paques
		date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear)),// Ascension
		date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear)), // Pentecote
	);

	sort($holidays);

	return $holidays;
}