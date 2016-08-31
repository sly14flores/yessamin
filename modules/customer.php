<div id="sub-menu">
<div id="in-sub-menu">
<?php
session_start();
require '../grants.php';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<a href="javascript: customer(1);" class="tooltip"><span>Add Dealer</span><img src="images/add-user.png" /></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<a href="javascript: customer(2);" class="tooltip"><span>Edit Dealer</span><img src="images/edit-user.png" /></a>';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<a href="javascript: confirmCustomerDelete();" class="tooltip"><span>Delete Dealer</span><img src="images/delete-user.png" /></a>';
?>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Branch:</td><td><select id="fbranch" style="width: 100px;"><option value="0">ALL</option><option value="1">Francey</option><option value="2">Yessamin</option><option value="3">Sta. Maria</option></select></td>
<td>Member No:</td><td><input type="text" id="fmno" class="highlight" /></td>
<td>Fullname:</td><td><input type="text" id="ffname" class="highlight" /></td>
<td rowspan="2"><a href="javascript: filterCustomer();"><img src="images/search.png" /></a></td>
</tr>
<tr>
<td>Recruiter:</td><td><input type="text" id="frname" class="highlight" /></td>
<td>Address:</td><td><input type="text" id="fadd" class="highlight" /></td>
<td style="text-align: right;"><input type="checkbox" id="lowcl" /><label for="lowcl">&nbsp;Low CL</label></td>
</tr>
</table>
</div>