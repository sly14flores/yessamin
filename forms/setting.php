<?php

session_start();

$aform  = '<form id="frmModule">';
$aform .= '<table id="tab-product">';
$aform .= '<tr><td colspan="2" class="change-password"><input type="checkbox" id="change-password" /> <a href="javascript: chkToggleChangePassword();">Change Password:</a></td></tr>';
$aform .= '<tr>';
$aform .= '<td>Old Password:</td>';
$aform .= '<td>';
$aform .= '<input type="password" name="opassword" id="opassword" size="30" />';
$aform .= '<input type="hidden" id="user-password" value="' . $_SESSION['password'] . '" />';
$aform .= '</td>';
$aform .= '</tr>';
$aform .= '<tr><td>New Password:</td><td><input type="password" name="npassword" id="npassword" size="30" /></td></tr>';
$aform .= '<tr><td>Re-type New Password:</td><td><input type="password" name="nnpassword" id="nnpassword" size="30" /></td></tr>';
$aform .= '<tr><td colspan="2" class="validate"></tr>';
$aform .= '<tr><td colspan="2" class="password"></tr>';
$aform .= '</table>';
$aform .= '</form>';
$aform .= '<p>Sync Database</p><hr style="margin-top: 3px; margin-bottom: 3px;">';
$aform .= '<form style="margin-bottom: 15px;" id="frmUploadDb" action="db-upload.php" method="post" enctype="multipart/form-data" target="upload_target">';
$aform .= '<p style="margin-bottom: 5px;"><strong>Database File</strong></p>';
$aform .= '<input id="db-file" name="db-file" type="file">';
$aform .= '</form>';
$aform .= '<input id="brestore" type="button" value="Restore" />';
$aform .= '<p style="margin-top: 5px;" id="sys-msg"></p>';

echo $aform;

?>