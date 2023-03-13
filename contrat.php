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

if ($admin) {

}
else {
	$fk_user = $user->id;
}

$employ = [];
$sql = 'SELECT *
	FROM '.MAIN_DB_PREFIX.'_user_employment
	WHERE fk_user='.$fk_user;
$q = $db->query($sql);
//var_dump($q); var_dump($db);
if ($q) {
	while($r=$db->fetch_array($q)) {
		$employ[$r['rowid']] = $r;
	}
}

/*
 * View
 */

$form = new Form($db);
//$formfile = new FormFile($db);

llxHeader("", $langs->trans("MMIProjectAreaContrat"));
echo '<link rel="stylesheet" href="css/mmiprojects.css" />';

print load_fiche_titre($langs->trans("MMIProjectAreaContrat"), '', 'mmiproject@mmiproject');

echo '<p>A Faire bien plus tard... pour l\'instant insérer à la main en base de données...</p>';

if ($admin)
	print $form->select_dolusers((GETPOST('userid', 'int') ? GETPOST('userid', 'int') : $user->id), 'userid', 0, '', 0, '', [], 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToProject"), '', 0, 0, 1);

var_dump($employ);

// End of page
llxFooter();
$db->close();
