<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>WALK-IN</title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}

body {
	background-color: #fbf8fa;
	font: 12px sans-serif;
}

#content {
	width: 95%;
	margin: 20px auto 0 auto;
	border: 1px solid #f3cce6;
	border-radius: 5px;	
	background-color: #fff;
}

#in-content {
	padding: 10px;
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

#frmTransactionItem table {
	border-collapse: collapse;
	width: 100%;
	overflow: scroll;
}

#tab-transaction-item thead td {
	background-color: #dd4b39;
	padding: 5px;
	color: #fff;
	font-weight: bold;
}

#tab-transaction-item td {
	padding: 5px;
}

#tab-transaction-item tbody td {
	border-bottom: 1px solid #dd4b39;
}

#frmTransactionItem {
	height: 300px;
	overflow: scroll;
}

.fixh {
	overflow: scroll;
}

#eotd {
	text-align: center;
}

#eotd input {
	margin-top: 20px;
}

.sub-validate {
	padding-top: 5px;
	color: #dd4b39;
}

/* loading modal */
#loadingModal {
	display: none;
	padding-top: 15px;
	padding-bottoM: 15px;
}

.loading-msg {
	padding-bottom: 10px;
	color: #993838;
	font-weight: bold;
}
/* end loading modal */

/* forms */
#frmModule, #subModule {
	font-family: sans-serif;
}

#frmModule table, #subModule table {
	border-collapse: collapse;
	width: 100%;
}

#frmModule td, #subModule td {
	padding-bottom: 8px;
	padding-right: 8px;
}

#frmProductSearchResults table {
	border-collapse: collapse;
	width: 100%;
	overflow: scroll;
}

#tab-product-search-results thead td {
	background-color: #dd4b39;
	padding: 5px;
	color: #fff;
	font-weight: bold;
}

#tab-product-search-results td {
	padding: 5px;
}

#tab-product-search-results tbody td {
	border-bottom: 1px solid #dd4b39;
}

#frmProductSearchResults {
	height: 440px;
}

#tab-product-search-results tr:hover {
	cursor: pointer;
	background-color: #fddfdf;
}

#tab-product-search-results tbody td {
	padding-top: 10px;
	padding-bottom: 10px;
}
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/dialogbox.js"></script>
<script type="text/javascript" src="js/globalf.js"></script>
<script type="text/javascript" src="js/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/walk-in-cash.js"></script>
<script type="text/javascript">
$(function() {

var m = 'forms/walk-in-cash.php';
var f = function() {

$('#adds').button();
$('#adds').click(function() { chkD(1); });

};
$('#in-content').load(m, function() { f(); });

$('#end-day').button();
$('#end-day').click(function() { confirmWalkInCash(1); });

});
</script>
</head>
<body>
<div id="content">
<div id="in-content">
</div>
</div>
<div id="eotd"><input type="button" id="end-day" value="End of the day" /></div>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
<div id="loadingModal" style="z-index: 2147483647;"><div class="loading-msg">Updating stocks please wait...</div><div class="ajax-loader"><img src="images/ajax-loader.gif" /></div></div>
</body>
</html>