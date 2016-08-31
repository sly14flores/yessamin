<?php

session_start();
$fullname = $_SESSION['fullname'];

?>
<form id="frmModule" class="clearfix" style="border-bottom: 1px solid #dddddd;">
<table id="tab-payment">
<tr>
<td colspan="3"><label for="recno">Receipt No:</label>&nbsp;<input type="text" name="recno" id="recno" class="iclear" size="16" onfocus="this.blur();" /><input type="hidden" id="hiscash" value="0" /></td>
<td colspan="3" align="right"><label for="recdate">Date:</label>&nbsp;<input type="text" name="recdate" id="recdate" class="iclear" value="<?php echo date("F j, Y",strtotime("+8 Hours")); ?>" onfocus="this.blur();" /></td>
</tr>
<tr>
<td><label for="tdn">Name:</label></td><td><input type="text" name="tdn" id="tdn" /><input type="hidden" id="hdid" value="0" /><input type="hidden" id="hclt" value="0" /></td>
<td><label for="prec">Recruiter:</label></td><td><input type="text" name="prec" id="prec" onfocus="this.blur();" /></td>
<td align="right"><label for="piclimit">Initial Credit Limit:</label></td><td align="right"><input type="text" name="piclimit" id="piclimit" onfocus="this.blur();" /><input type="hidden" id="returnedCL" value="0" /></td>
</tr>
<tr>
<td>Cashier:</td><td><input type="text" name="rca" id="rca" onfocus="this.blur();" value="<?php echo $fullname; ?>" /></td>
<td>&nbsp;</td><td>&nbsp;</td>
<td align="right"><label for="pclimit">Available Credit Limit:</label></td><td align="right"><input type="text" name="pclimit" id="pclimit" onfocus="this.blur();" /><input type="hidden" id="hpclimit" value="0" /></td>
</tr>
<!--<tr>
<td><label for="">Tra.No.:</label></td><td><input type="text" name="" id="" /></td>
<td><label for="">Amount:</label></td><td><input type="text" name="" id="" /></td>
<td colspan="2">Mode of Payment:&nbsp;<select id="mop" style="width: 90px;"><option value="0">Advance</option><option value="1">Full</option></select>&nbsp;<input type="button" id="addr" value="Add" /></td>
</tr>-->
</table>
</form>
<form id="terms-transactions">
<table id="tab-terms-transactions">
<thead>
<tr><td>Date</td><td>Tra.No.</td><td>Amount</td><td>Penalty</td><td>Balance</td><td>Due Date</td><td>Tool</td></tr>
</thead>
<tbody>
</tbody>
</table>
</form>
<form id="receipts">
<table id="receipts-items">
<thead>
<tr><td>Date</td><td>Tra.No.</td><td>Amount</td><td>Mode of Payment</td><td>Cashier</td><td>Tool</td></tr>
</thead>
<tbody>
</tbody>
</table>
</form>
<div style="padding-top: 5px;"><label for="span-amt">Amount:</label><input type="text" id="span-amt" class="highlight" value="0" style="text-align: right;" /><input type="button" id="span-pay" value="Span Payment" style="margin-left: 3px;" /></div><div id="receipt-total-amt" style="width: 200px; position: relative; top: 5px; left: 500px; text-align: right;">Total Amount:&nbsp;<span>0</span></div>
<table>
<tr><td class="validate"></td></tr>
</table>