<form id="frmModule">
<table id="tab-avon-category">
<tr>
<td>Company:</td><td><input type="text" id="offset-co" size="20" /><input type="hidden" id="offset-coid" value="0" /></td>
<td>Member Name:</td><td><input type="text" id="offset-mn" size="30" /></td>
</tr>
<tr>
<td>ID:</td><td><input type="text" id="offset-cid" onfocus="this.blur();" size="20" /><input type="hidden" id="offset-id" /></td>
<td>Date:</td><td><input type="text" id="offset-date" size="30" value="<?php echo date("m/d/Y",strtotime("+8 Hour")); ?>" onfocus="this.blur();" /></td>
</tr>
<tr>
<td colspan="4">Total per month:
<select style="width: 100px;" id="sel-month-total" onchange="offsetPerMonth();">
<?php

$mono = array("0","01","02","03","04","05","06","07","08","09","10","11","12");
$mo = array("All","January","February","March","April","May","June","July","August","September","October","November","December");
for ($i=0; $i<=12; $i++) {
echo '<option value="' . $mono[$i] . '">' . $mo[$i] . '</option>';
}

?>
</select><input type="text" size="10" id="moyr" value="<?php echo date("Y"); ?>" /><input type="hidden" id="hoid" value="0" />
</td>
</tr>
<tr>
<td colspan="2">Total: <span class="offset-total"></span></td><td colspan="2">Balance: <span class="offset-bal"></span></td>
</tr>
<tr><td colspan="4">Date: <input type="text" id="offset-item-date" /> &nbsp;&nbsp;Amount: <input type="text" id="offset-item-amount" /> &nbsp;&nbsp;<input type="button" id="offset-item-add" value="ADD" /><input type="hidden" id="hic" value="0" /><input type="hidden" id="loffiid" value="0" /><input type="hidden" id="del-oiids" value="" /></td></tr>
</table>
</form>
<form id="frmOffsetItem">
<table id="tab-offset-item">
<thead><tr><td>Date</td><td>No.</td><td>Amount</td><td>Tools</td></tr></thead>
<tbody></tbody>
</table>
</form>
<table><tr><td class="validate"></tr></table>