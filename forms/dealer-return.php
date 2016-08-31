<form id="subModule">
<table id="tab-stock-return">
<tr><td><label for="dretdate">Date:</label></td><td><input type="text" id="dretdate" value="<?php echo date("F j, Y",strtotime("+8 hours")); ?>" onfocus="this.blur();" /></td></tr>
<tr><td><label for="drettrano">Tra.No.:</label></td><td><input type="text" name="drettrano" id="drettrano" onfocus="this.blur();" /></td></tr>
<tr><td><label for="dretsup">Supplier:</label></td><td><input type="text" name="dretsup" id="dretsup" onfocus="this.blur();" /></tr>
<tr><td><label for="dretcode">Product Code:</label></td><td><input type="text" name="dretcode" id="dretcode" onfocus="this.blur();" /></td></tr>
<tr><td><label for="dretname">Product Name:</label></td><td><input type="text" name="dretname" id="dretname" onfocus="this.blur();" /></td></tr>
<tr><td><label for="dretsize">Product Size:</label></td><td><input type="text" name="dretsize" id="dretsize" onfocus="this.blur();" /></td></tr>
<tr><td><label for="dretqty">Quantity:</label></td><td><input type="text" name="retqty" id="dretqty" value="1" /></td></tr>
<tr><td><label for="dretnote">Note:</label></td><td><textarea style="resize: none;" rows="3" cols="17" name="dretnote" id="dretnote"></textarea></td></tr>
<tr><td colspan="2" class="sub-validate"></tr>
</table>
</form>