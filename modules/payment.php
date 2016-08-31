<div id="sub-menu">
<div id="in-sub-menu">
<input type="button" id="add" value="ADD" />
<input type="button" id="edit" value="EDIT" />
<?php
session_start();
require '../grants.php';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<input type="button" id="delete" value="DELETE" />';
?>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Payment Type:</td><td><select id="fptype" style="width: 75px;"><option value="-1">All</option><option value="0">Terms</option><option value="1">Cash</option></select></td>
<td>Branch:</td><td><select id="fbranch" style="width: 85px;"><option value="0">ALL</option><option value="1">Francey</option><option value="2">Yessamin</option><option value="3">Sta. Maria</option></select></td>
<td>Start:</td><td><input type="text" id="fs" size="10" class="highlight" value="<?php echo date("m/d/Y"); ?>" /></td>
<td>End:</td><td><input type="text" id="fe" size="10" class="highlight" value="" /></td>
<td>No:</td><td><input type="text" id="frno" size="10" class="highlight" /></td>
<td>TN no:</td><td><input type="text" id="ftnno" size="10" class="highlight" /></td>
<td>Dealer's Name:</td><td><input type="text" id="fcustomer" class="highlight" /></td>
<td><input type="button" id="search" value="SEARCH"/></td>
</tr>
</table>
</div>