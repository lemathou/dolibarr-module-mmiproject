<h2>Contrats de travail</h2>

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
		<td><input type="text" name="ref" value="" /></td>
	</tr>
	<tr>
		<td><label for="job"><?php echo $langs->trans('Job'); ?></label></td>
		<td><input type="text" name="job" value="" /></td>
	</tr>
	<tr>
		<td><label for="salary"><?php echo $langs->trans('Salary'); ?></label></td>
		<td><input type="text" name="salary" value="" /></td>
	</tr>
	<tr>
		<td><label for="weeklyhours"><?php echo $langs->trans('Weeklyhours'); ?></label></td>
		<td><input type="text" name="weeklyhours" value="" /></td>
	</tr>
	<tr>
		<td><label for="dateemployment"><?php echo $langs->trans('Dateemployment'); ?></label></td>
		<td><input type="text" name="dateemployment" value="" /></td>
	</tr>
	<tr>
		<td><label for="dateemploymentend"><?php echo $langs->trans('Dateemploymentend'); ?></label></td>
		<td><input type="text" name="dateemploymentend" value="" /></td>
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
		<td><input type="text" name="ref" value="<?php echo $link['ref']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="job"><?php echo $langs->trans('Job'); ?></label></td>
		<td><input type="text" name="job" value="<?php echo $link['job']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="salary"><?php echo $langs->trans('Salary'); ?></label></td>
		<td><input type="text" name="salary" value="<?php echo $link['salary']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="weeklyhours"><?php echo $langs->trans('Weeklyhours'); ?></label></td>
		<td><input type="text" name="weeklyhours" value="<?php echo $link['weeklyhours']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="dateemployment"><?php echo $langs->trans('Dateemployment'); ?></label></td>
		<td><input type="text" name="dateemployment" value="<?php echo $link['dateemployment']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="dateemploymentend"><?php echo $langs->trans('Dateemploymentend'); ?></label></td>
		<td><input type="text" name="dateemploymentend" value="<?php echo $link['dateemploymentend']; ?>" /></td>
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
