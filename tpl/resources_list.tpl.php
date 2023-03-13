<?php
$object_class = get_class($object);
?>
<table border="1" cellpadding="4">
	<tr>
		<td>#</td>
		<!--<td>Utilisation</td>-->
		<?php if ($object_class=='Project') { ?><td>Tâche</td><?php } ?>
		<td>Ressource</td>
		<!--<td>Type de ressource</td>-->
	</tr>
<?php foreach($pr as $row) {
	echo '<tr>';
	echo '<td>'.$row['rowid'].'</td>';
	//echo '<td>'.$row['usage'].'</td>';
	if ($object_class=='Project')
		echo '<td><a href="/projet/tasks/task.php?id='.$row['fk_task'].'&withproject=1">'.$row['task_name'].'</a></td>';
	echo '<td>'.(!empty($row['fk_resource']) ?$reso[$row['fk_resource']]['ref'] :'').'</td>';
	//echo '<td>'.(!empty($row['fk_c_type_resource']) ?$rt[$row['fk_c_type_resource']]['label'] :'').'</td>';
	if (!empty($resources_crud_links))
		echo '<td><a href="?id='.$id.'&edit='.$row['rowid'].'">Modifier</a> <a href="?id='.$id.'&delete='.$row['rowid'].'" onclick="return confirm(\'Supprimer ?\');">Supprimer</a></td>';
	echo '</tr>';
} ?>
</table>
