<?php

$tmode = (isset($_GET['tmode'])) ? $_GET['tmode'] : 1;
$br = (isset($_GET['br'])) ? $_GET['br'] : 1;
$pt = (isset($_GET['pt'])) ? $_GET['pt'] : "";

?>
<form id="frmModule" class="clearfix">
<table id="tab-transaction">
<tr>
<td align="left"><label for="trano">Tra No:</label><input type="text" name="trano" id="trano" class="iclear" size="10" disabled /></td>
<td align="left" colspan="2"><label for="tdate">Date:</label><input type="text" size="20" name="tdate" id="tdate" class="iclear" value="<?php echo date("F j, Y"); ?>" disabled /></td>
<td align="right"><label for="tstatus">Status:</label><input type="text" name="tstatus" id="tstatus" class="iclear" size="10" disabled /><input type="hidden" id="hstat" value="0" /><!--remove vat and additional discount if avon and 1-7 days onward--></td>
<td align="right" colspan="2"><label for="ddate">Due Date:</label><input type="text" size="20" name="ddate" id="ddate" class="iclear" value="" disabled /></td>
</tr>
<tr>
<td align="right" colspan="2"><label for="tdn">Dealer's Name:</label><input type="text" name="tdn" id="tdn" size="45" /><input type="hidden" id="hdid" value="0" /><input type="hidden" id="hdt" /><input type="hidden" id="hclt" value="0" /></td>
<td align="right" colspan="2"><label for="trec">Recruiter:</label><input type="text" name="trec" id="trec" class="iclear" size="30" disabled /></td>
<td align="right" colspan="2"><label for="tclimit">Credit Limit Balance:</label><input type="text" name="tclimit" id="tclimit" size="10" class="iclear" disabled /><input type="hidden" id="htclimit" value="0" /></td>
</tr>
<tr>
<td align="right"><label for="tpcode">Product Code:</label><input type="text" class="iclear" name="tpcode" id="tpcode" class="highlight" size="20" onclick="searchProduct();" disabled /><input type="hidden" id="hsino" value="0" /><input type="hidden" id="hsupd" value="" /><input type="hidden" id="hsupid" value="0" /></td>
<td align="right"><label for="tpname">Product Name:</label><input type="text" class="iclear" name="tpname" id="tpname" size="15" disabled /><input type="hidden" id="hitem" value="" /></td>
<td align="right"><label for="tpsize">Size:</label><input type="text" class="iclear" name="tpsize" id="tpsize" size="10" disabled /></td>
<td align="right"><label for="tprice">Price:</label><input type="text" class="iclear" name="tprice" id="tprice" size="10" disabled /><input type="hidden" id="hdis" value="0" /></td>
<td align="right"><label for="tquantity">Quantity:</label><input type="text" class="iclear" name="tquantity" id="tquantity" class="highlight" size="5" disabled /><input type="hidden" id="hstock" value="0" /></td>
<td><?php if ( ($tmode == 1) || ($pt == 'Terms') ) echo '<input type="checkbox" id="borrow" />&nbsp;<label for="borrow">Borrow</label>&nbsp;<input type="button" id="adds" value="Add" />'; ?><?php if ( ($br == 2) && ($tmode == 2) && ($pt == 'Terms') ) echo "<input type=\"button\" id=\"swaps\" style=\"margin-left: 5px;\" value=\"Swap\" />"; ?><input type="hidden" id="htc" value="0" /><input type="hidden" id="hhtc" value="0" /><input type="hidden" id="tinos" value="" /><input type="hidden" id="hsiid" value="0" /></td>
</tr>
</table>
</form>
<form id="frmTransactionItem">
<table id="tab-transaction-item">
<thead>
<tr><?php if ( ($br == 2) && ($tmode == 2) && ($pt == 'Terms') ) echo "<td>&nbsp;</td>" ?><td>Company</td><td>Quantity</td><td>Item Name/Description/Size/Variety</td><td>Unit Price</td><td>Gross Amount</td><td>Discount</td><td>Net Price</td><td align="center">Action</td><?php if ($tmode == 2) echo "<td>Returns</td>"; ?></tr>
</thead>
<tbody></tbody>
</table>
</form>
<div id="tra-total-qty" style="width: 200px; position: relative; top: 5px; left: 900px; text-align: right;">Total Item(s):&nbsp;<span>0</span></div>
<div id="tra-total-amt" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Net Amount Due:&nbsp;<span>0</span></div>
<div id="tra-add-dis" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Cash Discount:&nbsp;<span>0</span></div>
<div id="tra-vat" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Vat:&nbsp;<span>0</span></div>
<div id="tra-avond" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Add.Dis:&nbsp;<span>0</span></div>
<?php if ($tmode == 2) echo '<div id="tra-penalty" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Penalty:&nbsp;<span>0</span><input type="hidden" id="tbal" value="0" /></div>'; ?>
<div id="tra-total" style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;">Total:&nbsp;<span>0</span></div>
<div style="width: 200px; position: relative; top: 5px; left: 900px; margin-top: 5px; text-align: right;"><input type="checkbox" id="terms" onchange="ttype(this.id);" />&nbsp;<label for="terms">Terms&nbsp;</label><input type="checkbox" id="cash" onchange="ttype(this.id);" />&nbsp;<label for="cash">Cash</label></div>
<table>
<tr><td class="validate"></td></tr>
</table>