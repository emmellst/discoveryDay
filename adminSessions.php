<?php
require_once("database.php");
require_once("Session.php");
require_once("functions.php");

$allSessions = array();
$allSessions = getAllSessions();
?>

<table id="sessionTable" class="table table-condensed display">
	<thead>
	<tr>
		<th>Name (Click to Edit)</th>
		<th>Cost</th>
		<th>Block</th>
		<th>Capacity</th>
		<th>Filled</th>
		<th>% Full</th>
		<th>Buffer</th>
		<th>Room</th>
		<th>Supervisor</th>
		<th>Secretary</th>
		<th>Presenter</th>
		<th>Active</th>
	</tr>
	</thead>
	<tbody>

<?php
foreach($allSessions as $session) {
	if ($session->id == 0) continue;
?>
	<tr>
	<td><a href="#" data-toggle="modal" data-target="#editSession" onclick="adminUpdateSession(<?php echo $session->id?>);"><?php echo $session->name?></a></td>
	<td><?php echo $session->cost?></td>
	<td><?php
		if ($session->linked == "") echo $session->block;
		else {
			echo "DOUBLE";
		}
	?></td>
		<td><?php echo $session->capacity?></td>
		<td><?php echo $session->filled?></td>
		<td><strong><?php echo  round(($session->filled / $session->capacity) *100) ?></strong></td>
		<td><?php echo $session->buffer?></td>
		<td><?php echo $session->room?></td>
		<td><?php echo $session->supervisor?></td>
		<td><?php echo $session->secretary?></td>
		<td><?php echo $session->presenter?></td>
		<td><a onclick="toggleActiveSession(<?php echo $session->id?>);"><?php echo $session->active == "1" ? "YES" : "NO"; ?></a></td>
	</tr>
<?php	

}
?>
</tbody>
