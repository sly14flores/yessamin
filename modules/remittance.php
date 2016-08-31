<?php

session_start();
require '../grants.php';
$store_branch = $_SESSION['branch'];

$co = "First Cutoff";
if ((date("D") == "Sat") || (date("D") == "Sun")) $co = "End of the day";
//if (date("D") == "Fri") $co = "End of the day";

?>
<div id="sub-menu">
<div id="in-sub-menu">
<p><?php /* if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) */ echo '<a href="javascript: cutOff();" class="tooltip" style="padding-right: 8px;"><span>' . $co . '</span><img src="images/cutoff.png" /></a>'; ?><a href="javascript: printRemittance();" class="tooltip"><span>Print Remittance</span><img src="images/print.png" /></a></p>
<p>
<input type="button" id="add" value="ADD Deduction" />
<?php
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<input type="button" id="edit" value="EDIT Deduction" />';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) echo '<input type="button" id="delete" value="DELETE Deduction" />';
?>
</p>
</div>
</div>
<div id="search-bar">
<table id="filter">
<tr>
<td>Branch:</td><td><select id="fbranch" style="width: 85px;">
<option value="1" <?php if ($store_branch == 1) echo "selected=\"selected\"";?> >Francey</option><option value="2" <?php if ($store_branch == 2) echo "selected=\"selected\"";?>>Yessamin</option><option value="3" <?php if ($store_branch == 3) echo "selected=\"selected\"";?> >Sta. Maria</option>
</select></td>
<td>Cutoff:</td><td><select id="fcutoff" style="width: 110px;"><option value="0">All</option><option value="1">First Cutoff</option><option value="2">End of the day</option></select></td>
<td>Date:</td><td><input type="text" id="fdate" size="10" class="highlight" value="<?php echo date("m/d/Y"); ?>" /></td>
<td><input type="button" id="search" value="SEARCH"/></td>
</tr>
</table>
</div>