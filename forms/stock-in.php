<?php

$src = $_GET['src'];
$br = (isset($_GET['br'])) ? $_GET['br'] : 1;

?>
<form id="frmModule" class="clearfix">
<table id="tab-stock-in">
<tr>
<td <?php if (($br == 2) || ($br == 3)) echo "colspan=\"2\""; ?>"><label for="sindate">Date Invoice:</label><input type="text" name="sindate" id="sindate" size="10" /></td>
<td>Cashier: <span class="stock-in-cashier"><?php if ($src == 1) { session_start(); echo $_SESSION['fullname']; }  ?></span></td>
<td colspan="2"><label for="srefno">Ref.No:</label><input type="text" name="srefno" id="srefno" size="10" /></td>
<td colspan="2"><label for="supplier">Supplier:</label><input type="text" name="supplier" id="supplier" size="17" /><input type="hidden" id="ssup" value="0" /></td>
</tr>
<tr>
<td align="left" colspan="<?php if (($br == 2) || ($br == 3)) echo "3"; else echo "2"; ?>"><label for="smno">Member No:</label><input type="text" name="smno" id="smno" /></td>
<td align="left" colspan="4"><label for="smn">Member Name:</label><input type="text" name="smn" id="smn" size="50" /></td>
</tr>
<tr>
<?php if (($br == 2) || ($br == 3)) echo "<td><label for=\"pacat\">Category:</label><select id=\"pacat\" style=\"width: 120px;\"></select></td>"; ?>
<td><label for="pcode">Product Code:</label><div class="autocomplete-own"><div></div><input type="text" class="iclear" name="pcode" id="pcode" size="12" /></div><input type="hidden" id="hsiid" value="0" /><input type="hidden" id="houtd" value="0" /></td>
<td><label for="pname">Product Name:</label><input type="text" class="iclear" name="pname" id="pname" size="20" /></td>
<td><label for="psize">Size:</label><input type="text" class="iclear" name="psize" id="psize" size="10" /></td>
<td align="right"><label for="sprice">Price:</label><input type="text" class="iclear" name="sprice" id="sprice" size="10" /></td>
<td align="right"><label for="squantity">Quantity:</label><input type="text" class="iclear" name="squantity" id="squantity" size="15" /></td>
<td><input type="button" id="adds" value="Add" /><input type="hidden" id="hic" value="0" /><input type="hidden" id="sinos" value="" /></td>
</tr>
</table>
</form>
<form id="frmStockItem">
<table id="tab-stock-in-item">
<thead>
<tr><?php if (($br == 2) || ($br == 3)) echo "<td>Category</td>"; ?><td>Product Code</td><td>Product Name</td><td>Size</td><td>Price</td><td>Quantity</td><td>Amount</td><td align="center">Action</td><?php if ($src == 2) echo "<td>Returns</td><td>Remarks</td>"; ?></tr>
</thead>
<tbody></tbody>
</table>
</form>
<table style="margin-top: 10px;">
<tr><td>&nbsp;Offset ID:&nbsp;</td><td><input type="text" id="stock-off-id" value="" style="width: 250px; text-align: left;" /></td><td>Offset:&nbsp;</td><td><input type="text" id="stock-off" value="0" style="width: 75px; text-align: right;" onchange="updateOffBal();" /></td><td>&nbsp;Mode of Payment</td><td>&nbsp;<select id="sel-stock-mop" style="width: 100px;" onchange="stockMOP(this.value);"><option value="1">COD</option><option value="0">Check</option><option value="2">Offset</option><option value="3">Swap</option></select></td><td><span class="stock-mop"></span></td></tr>
<tr><td>&nbsp;Balance:&nbsp;</td><td><input type="text" id="stock-off-bal" value="" style="width: 250px; text-align: left;" onfocus="this.blur();" /></td><td colspan="2">&nbsp;</td><td colspan="3">&nbsp;</td></tr>
<tr><td style="margin-top: 10px;">Avon Rebate:</td><td><input type="text" id="avon-rebate" value="0" style="width: 250px; margin-top: 5px;" /></td><td colspan="5">&nbsp;</td></tr>
<tr><td style="margin-top: 4px;">CFT Discount:</td><td><input type="text" id="avon-cft-discount" value="0" style="width: 250px; margin-top: 5px;" /></td><td colspan="5">&nbsp;</td></tr>
<tr><td style="margin-top: 4px;">NCFT Discount:</td><td><input type="text" id="avon-ncft-discount" value="0" style="width: 250px; margin-top: 2px;" /></td><td colspan="5">&nbsp;</td></tr>
<tr><td style="margin-top: 4px;">Homestyle Discount:</td><td><input type="text" id="avon-home-discount" value="0" style="width: 250px; margin-top: 2px;" /></td><td colspan="5">&nbsp;</td></tr>
<tr><td style="margin-top: 4px;">Health Care Discount:</td><td><input type="text" id="avon-health-discount" value="0" style="width: 250px; margin-top: 2px;" /></td><td colspan="5">&nbsp;</td></tr>
</table>
<div id="si-total-amt" style="width: 300px; position: relative; top: 5px; left: 850px;">Total Gross: <span>0</span></div>
<div id="si-total-net-amt" style="width: 300px; position: relative; top: 5px; left: 850px;">Total Net: <span>0</span></div>
<table>
<tr><td class="validate"></td></tr>
</table>