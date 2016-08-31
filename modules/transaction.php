<div id="sub-menu">
<div id="in-sub-menu">
<a href="javascript: transaction(1);" class="tooltip"><span>New Transaction</span><img src="images/add-product.png" /></a>
<a href="javascript: transaction(2);" class="tooltip"><span>View Transaction</span><img src="images/view.png" /></a>
<a href="javascript: walkIn();" class="tooltip"><span>WALK INs</span><img src="images/walk-in.png" /></a>
<?php
session_start();
require '../grants.php';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<a href="javascript: confirmTransactionDelete();" class="tooltip"><span>Delete Transaction</span><img src="images/delete-product.png" /></a>';
?>
<a href="javascript: dealerReturns();" class="tooltip"><span>View Dealer's Returns Summary</span><img src="images/return-64.png" /></a>
<a href="javascript: traReport();" class="tooltip"><span>Print Report</span><img src="images/print.png" /></a>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Payment Type:</td><td align="left"><select id="fptype" style="width: 75px;"><option value="-1">All</option><option value="0">Terms</option><option value="1">Cash</option></select></td>
<td align="left">Branch:</td><td align="left"><select id="fbranch" style="width: 85px;"><option value="0">ALL</option><option value="1">Francey</option><option value="2">Yessamin</option><option value="3">Sta. Maria</option></select></td>
<td align="left">Start:&nbsp;<input type="text" id="fs" size="10" class="highlight" value="<?php echo date("m/d/Y"); ?>" /></td>
<td align="left">End:&nbsp;<input type="text" id="fe" size="10" class="highlight" value="" /></td>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td>Transaction No:</td><td><input type="text" id="ftrano" class="highlight" /></td>
<td>Dealer's Name:</td><td><input type="text" id="fcustomer" class="highlight" /></td>
<td>Product Code/Name/Size:</td><td><input type="text" id="fpcon" class="highlight" /></td>
<td><select id="unpaid-due" style="width: 65px;"><option value="0">All</option><option value="1">Due</option><option value="2">Unpaid</option><option value="3">Paid</option></select></td>
<td><a href="javascript: filterTransaction();"><img src="images/search.png" /></a></td>
</tr>
</table>
</div>