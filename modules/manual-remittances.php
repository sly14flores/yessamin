<p style="text-align: center;"><a href="javascript: printMRemittance();" class="tooltip"><span>Print Manual Remittance</span><img src="images/print.png" /></a></p>
<div id="sub-menu">
<div id="in-sub-menu">
<form style="width: 360px;">
<fieldset style="padding: 5px;">
<legend>Remittance</legend>
<input type="button" id="add" value="ADD" />
<input type="button" id="edit" value="EDIT" />
<?php
session_start();
require '../grants.php';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<input type="button" id="delete" value="DELETE" />';
?>
<a href="#" class="tooltip-min"><input style="margin-left: 20px;" type="button" id="cutoff" value="Cutoff" /><span id="dco">First Cut-off</span><input type="hidden" id="hco" value="1" /></a>
</fieldset>
</form>
</div>
<div id="in-sub-menu">
<!--<form style="width: 360px;">
<fieldset style="padding: 5px;">
<legend>Deduction</legend>
<input type="button" id="add-deduction" value="ADD" />
<input type="button" id="edit-deduction" value="EDIT" />
<?php
//if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<input type="button" id="delete-deduction" value="DELETE" />';
?>
</fieldset>
</form>-->
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Cutoff:</td><td><select id="sel-cutoff" style="width: 130px;"><option value="0">All</option><option value="1">First Cut-off</option><option value="2">End of the day</option></select></td>
<td>Start:</td><td><input type="text" id="fs" size="10" class="highlight" value="<?php echo date("m/d/Y",strtotime("+8 Hour")); ?>" /></td>
<td>End:</td><td><input type="text" id="fe" size="10" class="highlight" value="" /></td>
<td>Dealer's Name:</td><td><input type="text" id="fcustomer" class="highlight" /></td>
<td><input type="button" id="search" value="SEARCH"/></td>
</tr>
</table>
</div>