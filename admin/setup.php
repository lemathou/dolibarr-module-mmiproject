<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022-2023 Moulin Mathieu <contact@iprospective.fr>
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
 * \file    mmiproject/admin/setup.php
 * \ingroup mmiproject
 * \brief   MMIProject setup page.
 */

// Load Dolibarr environment
require_once '../main_load.inc.php';

// Parameters
$arrayofparameters = array(
	'MMIPROJECT_TIME'=>array('type'=>'separator','enabled'=>1),
	'PROJECT_ADDTIMESPENT_MULTIPLE_USERID'=>array('type'=>'yesno', 'enabled'=>1),
	'MMIPROJECT_SOLIDAY'=>array('type'=>'date', 'enabled'=>1),

	'MMIPROJECT_DECENNALE'=>array('type'=>'separator','enabled'=>1),
	'MMIPROJECT_DECENNALE_FIELD'=>array('type'=>'yesno', 'enabled'=>1),
	'MMIPROJECT_DECENNALE_TITLE'=>array('type'=>'yesno', 'enabled'=>1),
	'MMIPROJECT_DECENNALE_TEXT'=>array('type'=>'textarea', 'enabled'=>1),

	'MMIPROJECT_TASKS'=>array('type'=>'separator','enabled'=>1),
	'TASK_SHOW_PARENT_LABEL'=>array('type'=>'yesno', 'enabled'=>1),
	'PROJECT_ALLOW_COMMENT_ON_PROJECT'=>array('type'=>'yesno','enabled'=>1),
	'PROJECT_ALLOW_COMMENT_ON_TASK'=>array('type'=>'yesno','enabled'=>1),
	'TASK_CREATE_WITHOUT_DEFAULT_CONTACT'=>array('type'=>'yesno','enabled'=>1),
	'PROJECT_TASK_LIST_LABEL_DISP'=>array('type'=>'array','list'=>['default'=>'PROJECT_TASK_LIST_LABEL_DISP_DEFAULT', 'nowrap'=>'PROJECT_TASK_LIST_LABEL_DISP_NOWRAP', 'full'=>'PROJECT_TASK_LIST_LABEL_DISP_FULL'],'enabled'=>1),

	'MMIPROJECT_HOLIDAYS'=>array('type'=>'separator','enabled'=>1),
	'MMIHOLIDAY_ALLOW_REQUEST_WITHOUT_OPEN_DAY'=>array('type'=>'yesno','enabled'=>1),
);

require_once('../../mmicommon/admin/mmisetup_1.inc.php');
