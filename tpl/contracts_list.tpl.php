<?php
$object_class = get_class($object);
?>
<table border="1" cellpadding="4">
	<tr>
		<td>#</td>
		<td>status</td>
		<td>ref</td>
		<td>job</td>
		<td>salary</td>
		<td>weeklyhours</td>
		<td>dateemployment</td>
		<td>dateemploymentend</td>
	</tr>
<?php foreach($contracts as $row) {
	echo '<tr>';
	echo '<td>'.$row['rowid'].'</td>';
	echo '<td>'.$row['status'].'</td>';
	echo '<td>'.$row['ref'].'</td>';
	echo '<td>'.$row['job'].'</td>';
	echo '<td>'.$row['salary'].'</td>';
	echo '<td>'.$row['weeklyhours'].'</td>';
	echo '<td>'.$row['dateemployment'].'</td>';
	echo '<td>'.$row['dateemploymentend'].'</td>';
	echo '<td><a href="?id='.$id.'&edit='.$row['rowid'].'">Modifier</a> <a href="?id='.$id.'&delete='.$row['rowid'].'" onclick="return confirm(\'Supprimer ?\');">Supprimer</a></td>';
	echo '</tr>';
} ?>
</table>
