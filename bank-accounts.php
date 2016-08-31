<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Bank Accounts</title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}

img {
	border: 0;
}

body {
	background-color: #fbf8fa;
	font: 14px sans-serif;
}

#sub-menu {
	width: 100px;
	margin-top: 0;
	text-align: center;
	margin-left: auto;
	margin-right: auto;
}

#in-sub-menu {
	padding-top: 10px;
}

#search-bar {
	margin: 0 auto;
	text-align: center;
}

#filter {
	margin: 15px auto;
	border: 1px solid #f3cce6;
	border-radius: 5px;
}

#search-bar  #filter td {
	padding: 3px;
}

#search-bar #filter input {
		font-size: 1.1em;
}

#content {
	width: 90%;
	margin: 20px auto 0 auto;
	border: 1px solid #f3cce6;
	border-radius: 5px;	
	background-color: #fff;
}

#in-content {
	padding: 10px;
}

#content-page {
	width: 100%;
	border-collapse: collapse;
	font-family: sans-serif;
}

#content-page thead {
	color: #d235a1;
}

#content-page thead tr {
	border-bottom: 2px solid #d0b252;
}

#content-page thead td {
	padding-bottom: 4px;
}

#content-page thead td:first-child {
	padding-left: 3px;
}

#content-page tbody td {
	padding: 7px 0 5px 3px;
	border-bottom: 1px solid #d0b252;
	color: #265fb9;
}

#content-page tbody td:last-child {
	padding-left: 0;
}

#content-page tbody td img {
	padding-right: 5px;
}

#content-page tfoot ul {
	list-style-type: none;
	float: left;
	margin-top: 5px;
	padding-top: 10px;
}

#content-page tfoot li {
	float: left;
	padding-right: 10px;
}

#frmModule table {
	width: 100%;
	border-collapse: collapse;
	padding-bottom: 8px;
	padding-right: 8px;
}

#frmModule td {
	padding-bottom: 8px;
	padding-right: 8px;
}

.validate {
	padding-top: 5px;
	color: #dd4b39;
}

.row-style-odd:hover, .row-style-even:hover {
	background-color: #fddfdf;
}

/* tooltip */
a.tooltip:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none; padding:2px 3px; margin-top:60px; margin-left:30px; width:100px;}
a.tooltip:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}

a.tooltip-min:hover {background:#inherit; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip-min span {display:none; padding:2px 3px; margin-top:27px; margin-left:-25px; width:60px; font-size:.9em; text-align:center;}
a.tooltip-min:hover span{display:inline; position:absolute; border:1px solid #cccccc; border-radius:5px; background: #ffffff; color:#d54e21;}
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/dialogbox.js"></script>
<script type="text/javascript">
function confirmAdd(src) {

var m = 'Add this bank account?';
var f = function() {

var baname = $('#ba-name').val();
var badesc = $('#ba-desc').val();

if ((baname == '') || (badesc == '')) {

notify(300,200,'Please enter name/description.');
return;

} else {

addBankAccount(src);

}

};
confirmation(300,200,m,f);

}

function addBankAccount(src) {

$('#frmBankAccounts').submit();
document.getElementById("form_bank_account").onload = updateDone;

}

function updateDone() {

window.location.href = 'bank-accounts.php';

}

function confirmDelete(id) {

var m = 'Are you sure you want to delete this account?';
var f = function() {

$.ajax({
	url: 'stock-in.php?p=delete_bank_account',
	type: 'post',
	data: {baid: id},
	success: function(data, status) {
		var e = function() { window.location.href = 'bank-accounts.php'; };
		notify(300,200,data,e);
	}
});

}

confirmation(300,200,m,f);

}

function confirmInlineEdit(id) {

var ibaname = $('#iba-name' + id).val();
var ibadesc = $('#iba-desc' + id).val();
var ibaamount = $('#iba-amount' + id).val();

if ((ibaname == '') || (ibadesc == '')) {

notify(300,200,'Please enter name/description.');
return;

} else {

$('#frmInlineEdit' + id).submit();
document.getElementById("form_bank_account").onload = updateDone;

}

}

$(function() {

var m = 'modules/bank.php';
var f = function() {
	$('#add').button();
	$('#add').click(function() { confirmAdd(0); });
	
	highl();	
};
$('#sub-menu-search').load(m, function() { f(); });

});
</script>
</head>
<body>
<div id="sub-menu-search">
</div>
<div id="content">
<div id="in-content">
<?php

require 'config.php';

$str_response = "";
$sql = "SELECT `bank_account_id`, `bank_account_name`, `bank_account_desc`, `bank_account_amount` FROM `bank_accounts`";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$str_response .= '<div name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td>Name</td><td>Description</td><td>Amount</td><td>Actions</td></tr>';
$str_response .= '</thead>';
if ($rc>0) {
$str_response .= '<tbody>';
	for ($i=0; $i<$rc; ++$i) {
		$rec = $rs->fetch_array();
		$str_response .= '<form id="frmInlineEdit' . $rec['bank_account_id'] . '" name="frmInlineEdit' . $rec['bank_account_id'] . '" method="get" action="ieditba.php" target="form_bank_account"><tr><td><input type="hidden" name="eid" value="' . $rec['bank_account_id'] . '" /><input type="text" id="iba-name' . $rec['bank_account_id'] . '" name="iba-name" value="' . $rec['bank_account_name'] . '"/></td><td><input type="text" id="iba-desc' . $rec['bank_account_id'] . '" name="iba-desc" value="' . $rec['bank_account_desc'] . '"/></td><td><input type="text" id="iba-amount' . $rec['bank_account_id'] . '" name="iba-amount" value="' . $rec['bank_account_amount'] . '"/></td><td><a href="javascript: confirmInlineEdit(' . $rec['bank_account_id'] . ');"><img src="images/save.png" /></a><a href="javascript: confirmDelete(' . $rec['bank_account_id'] . ');"><img src="images/delete.png" /></a></td></tr></form>';
	}
$str_response .= '</tbody>';
}
$str_response .= '</table>';
$str_response .= '</div>';
db_close();

echo $str_response;

?>
</div>
</div>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
<iframe id="form_bank_account" name="form_bank_account" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
</body>
</html>