<?php

session_start();
require '../grants.php';

?>
<div id="sub-menu">
<div id="in-sub-menu">
<a href="javascript: cashier(1);" class="tooltip"><span>Add Cashier</span><img src="images/add-user.png" /></a>
<?php

if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<a href="javascript: cashier(2);" class="tooltip"><span>Edit Cashier</span><img src="images/edit-user.png" /></a>';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) echo '<a href="javascript: confirmCashierDelete();" class="tooltip"><span>Delete Cashier</span><img src="images/delete-user.png" /></a>';

?>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Fullname:</td><td><input type="text" id="fcname" class="highlight" /></td>
<td>Cashier ID:</td><td><input type="text" id="fcashierid" class="highlight" /></td>
<td><a href="javascript: filterCashier();"><img src="images/search.png" class="highlight" /></a></td>
</tr>
</table>
</div>