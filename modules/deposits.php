<p style="text-align: center;"><a href="javascript: printDeposits();" class="tooltip"><span>Print Deposits</span><img src="images/print.png" /></a></p>
<div id="sub-menu">
<div id="in-sub-menu">
<form style="width: 360px;">
<fieldset style="padding: 5px;">
<legend>Tools</legend>
<input type="button" id="add" value="ADD" />
<input type="button" id="edit" value="EDIT" />
<?php
session_start();
require '../grants.php';
$store_branch = $_SESSION['branch'];
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<input type="button" id="delete" value="DELETE" />';
?>
</fieldset>
</form>
</div>
<div id="in-sub-menu"></div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Branch:</td><td><select id="fbranch" style="width: 85px;">
<option value="0">All</option>
<option value="1" <?php if ($store_branch == 1) echo "selected=\"selected\"";?> >Francey</option><option value="2" <?php if ($store_branch == 2) echo "selected=\"selected\"";?>>Yessamin</option><option value="3" <?php if ($store_branch == 3) echo "selected=\"selected\"";?> >Sta. Maria</option>
</select></td>
<td>Account Name:</td><td><select id="faccount-name" style="width: 100px;"></select></td>
<td>Start:</td><td><input type="text" id="fs" size="10" class="highlight" value="<?php //echo date("m/d/Y",strtotime("+8 Hour")); ?>" /></td>
<td>End:</td><td><input type="text" id="fe" size="10" class="highlight" value="" /></td>
<td><input type="button" id="search" value="SEARCH"/></td>
</tr>
</table>
</div>