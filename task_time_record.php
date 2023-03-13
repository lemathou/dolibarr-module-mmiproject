<?php

// Load Dolibarr environment
require_once 'env.inc.php';
require_once 'main_load.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("MMIProjectArea"));

print load_fiche_titre($langs->trans("MMIProjectArea"), '', 'mmiproject.png@mmiproject');

// End of page
llxFooter();
$db->close();

