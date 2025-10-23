<?php

// Protection against direct call of file
if (!defined('DOL_VERSION')) {
	die('You cannot access this file directly');
}

$resources_crud_links = true;
include DOL_DOCUMENT_ROOT.'/custom/mmiproject/tpl/resources.tpl.php';
