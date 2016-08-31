<form id="subModule">
<table id="tab-stock-return">
<tr><td><label for="retdate">Date:</label></td><td><input type="text" id="retdate" value="<?php echo date("F j, Y",strtotime("+8 hours")); ?>" onfocus="this.blur();" /></td></tr>
<tr><td><label for="retref">Ref.No.:</label></td><td><input type="text" name="retref" id="retref" onfocus="this.blur();" /></td></tr>
<tr><td><label for="retsup">Supplier:</label></td><td><input type="text" name="retsup" id="retsup" onfocus="this.blur();" /></tr>
<tr><td><label for="retcode">Product Code:</label></td><td><input type="text" name="retcode" id="retcode" onfocus="this.blur();" /></td></tr>
<tr><td><label for="retname">Product Name:</label></td><td><input type="text" name="retname" id="retname" onfocus="this.blur();" /></td></tr>
<tr><td><label for="retsize">Product Size:</label></td><td><input type="text" name="retsize" id="retsize" onfocus="this.blur();" /></td></tr>
<tr><td><label for="retqty">Quantity:</label></td><td><input type="text" name="retqty" id="retqty" value="1" /></td></tr>
<tr><td><label for="retnote">Note:</label></td><td><textarea style="resize: none;" rows="3" cols="17" name="retnote" id="retnote"></textarea></td></tr>
<tr><td colspan="2" class="sub-validate"></tr>
</table>
</form>