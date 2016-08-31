<form id="frmModule">
<table id="tab-manual-remittance">
<tr><td>Date:</td><td><input type="text" name="mr-date" id="mr-date" size="30" value="<?php echo date("m/d/Y",strtotime("+8 Hour")); ?>" onfocus="this.blur();" /></td></tr>
<tr><td>Dealer Name:</td><td><input type="text" name="mr-dealer" id="mr-dealer" size="30" /><input type="hidden" id="mr-did" value="0" /></td></tr>
<tr><td>Amount:</td><td><input type="text" name="mr-amount" id="mr-amount" size="30" /></td></tr>
<tr><td>Note:</td><td><input type="text" name="mr-note" id="mr-note" class="iclear" size="30" /></td></tr>
<tr><td colspan="2" class="validate"></tr>
<tr><td colspan="2" class="password"></tr>
</table>
</form>