<form id="frmModule" class="frmModule">
<table id="tab-product">
<tr>
<td>Member Number:</td><td><input type="text" name="mnumber" id="mnumber" onfocus="this.blur();" /></td>
<td>Credit Limit:</td><td><select id="selCredit" onchange="togCredLine();"><option value="0">Initial</option><option value="1">Custom Credit Limit</option><option value="2">Unlimited Credit Limit</option></select></td>
<td colspan="2"><input type="text" name="climit" id="climit" size="34" value="1500" class="iclear" disabled /></td>
</tr>
<tr>
<td>First Name:</td><td><input type="text" name="fname" id="fname" /></td>
<td>Last Name:</td><td><input type="text" name="lname" id="lname" /></td>
<td>Middle Name:</td><td><input type="text" name="mname" id="mname" /></td>
</tr>
<tr>
<td>Address:</td><td colspan="3"><input type="text" name="address" id="address" size="60" /></td>
<td>Contact(s):</td><td><input type="text" name="contact" id="contact" /></td>
</tr>
<tr>
<td>Age:</td><td><input type="text" name="age" id="age" /></td>
<td>Birthday:</td><td><input type="text" name="bday" id="bday" /></td>
<td>Occupation:</td><td><input type="text" name="job" id="job" /></td>
</tr>
<tr>
<td>Civil Status:</td><td><select id="cstatus"><option value="Single">Single</option><option value="Married">Married</option></select></td>
<td>Spouse Name:</td><td><input type="text" name="spouse" id="spouse" /></td>
<td>Recruiter:</td><td><input type="text" name="recruiter" id="recruiter" /></td>
</tr>
<tr>
<td>Discount Type:</td>
<td><select id="discountt" style="width: 180px;">
<option value="1">Basic Discount</option>
<option value="2">Top Seller's Discount</option>
<option value="3">Outright Discount</option>
<option value="4">Special Discount</option>
<option value="5">SL Discount</option>
</select></td>
<td>Branch:</td>
<td><select id="cbranch" style="width: 100px;">
<option value="1">Francey</option>
<option value="2">Yessamin</option>
</select></td>
<td><input type="checkbox" id="addcl" disabled onchange="togAddCL();" /><label for="addcl">&nbsp;Add CL:&nbsp;</label></td>
<td><input type="text" id="add-cl" value="0" disabled /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="checkbox" id="unli-terms" />&nbsp;<label for="unli-terms"><a href="javascript: unliT();" class="tooltip-min" style="text-decoration: none;">Unlimited Terms<span>No due dates / No penalty</span></a></label></td>
<td colspan="2">&nbsp;</td>
<td><input type="checkbox" id="reset-cl" disabled onchange="togResetCL();" /><label for="reset-cl">&nbsp;Reset CL</label></td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="padding-top: 10px">Category:</td><td style="padding-top: 10px"><select style="width: 180px;" id="customer-category"><option value="fd">FD</option><option value="cbc">CBC</option></select></td>
<td colspan="4">&nbsp;</td>
</tr>
<tr><td colspan="6" class="validate"></tr>
</table>
</form>