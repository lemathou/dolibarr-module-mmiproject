<h2>Ressources liées</h2>

<p style="float: right;margin: 10px;"><a href="?id=<?php echo $id; ?>&add">Ajouter</a></p>

<?php if (isset($_GET['add'])) { ?>
<form method="POST" action="?id=<?php echo $id; ?>&action=resource_add">
<table>
	<!--
	<tr>
		<td><label for="usage"><?php echo $langs->trans('ResourceUsage'); ?></label></td>
		<td><input name="usage" /></td>
	</tr>
	-->
	<tr>
		<td><label for="fk_resource"><?php echo $langs->trans('Resource'); ?></label></td>
		<td><select name="fk_resource"><option value="">--</option><?php foreach ($reso as $r) {
			echo '<option value="'.$r['rowid'].'">'.$r['ref'].'</option>';
		} ?></select></td>
	</tr>
	<!--
	<tr>
		<td><label for="fk_c_type_resource"><?php echo $langs->trans('ResourceType'); ?></label></td>
		<td><select name="fk_c_type_resource"><option value="">--</option><?php foreach ($rt as $r) {
			echo '<option value="'.$r['rowid'].'">'.$r['label'].'</option>';
		} ?></select></td>
	</tr>
	-->
	<tr>
		<td></td>
		<td></td>
		<td><input type="submit" name="" value="Associer une ressource" /></td>
	</tr>
</table></form>
<hr />
<?php } elseif (!empty($edit)) { ?>
<form method="POST" action="?id=<?php echo $id; ?>&action=resource_edit&link_id=<?php echo $_GET['edit']; ?>">
<table>
	<!--
	<tr>
		<td><label for="usage"><?php echo $langs->trans('ResourceUsage'); ?></label></td>
		<td><input name="usage" value="<?php echo $link['usage']; ?>" /></td>
	</tr>
	-->
	<tr>
		<td><label for="fk_resource"><?php echo $langs->trans('Resource'); ?></label></td>
		<td><select name="fk_resource"><option value="">--</option><?php foreach ($reso as $r) {
			echo '<option value="'.$r['rowid'].'"'.($link['fk_resource']==$r['rowid'] ?' selected' :'').'>'.$r['ref'].'</option>';
		} ?></select></td>
	</tr>
	<!--
	<tr>
		<td><label for="fk_c_type_resource"><?php echo $langs->trans('ResourceType'); ?></label></td>
		<td><select name="fk_c_type_resource"><option value="">--</option><?php foreach ($rt as $r) {
			echo '<option value="'.$r['rowid'].'"'.($link['fk_c_type_resource']==$r['rowid'] ?' selected' :'').'>'.$r['label'].'</option>';
		} ?></select></td>
	</tr>
	-->
	<tr>
		<td></td>
		<td></td>
		<td><input type="submit" name="" value="Modifier une ressource" /></td>
	</tr>
</table></form>
<hr />
<?php } ?>

<?php

require 'resources_list.tpl.php';
