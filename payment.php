<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>INPUTS</title>
<style type="text/css">
img {
	border: 0;
}

* {
	margin: 0;
	padding: 0;
}

body {
	background-color: #fbf8fa;
	font: 14px sans-serif;
}

#sub-menu {
	width: 50%;
	margin-top: 0;
	margin-left: 115px;
}

#in-sub-menu {
	padding-top: 5px;
	padding-bottom: 15px;
}

#search-bar {
	margin: 0 auto;
	text-align: center;
}

#filter {
	margin: 0 auto;
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

.content-loading {
	margin-top: 10%;
	width: 5%;
	height: 5%;
	margin-left: auto;
	margin-right: auto;
	text-align: center;
	font-size: .9em;
	color: #8e8e8e;
}

/* payment form */
#terms-transactions {
	height: 150px;
	overflow: scroll;
}

#receipts {
	height: 200px;
	overflow: scroll;
}

#terms-transactions table, #receipts table {
	border-collapse: collapse;
	width: 100%;
	overflow: scroll;
}

#tab-terms-transactions thead td, #receipts thead td {
	background-color: #dd4b39;
	padding: 5px;
	color: #fff;
	font-weight: bold;
}

#tab-terms-transactions td, #receipts-items td {
	padding: 5px;
}

#tab-terms-transactions tbody td, #receipts-items tbody td {
	border-bottom: 1px solid #dd4b39;
}

#tab-terms-transactions tbody tr:hover, #receipts-items tbody tr:hover {
	background-color: #f8e1de;
}

/*
#tab-payment tr:last-child {
	border-top: 1px solid #dddddd;
}

#tab-payment tr:last-child td {
	padding-top: 3px;
}
*/
/* end payment form */

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
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/jquery.blockUI.js?ver=1.0"></script>
<script type="text/javascript" src="js/dialogbox.js?ver=1.0"></script>
<script type="text/javascript" src="js/globalf.js?ver=1.0"></script>
<script type="text/javascript" src="js/payment.js?ver=1.0"></script>
<script type="text/javascript">
$(function() {

var m = 'modules/payment.php';
var f = function() {
	$('#add').button();
	$('#edit').button();
	$('#delete').button();
	$('#search').button();	
	$('#add').click(function() { payment(1); });
	$( "#fs" ).datepicker({
		showOn: "button",
		buttonImage: "images/calendar.gif",
		buttonImageOnly: true
	});

	$( "#fe" ).datepicker({
		showOn: "button",
		buttonImage: "images/calendar.gif",
		buttonImageOnly: true
	});
	$('#edit').click(function() { payment(2); });
	$('#delete').click(function() { confirmReceiptDelete(); });
	$('#search').click(function() { filterReceipt(); });

	$.ajax({
	type: 'json',
	url: 'transaction.php?p=list_dealers',
	success: function(data, status) {
			var suggestList = {};
			suggestList.dealerlist = data;
			$('input#fcustomer').jsonSuggest({data: suggestList.dealerlist, minCharacters: 2, onSelect: function(item){
				$('#fcustomer').val(item.dealer);
			}});				 
			}
	});	
	
	highl();
	
};
$('#sub-menu-search').load(m, function() { f(); });

content(5,0);

});

function changeCashier(tid) {

var t = 'Change Cashier';
var inForm = 'forms/change-cashier.php';
var exe = function() {
$.ajax({
	url: 'payment-ajax.php?p=get_cashier&ccid=' + tid,
	type: 'get',
	success: function(data, status) {
		$('#tra-cashier').val(parseInt(data));
	}
});
};

var f = function() {

var cid = $('#tra-cashier').val();

$.ajax({
	url: 'payment-ajax.php?p=change_cashier&cid=' + cid,
	type: 'post',
	data: {tcc: tid},
	success: function(data, status) {
		closeMainDialog();
		content(5,2);	
	}
});

};

mainDialog(400,180,t,inForm,exe);
mainDialogB('Update','Cancel',f);

}
</script>
</head>
<body>
<div id="sub-menu-search">
</div>
<div id="content">
<div id="in-content">
</div>
</div>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
<div id="processModal" style="z-index: 2147483647;"><div class="loading-msg">Processing please wait...</div><div class="ajax-loader"><img src="images/ajax-loader.gif" /></div></div>
</body>
</html>