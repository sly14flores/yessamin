<form id="subModule">
<table id="tab-repalce-item">
<tr><td colspan="4" style="color: #fe2e2e;">Replace this item:</td></tr>
<tr>
<td>Product Code:</td><td><input type="text" id="irepcode" onfocus="this.blur();" /></td>
<td>Product Name:</td><td><input type="text" id="irepname" onfocus="this.blur();" /></td>
<td>Product Size:</td><td><input type="text" id="irepsize" onfocus="this.blur();" /></td>
<td>Quantity:</td><td><input type="text" id="irepqty" value="0" /></td>
</tr>
<tr><td colspan="4" style="color: #fe2e2e;">With this item:</td></tr>
<tr>
<td>Product Code:</td><td><input type="text" name="repicode" id="repicode" /><input type="hidden" id="hrsino" value="0" /></td>
<td>Product Name:</td><td><input type="text" id="repiname" onfocus="this.blur();" /></td>
<td>Product Size:</td><td><input type="text" id="repisize" onfocus="this.blur();" /></td>
<td>Quantity:</td><td><input type="text" name="repiqty" id="repiqty" value="1" /></td>
</tr>
<tr><td colspan="4" class="sub-validate"></tr>
</table>
</form>