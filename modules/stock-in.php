<div id="sub-menu">
<div id="in-sub-menu">
<?php
session_start();
require '../grants.php';
$m_br = $_SESSION['branch'];
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$ENCODER_GRANT) == 1)) echo '<a href="javascript: stock(1);" class="tooltip"><span>Add Stock</span><img src="images/add-product.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$ENCODER_GRANT) == 1)) echo '<a href="javascript: stock(2);" class="tooltip"><span>Edit Stock</span><img src="images/edit-product.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1)) echo '<a href="javascript: confirmStockDelete();" class="tooltip"><span>Delete Stock</span><img src="images/delete-product.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$ENCODER_GRANT) == 1)) echo '<a href="javascript: stockReturns();" class="tooltip"><span>View Returned Stocks Summary</span><img src="images/return-64.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$ENCODER_GRANT) == 1)) echo '<a href="javascript: avonCats();" class="tooltip"><span>Avon Categories</span><img src="images/category-64.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$ENCODER_GRANT) == 1)) echo '<a href="javascript: offsets();" class="tooltip"><span>Offsets from Returns</span><img src="images/offset.png" /></a>';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<a href="javascript: loans();" class="tooltip"><span>Loans</span><img src="images/loans.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1) || (substr_count($USER_GRANTS,$CASHIER_GRANT) == 1)) echo '<a href="utils/stock.php" target="_blank" class="tooltip"><span>Stocks Utility</span><img src="images/utility.png" /></a>';
?>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td colspan="2" style="text-align: left;">Start: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="fs" size="10" class="highlight" value="<?php echo date("m/d/Y"); ?>" /></td>
<td style="text-align: left;">End: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="fe" size="10" class="highlight" value="" /></td>
<td colspan="2">Due Date: <input type="text" id="fd" size="10" class="highlight" value="" /></td>
</tr>
<tr>
<td colspan="2" style="text-align: left;">Supplier: <input type="text" id="fsupplier" class="highlight" /><input type="hidden" id="fsup" value="0" /></td>
<td colspan="2" style="text-align: left;">Ref No: <input type="text" id="frefno" size="10" class="highlight" /></td>
<td>&nbsp;</td>
</tr>
<tr>
<td>Member Name:</td><td><input type="text" id="fmemn" size="20" class="highlight" /></td>
<td>Product Code/Name/Size:</td><td><input type="text" id="fpcon" class="highlight" /></td>
<td><a href="javascript: filterStock();"><img src="images/search.png" /></a></td>
</tr>
</table>
</div>