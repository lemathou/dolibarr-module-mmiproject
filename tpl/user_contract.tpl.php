<?php

$weekdays = [1=>'Lundi', 2=>'Mardi', 3=>'Mercredi', 4=>'Jeudi', 5=>'Vendredi', 6=>'Samedi'];

?><h2>Contrats de travail</h2>

<p style="float: right;margin: 10px;"><a href="?id=<?php echo $id; ?>&add">Ajouter</a></p>

<?php if (isset($_GET['add'])) { ?>
<form method="POST" action="?id=<?php echo $id; ?>&action=contract_add"><table>
	<tr>
		<td><label for="status"><?php echo $langs->trans('Status'); ?></label></td>
		<td><select name="status">
				<option value="0">Brouillon</option>
				<option value="1">Validée</option>
		</select></td>
	</tr>
	<tr>
		<td><label for="ref"><?php echo $langs->trans('Ref'); ?></label></td>
		<td><input id="ref" type="text" name="ref" value="" /></td>
	</tr>
	<tr>
		<td><label for="job"><?php echo $langs->trans('Job'); ?></label></td>
		<td><input id="job" type="text" name="job" value="" /></td>
	</tr>
	<tr>
		<td><label for="salary"><?php echo $langs->trans('Salary'); ?></label></td>
		<td><input id="salary" type="text" name="salary" value="" /></td>
	</tr>
	<tr>
		<td><label for="weeklyhours"><?php echo $langs->trans('WeeklyHours'); ?></label></td>
		<td><input id="weeklyhours" type="text" name="weeklyhours" value="35" /></td>
	</tr>
	<tr>
		<td><label for="dailyhours"><?php echo $langs->trans('DailyWorkedHours'); ?></label></td>
		<td><input id="dailyhours" type="text" name="dailyhours" value="7" /></td>
	</tr>
	<tr>
		<td><label for="workdaysnb"><?php echo $langs->trans('Extrafield_workdaysnb'); ?></label></td>
		<td><input id="workdaysnb" type="text" name="workdaysnb" value="5" /></td>
	</tr>
	<tr>
		<td><label for="workdays"><?php echo $langs->trans('Extrafield_workdays'); ?></label></td>
		<td><select id="workdays" multiple name="workdays[]" size="6"><?php foreach($weekdays as $i=>$j) echo '<option value="'.$i.'">'.$j.'</option>'; ?></select></td>
	</tr>
	<tr>
		<td><label for="workdays2"><?php echo $langs->trans('Extrafield_workdays2'); ?></label></td>
		<td><select id="workdays2" multiple name="workdays2[]" size="6"><?php foreach($weekdays as $i=>$j) echo '<option value="'.$i.'">'.$j.'</option>'; ?></select></td>
	</tr>
	<tr>
		<td><label for="dateemployment"><?php echo $langs->trans('DateEmploymentStart'); ?></label></td>
		<td><input id="dateemployment" type="text" name="dateemployment" value="" /></td>
	</tr>
	<tr>
		<td><label for="dateemploymentend"><?php echo $langs->trans('DateEmploymentEnd'); ?></label></td>
		<td><input id="dateemployment" type="text" name="dateemploymentend" value="" /></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><input type="submit" name="" value="Associer un contrat de travail" /></td>
	</tr>
</table></form>
<hr />
<?php } elseif (!empty($edit)) { ?>
<form method="POST" action="?id=<?php echo $id; ?>&action=contract_edit&link_id=<?php echo $_GET['edit']; ?>"><table>
	<tr>
		<td><label for="status"><?php echo $langs->trans('Status'); ?></label></td>
		<td><select name="status">
				<option value="0"<?php if ($link['status']=='0') echo 'selected'; ?>>Brouillon</option>
				<option value="1"<?php if ($link['status']=='1') echo 'selected'; ?>>Validée</option>
		</select></td>
	</tr>
	<tr>
		<td><label for="ref"><?php echo $langs->trans('Ref'); ?></label></td>
		<td><input id="ref" type="text" name="ref" value="<?php echo $link['ref']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="job"><?php echo $langs->trans('Job'); ?></label></td>
		<td><input id="job" type="text" name="job" value="<?php echo $link['job']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="salary"><?php echo $langs->trans('Salary'); ?></label></td>
		<td><input id="salary" type="text" name="salary" value="<?php echo $link['salary']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="weeklyhours"><?php echo $langs->trans('WeeklyHours'); ?></label></td>
		<td><input id="weeklyhours" type="text" name="weeklyhours" value="<?php echo $link['weeklyhours']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="dailyhours"><?php echo $langs->trans('DailyWorkedHours'); ?></label></td>
		<td><input id="dailyhours" type="text" name="dailyhours" value="<?php echo $link['dailyhours']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="workdaysnb"><?php echo $langs->trans('Extrafield_workdaysnb'); ?></label></td>
		<td><input id="workdaysnb" type="text" name="workdaysnb" value="<?php echo $link['workdaysnb']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="workdays"><?php echo $langs->trans('Extrafield_workdays'); ?></label></td>
		<td><select id="workdays" multiple name="workdays[]" size="6"><?php foreach($weekdays as $i=>$j) echo '<option value="'.$i.'"'.(in_array($i, $link['workdays']) ?' selected' :'').'>'.$j.'</option>'; ?></select></td>
	</tr>
	<tr>
		<td><label for="workdays2"><?php echo $langs->trans('Extrafield_workdays2'); ?></label></td>
		<td><select id="workdays2" multiple name="workdays2[]" size="6"><?php foreach($weekdays as $i=>$j) echo '<option value="'.$i.'"'.(in_array($i, $link['workdays2']) ?' selected' :'').'>'.$j.'</option>'; ?></select></td>
	</tr>
	<tr>
		<td><label for="dateemployment"><?php echo $langs->trans('DateEmploymentStart'); ?></label></td>
		<td><input id="dateemployment" type="text" name="dateemployment" value="<?php echo $link['dateemployment']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="dateemploymentend"><?php echo $langs->trans('DateEmploymentEnd'); ?></label></td>
		<td><input id="dateemploymentend" type="text" name="dateemploymentend" value="<?php echo $link['dateemploymentend']; ?>" /></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><input type="submit" name="" value="Modifier un contrat de travail" /></td>
	</tr>
</table></form>
<hr />
<?php } ?>

<?php

require 'contracts_list.tpl.php';
