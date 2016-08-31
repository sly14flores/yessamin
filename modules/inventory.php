<?php

session_start();
$br = $_SESSION['branch'];

?>
<div id="sub-menu">
<div id="in-sub-menu">
<a href="javascript: printInventory();" class="tooltip"><span>Print Inventory</span><img src="images/print.png" /></a>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr><td colspan="<?php if ($br == 2) echo "7"; else echo "5"; ?>"><label for="">Start Date:</label> <input type="text" id="fsd" /> <label for="">End Date:</label> <input type="text" id="fed" /></td></tr>
<tr>
<td>Supplier:</td><td><input type="text" id="fsup" class="highlight" /><input type="hidden" id="fhsup" value="0" /></td>
<?php if ($br == 2) echo '<td>Category:</td><td><select id="fcat" class="highlight" style="width: 150px;"></select></td>'; ?>
<td>Product Code/Name/Size:</td><td><input type="text" id="fpcon" class="highlight" size="30" /></td>
<td><input type="button" id="search" value="SEARCH"/></td>
</tr>
</table>
</div>