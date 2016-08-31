<form id="frmModule" class="clearfix">
<table id="tab-transaction">
<tr>
<td colspan="4"><label for="tdn">Name:</label><input type="text" name="tdn" id="tdn" size="61" value="Walk-in" onfocus="this.blur();" /></td>
<td align="right" colspan="3"><label for="tdate">Date:</label><input type="text" size="20" name="tdate" id="tdate" class="iclear" value="<?php echo date("F j, Y"); ?>" disabled /></td>
</tr>
<tr>
<td><label for="tpcode">Product Code:</label><input type="text" class="iclear" name="tpcode" id="tpcode" onclick="searchProduct();" size="20" /><input type="hidden" id="hsino" value="0" /><input type="hidden" id="hsupd" value="" /><input type="hidden" id="hsupid" value="0" /></td>
<td><label for="tpname">Product Name:</label><input type="text" class="iclear" name="tpname" id="tpname" size="15" disabled /><input type="hidden" id="hitem" value="" /></td>
<td><label for="tpsize">Size:</label><input type="text" class="iclear" name="tpsize" id="tpsize" size="10" disabled /></td>
<td><label for="tprice">Price:</label><input type="text" class="iclear" name="tprice" id="tprice" size="10" disabled /></td>
<td><label for="tpdisc">Discount:</label><input type="text" name="tpdisc" id="tpdisc" size="5" value="0" /></td>
<td><label for="tquantity">Quantity:</label><input type="text" class="iclear" name="tquantity" id="tquantity" size="15" /><input type="hidden" id="hstock" /><input type="hidden" id="hsiid" value="0" /></td>
<td><input type="button" id="adds" value="Add" /><input type="hidden" id="htc" value="0" /></td>
</tr>
</table>
</form>
<form id="frmTransactionItem">
<table id="tab-transaction-item">
<thead>
<tr><td>Company</td><td>Quantity</td><td>Item Name/Description/Size/Variety</td><td>Unit Price</td><td>Gross Amount</td><td>Discount</td><td>Net Price</td><td align="center">Action</td></tr>
</thead>
<tbody></tbody>
</table>
</form>
<div id="tra-total-qty" style="width: 200px; position: relative; top: 5px; left: 750px; margin-top: 5px; text-align: right;">Total Item(s):&nbsp;<span>0</span></div>
<div id="tra-total" style="width: 200px; position: relative; top: 5px; left: 750px; margin-top: 5px; text-align: right;">Total:&nbsp;<span>0</span></div>
<table>
<tr><td class="validate"></td></tr>
</table>