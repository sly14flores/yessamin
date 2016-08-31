<form id="subModule">
<table id="tab-replace-stock">
<tr><td colspan="4" style="color: #fe2e2e;">Replace this stock:</td></tr>
<tr>
<td>Product Code:</td><td><input type="text" id="srepcode" onfocus="this.blur();" /></td>
<td>Product Name:</td><td><input type="text" id="srepname" onfocus="this.blur();" /></td>
<td>Product Size:</td><td><input type="text" id="srepsize" onfocus="this.blur();" /></td>
<td>Quantity:</td><td><input type="text" id="srepqty" value="0" /></td>
</tr>
<tr><td colspan="4" style="color: #fe2e2e;">With this stock:</td></tr>
<tr>
<td>Product Code:</td><td><input type="text" name="repscode" id="repscode" /></td>
<td>Product Name:</td><td><input type="text" id="repsname" onfocus="this.blur();" /></td>
<td>Product Size:</td><td><input type="text" name="repssize" id="repssize" /></td>
<td>Quantity:</td><td><input type="text" name="repsqty" id="repsqty" value="1" /></td>
</tr>
<tr><td colspan="4" class="sub-validate"></tr>
</table>
</form>