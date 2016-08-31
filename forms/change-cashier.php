<?php require '../config.php'; ?>
<form id="frmModule">
<table id="tab-change-cashier">
<tr>
<td>Cashier:</td>
<td>
<select id="tra-cashier" style="width: 300px;">
<?php

$sql = "select user_id, if(user_branch = 1,'Francey','Yessamin') branch, concat(firstname, ' ', lastname) cashier from users";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
	$rec = $rs->fetch_array();
	echo '<option value="' . $rec['user_id'] . '">' . $rec['branch'] . ' - ' . $rec['cashier'] . '</option>';
}
}
db_close();

?>
</select>
</td>
</tr>
</table>
</form>